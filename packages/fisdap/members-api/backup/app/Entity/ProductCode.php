<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;

/**
 * Entity class for Product Codes
 *
 * @Entity
 * @Table(name="fisdap2_product_codes")
 */
class ProductCode extends EntityBaseClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var \Fisdap\Entity\ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;

    /**
     * @var integer
     * @Column(type="integer")
     */
    protected $configuration = 0;

    /**
     * @var integer
     * @Column(type="integer")
     */
    protected $configuration_limits = 0;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $code;

    /**
     * @ManyToOne(targetEntity="CertificationLevel")
     */
    protected $certification_level;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $graduation_date;

    /**
     * @ManyToOne(targetEntity="ClassSectionLegacy")
     * @JoinColumn(name="group_id", referencedColumnName="Sect_id")
     */
    protected $group;

    /**
     * @ManyToOne(targetEntity="OrderConfiguration", inversedBy="product_codes")
     */
    protected $order_configuration;

    public function set_group($value)
    {
        $this->group = self::id_or_entity_helper($value, "ClassSectionLegacy");
        return $this;
    }

    public function set_certification_level($value)
    {
        $this->certification_level = self::id_or_entity_helper($value, "CertificationLevel");
        return $this;
    }

    public function set_program($value)
    {
        $this->program = self::id_or_entity_helper($value, "ProgramLegacy");
        return $this;
    }

    public function set_order_configuration($value)
    {
        $this->order_configuration = self::id_or_entity_helper($value, "OrderConfiguration");
        return $this;
    }

    /**
     * Create a product code for this program
     * @return string the product code for this configuration
     * @throws \Exception when no program has been set
     * @throws \Exception if we loop too many times
     */
    public function generateProductCode()
    {
        if (!$this->program->id) {
            throw new \Exception("A product code cannot be generated if no program is set.");
        }

        $counter = 50;
        while ($counter > 0) {
            $productCode = $this->program->product_code_id . "-" . $this->generateRandomString();
            if (!self::getByProductCode($productCode)) {
                $this->code = $productCode;
                //$this->save();
                return $this->code;
            }
            $counter--;
        }
        throw new \Exception("A unique product code could not be generated. We have too many.");
    }

    /**
     * Generate a pseudo-random alphanumeric string
     * @return string
     */
    private function generateRandomString()
    {
        $characters = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $stringLength = '4';
        $upperLimit = strlen($characters) - 1;
        $string = '';

        for ($i = 0; $i < $stringLength; $i++) {
            $string .= $characters[mt_rand(0, $upperLimit)];
        }

        return $string;
    }

    /**
     * Given a product code, retrieve the entity
     * @param string $code
     * @return \Fisdap\Entity\ProductCode or NULL if the given code doesn't exist
     */
    public static function getByProductCode($code)
    {
        $productCode = EntityUtils::getRepository("ProductCode")->findOneByCode($code);

        if ($productCode->id) {
            return $productCode;
        }

        return null;
    }

    /**
     * Will determine if the given code is a valid legacy product code
     * Really ugly but who cares - pretty much copied directly from legacy code with few changes
     * @param string $code
     * @return boolean
     */
    public static function isLegacyProductCode($code)
    {
        $code = trim($code);
        $programIdPart = strtok($code, '-');
        $AccountTypeCode = strtok('-');
        $Remainder = strtok('-');

        // if there is no account type code, this is a bad code
        if (!$AccountTypeCode) {
            return false;
        }

        // if there is a remainder after the hyphen, this is a bad code
        if ($Remainder != null) {
            return false;
        }

        // valid program order id?
        $program = EntityUtils::getRepository("ProgramLegacy")->getProgramByOrderId($programIdPart);
        // if there is no program or the program can't order accounts, this is an invalid code
        if (!$program || $program[0]->order_permission->name == "Cannot Order Accounts") {
            return false;
        }

        $LetterCode = strtok($AccountTypeCode, '123456789');
        $LetterCode = strtoupper($LetterCode);
        $NumericCode = substr($AccountTypeCode, strlen($LetterCode));

        // parseLegacyCode will return NULL if the letter code is invalid
        if (!ProductCode::parseLegacyCode($NumericCode, $LetterCode)) {
            return false;
        }

        // if there is a non-numeric numeric code, return false
        if (!is_numeric($NumericCode) && $NumericCode != '') {
            return false;
        }

        /**
         * We do not accept a numeric code with a bitwise 8, because testing no longer a product
         */
        if ($NumericCode & 8) {
            return false;
        } else {
            // grab the products and throw the configuration in an array
            // process copied from legacy (except not hard-coded configs obviously)
            $products = EntityUtils::getRepository("Product")->getProducts();
            foreach ($products as $product) {
                $valid_products[] = $product->configuration;
            }

            $valid_mask = ~array_sum($valid_products);    // must create exact bit wise opposite of valid mask in order to bitwise "and" with the incoming configuration code

            if (($valid_mask & $NumericCode)) {
                return false;
            }
        }

        // if we made it this far, then we have a valid product code
        return true;
    }


    /**
     * Return the details for an account created with this product code
     * @return array
     */
    public function getAccountDetails($couponId = null)
    {
        // get info about this order
        $orderConfig = $this->order_configuration;
        $certification = $orderConfig->certification_level;
        $isStudent = ($certification->description) ? true : false;
        $productConfig = $orderConfig->configuration;
        $productDetails = EntityUtils::getRepository("Product")->getProductInfo($productConfig, $isStudent);

        //Apply a coupon if one is given
        $orderConfig->order->applyCoupon($couponId);

        // get all values we're interested in (and check for null values)
        $accountDetails = array(
            "cert" => $certification->description,
            "certId" => $certification->id,
            "programName" => $orderConfig->order->program->name,
            "programId" => $orderConfig->order->program->id,
            "configuration" => $productConfig,
            "products" => $productDetails,
            "cost" => $orderConfig->calculateFinalPrice(),
            "code" => $this->code,
            "groupName" => $this->group->name,
            "groupId" => $this->group->id,
            "graduationDate" => (!is_null($this->graduation_date)) ? $this->graduation_date->format("M Y") : null
        );

        //Remove the coupon so it doesn't apply to other people
        $orderConfig->order->removeCoupon();

        return $accountDetails;
    }

    public static function getAccountFromLegacyProductCode($code, $couponId = null)
    {
        $code = trim($code);
        $programIdPart = strtok($code, '-');
        $AccountTypeCode = strtok('-');
        $LetterCode = strtok($AccountTypeCode, '123456789');
        $LetterCode = strtoupper($LetterCode);
        $NumericCode = substr($AccountTypeCode, strlen($LetterCode));

        $program = EntityUtils::getRepository("ProgramLegacy")->getProgramByOrderId($programIdPart);

        // get some info about the products associated with this order
        $config = ProductCode::parseLegacyCode($NumericCode, $LetterCode);
        $productConfig = $config['config'];
        $isStudent = ($config['type'] != "instructor") ? true : false;
        $productDetails = EntityUtils::getRepository("Product")->getProductInfo($productConfig, $isStudent);

        // create a new order
        $order = EntityUtils::getEntity("Order");
        $order->program = $program[0]->id;

        // add this configuration to the order
        $orderConfig = EntityUtils::getEntity("OrderConfiguration");
        $order->addOrderConfiguration($orderConfig);
        $orderConfig->configuration = $productConfig;
        // set the cert level if it's a student
        if ($isStudent) {
            $orderConfig->certification_level = $config['type'];
        }

        // apply the coupon
        $order->applyCoupon($couponId);

        // get final price
        $cost = $orderConfig->calculateFinalPrice();

        // get all values we're interested in
        $accountDetails = array(
            "cert" => EntityUtils::getEntity("CertificationLevel", $config['type'])->description,
            "certId" => $config['type'],
            "programName" => $program[0]->name,
            "programId" => $program[0]->id,
            "products" => $productDetails,
            "configuration" => $productConfig,
            "cost" => $cost,
            "code" => $code
        );

        return $accountDetails;
    }

    // painful - copied directly from legacy to handle parsing the 'numericCode' piece of the product code
    // returns an array with the account type and product configuration
    public function parseLegacyCode($NumericCode, $letterCode)
    {
        $trackingConfig = 3;
        $skillsTrackerLimited = 4096;
        $schedulerLimited = 8192;
        switch ($letterCode) {
            //Paramedic accounts
            case 'P':        // FISDAP ALS (paramedic)
                $AccountType = 3;
                $Configuration = $trackingConfig + $NumericCode;
                break;

            case 'PP':        // FISDAP ALS (paramedic) with PDA Access
                $AccountType = 3;
                $Configuration = $trackingConfig + 4 + $NumericCode;
                break;

            case 'SA':        // Scheduler Only ALS (paramedic)
                $AccountType = 3;
                $Configuration = 2 + $NumericCode;
                break;

            case 'PN':        // Paramedic account with NO tracking or scheduler
                $AccountType = 3;
                $Configuration = $NumericCode;
                break;

            case 'PO':        // Tracking only for programs w/ scheduler
                $AccountType = 3;
                $Configuration = 1 + $NumericCode;
                break;

            case 'POP':     // Tracking+PDA only for programs w/ scheduler
                $AccountType = 3;
                $Configuration = 5 + $NumericCode;
                break;

            //EMT-I accounts
            case 'I':        // FISDAP ALS (emt-i)
                $AccountType = 5;
                $Configuration = $trackingConfig + $NumericCode;
                break;

            case 'IP':        // FISDAP ALS (emt-i) with PDA Access
                $AccountType = 5;
                $Configuration = $trackingConfig + 4 + $NumericCode;
                break;

            case 'SI':        // Scheduler Only ALS (emt-i)
                $AccountType = 5;
                $Configuration = 2 + $NumericCode;
                break;

            case 'IN':        // emt-i account with NO tracking or scheduler
                $AccountType = 5;
                $Configuration = $NumericCode;
                break;

            case 'IO':        // Tracking only for programs w/ scheduler
                $AccountType = 5;
                $Configuration = 1 + $NumericCode;
                break;

            case 'IOP':     // Tracking+PDA only for programs w/ scheduler
                $AccountType = 5;
                $Configuration = 5 + $NumericCode;
                break;

            //EMT-B accounts
            case 'B':        // FISDAP BLS (emt-b)
                $AccountType = 1;
                $Configuration = $skillsTrackerLimited + $schedulerLimited + $NumericCode;
                $ConfigLimit = 31;
                break;

            case 'SB':        // Scheduler Only BLS (paramedic)
                $AccountType = 1;
                $Configuration = $schedulerLimited + $NumericCode;
                $ConfigLimit = 31;
                break;

            case 'BN':        // Paramedic account with NO tracking or scheduler
                $AccountType = 1;
                $Configuration = $NumericCode;
                $ConfigLimit = 31;
                break;

            case 'BO':        // Tracking only for programs w/ scheduler
                $AccountType = 1;
                $Configuration = $skillsTrackerLimited + $NumericCode;
                $ConfigLimit = 31;
                break;

            // Instructor accounts
            case 'NP':        //Preceptor Training
                $AccountType = 'instructor';
                $IsInstructor = true;
                $Configuration = 64;
                $ConfigLimit = 0;
                break;

            default:
                return null;
                break;
        }

        return array(
            "type" => $AccountType,
            "config" => $Configuration
        );
    }

    /**
     * Checks to see if we can use this product code right now
     * @return bool
     */
    public function isValid()
    {
        // make sure the program associated with this product code can actually order accounts
        if ($this->program->order_permission->name == "Cannot Order Accounts") {
            return false;
        }

        return true;
    }
}
