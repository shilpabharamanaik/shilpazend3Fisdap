<?php namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Order Configuration
 *
 * @Entity(repositoryClass="Fisdap\Data\Order\Configuration\DoctrineOrderConfigurationRepository")
 * @Table(name="fisdap2_order_configurations")
 * @HasLifecycleCallbacks
 */
class OrderConfiguration extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var Order
     * @ManyToOne(targetEntity="Order", inversedBy="order_configurations")
     */
    protected $order;
    
    /**
     * @var User
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="upgraded_user_id", referencedColumnName="id")
     */
    protected $upgraded_user;
    
    /**
     * @var integer a bitmask representing what products this order has access to
     * @Column(type="integer")
     */
    protected $configuration = 0;
        
    /**
     * @var integer a bitmask representing any products that are limited
     * @Column(type="integer")
     */
    protected $configuration_limits = 0;

    /**
     * @var integer a bitmask representing what products this order should REMOVE/DOWNGRADE access to
     * @Column(type="integer")
     */
    protected $downgrade_configuration = 0;
    
    /**
     * @var integer a bitmask representing attempt-limited products for which the user's attempts should be reduced by the standard amount
     * @Column(type="integer")
     */
    protected $reduce_configuration = 0;

    /**
     * @var CertificationLevel
     * @ManyToOne(targetEntity="CertificationLevel")
     */
    protected $certification_level;
    
    /**
     * @var ClassSectionLegacy
     * @ManyToOne(targetEntity="ClassSectionLegacy")
     * @JoinColumn(name="group_id", referencedColumnName="Sect_id")
     */
    protected $group;
    
    /**
     * @var \DateTime
     * @Column(type="date", nullable=true)
     */
    protected $graduation_date;
    
    /**
     * @var integer
     * @Column(type="integer")
     */
    protected $quantity = 0;

    /**
     * @var decimal
     * @Column(type="decimal", precision=10, scale=2)
     */
    protected $individual_cost = 0;

    /**
     * @var decimal
     * @Column(type="decimal", precision=10, scale=2)
     */
    protected $subtotal_cost;
    
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="ProductCode", mappedBy="order_configuration", cascade={"persist","remove"})
     */
    protected $product_codes;
    
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="SerialNumberLegacy", mappedBy="order_configuration")
     */
    protected $serial_numbers;
    
    /**
     * @var ProductPackage
     * @ManyToOne(targetEntity="ProductPackage")
     */
    protected $package;
    
    /**
     * @var string
     * @Column(type="text", nullable=true)
     */
    protected $discounts;
    
    /**
     * @var string summary of the products for this configuration
     * @Column(type="text", nullable=true)
     */
    protected $product_summary;
    
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $free_accounts = false;
    
    public function init()
    {
        $this->product_codes = new ArrayCollection();
        $this->serial_numbers = new ArrayCollection();
    }
    
    public function set_upgraded_user($value)
    {
        $this->upgraded_user = self::id_or_entity_helper($value, "User");
    }
    
    public function set_order($value)
    {
        $this->order = self::id_or_entity_helper($value, "Order");
    }
    
    public function set_certification_level($value)
    {
        $this->certification_level = self::id_or_entity_helper($value, "CertificationLevel");
    }
    
    public function set_group($value)
    {
        $this->group = self::id_or_entity_helper($value, "ClassSectionLegacy");
    }
    
    public function set_configuration($value)
    {
        $this->configuration = $value;
        
        //If the cert level is not set, for certain products, set a cert level
        if (!$this->certification_level) {
            switch ($this->configuration) {
                case 32768:
                    $this->set_certification_level(3);
                    break;
                case 65536:
                    $this->set_certification_level(1);
                    break;
                case 131072:
                    $this->set_certification_level(5);
                    break;
            }
        }
    }
    
    public function generateProductCode()
    {
        $this->product_codes->clear();
        $productCode = EntityUtils::getEntity("ProductCode");
        $productCode->program = $this->order->program;
        $productCode->configuration = $this->configuration;
        $productCode->configuration_limits = $this->configuration_limits;
        $productCode->certification_level = $this->certification_level;
        $productCode->graduation_date = $this->graduation_date;
        $productCode->group = $this->group;
        $productCode->order_configuration = $this;
        $this->product_codes->add($productCode);
        $productCode->generateProductCode();
        $productCode->save(false);
    }
    
    public function getProductSummary()
    {
        if ($this->product_summary) {
            return $this->product_summary;
        }
        
        $summaryPieces = array();
        
        if ($this->package) {
            $summaryPieces[] = $this->package->name;
            $config = $this->configuration - $this->package->configuration;
        } else {
            $config = $this->configuration;
        }
        
        // Get markup for the upgrade/added products
        $products = EntityUtils::getRepository("Product")->getProducts($config, true, false, false, true, $this->order->program->profession->id);
        foreach ($products as $product) {
            if ($this->order->upgrade_purchase) {
                $serialNumber = $this->upgraded_user->getCurrentUserContext()->getPrimarySerialNumber();
                $summaryPieces[] = ($serialNumber->configuration & $product->configuration ? "Additional Attempts - " : null) . $product->name;
            } else {
                $summaryPieces[] = $product->name;
            }
        }
        $productList = "<div class='product-summary'>" . implode(", ", $summaryPieces) . "</div>";
        
        // get markup for any downgrades that are included in the order
        if ($this->downgrade_configuration > 0) {
            $downgradeSummaryPieces = array();
            $downgrades = EntityUtils::getRepository("Product")->getProducts($this->downgrade_configuration, true, false, false, true, $this->order->program->profession->id);
            foreach ($downgrades as $product) {
                $downgradeSummaryPieces[] = $product->name;
            }
            
            $downgradeList = "<div class='product-summary-downgrades'><strong>To be downgraded</strong>: " . implode(", ", $downgradeSummaryPieces) . "</div>";
        }
        
        // get markup for any downgrades that are included in the order
        if ($this->reduce_configuration > 0) {
            $reduceSummaryPieces = array();
            $reduces = EntityUtils::getRepository("Product")->getProducts($this->reduce_configuration, true, false, false, true, $this->order->program->profession->id);
            foreach ($reduces as $product) {
                $reduceSummaryPieces[] = $product->name;
            }
            
            $reduceList = "<div class='product-summary-reduces'><strong>To be reduced</strong>: " . implode(", ", $reduceSummaryPieces) . "</div>";
        }
       
        return $productList . $downgradeList . $reduceList;
    }
    
    /**
     * Determine if this configuration contains only study tools
     * @return boolean
     */
    public function onlyStudyTools()
    {
        $onlyStudyTools = true;
        foreach ($this->getProductArray() as $product) {
            if ($product['category'] != 3) {
                $onlyStudyTools = false;
            }
        }
        return $onlyStudyTools;
    }
    
    /**
     * Determine if this configuration contains only one of the transition courses
     * @return boolean
     */
    public function onlyTransitionCourse()
    {
        $onlyTransitionCourse = true;
        foreach ($this->getProductArray() as $product) {
            if ($product['category'] != 4) {
                $onlyTransitionCourse = false;
            }
        }
        return $onlyTransitionCourse;
    }
    
    /**
     * Determine if this ocnfiguration contains only Preceptor Training
     * @return boolean
     */
    public function onlyPreceptorTraining()
    {
        $onlyPreceptorTraining = true;
        foreach ($this->getProductArray() as $product) {
            if ($product['configuration'] != 64) {
                $onlyPreceptorTraining = false;
            }
        }
        return $onlyPreceptorTraining;
    }
    
    /**
     * Determine if this configuration matches the products in a given product code
     * @param product code
     * @return true if the products match, false if there is an issue
     */
    public function compareToProductCode($productCode, $legacy = false)
    {
        if (!$legacy) {
            $code = ProductCode::getByProductCode($productCode);
            $acctDetails = $code->getAccountDetails();
        } else {
            $acctDetails = ProductCode::getAccountFromLegacyProductCode($productCode);
        }
        
        
        if ($acctDetails['configuration'] == $this->configuration) {
            return true;
        }

        return false;
    }
    
    /**
     * Get each product (will return just an array of IDs if param is true)
     * @param id only, false if not specified
     * @return array
     */
    public function getProductArray($idOnly = false)
    {
        return Product::getProductArray($this->configuration, $idOnly, $this->order->program->profession->id);
    }
    
    /**
     * Does this order configuration contain multiple products
     * Mostly used to determine plural copy
     *
     * @return boolean
     */
    public function hasMultipleProducts()
    {
        $products = $this->getProductArray();
        return count($products) > 1;
    }
    
    /**
     * Get the cost per account of this configuration displayed as currency
     * @return string
     */
    public function getProductPrice()
    {
        return number_format($this->individual_cost, 2, ".", ",");
    }
    
    /**
     * Get the subtotal cost of this configuration displayed as currency
     * @return string
     */
    public function getSubtotalCost()
    {
        return number_format($this->subtotal_cost, 2, ".", ",");
    }
    
    public function isInstructorAccount()
    {
        return !$this->certification_level->id;
    }
    
    /**
     * Display textual summary of WHO these accounts are for
     * @return string description of account holders
     */
    public function getAccountSummary($includeGroup = true, $listSeparator = "<br>")
    {
        if ($this->order->upgrade_purchase) {
            $summary = $this->upgraded_user->getFullName();
            $summary .= !$this->upgraded_user->isInstructor() ? $listSeparator . $this->upgraded_user->getCurrentRoleData()->getCertification()->description : null;
            $summary .= !$this->upgraded_user->isInstructor() ? $listSeparator . $this->upgraded_user->getCurrentRoleData()->getGraduationDate()->format("M Y") : null;
            
            return $summary;
        }
        
        $summary = "";
        if (!$this->isInstructorAccount()) {
            $summary .= $this->certification_level->description;
            $summary .= $this->graduation_date ? $listSeparator . $this->graduation_date->format("M Y") : "";
            if ($includeGroup) {
                $summary .= $this->group->id ? $listSeparator . $this->group->name : "";
            }
        } else {
            $summary .= "Instructor";
        }
        
        return $summary;
    }

    public function getSummary($includeQuantity = true, $confirmationMessage = false)
    {
        if ($this->order->upgrade_purchase) {
            if ($confirmationMessage) {
                $upgradeLanguage = "now has";
            } else {
                $upgradeLanguage = "upgraded to";
            }
            return $this->upgraded_user->getName() . " " . $upgradeLanguage . " access " . $this->getProductSummary();
        }
        
        $cert = $this->certification_level->id ? $this->certification_level->description : null;
        $summary = "";
        
        if ($includeQuantity) {
            $summary .= $this->quantity . " ";
        }
        
        if ($cert) {
            $summary .= $cert . " ";
        }
        
        $summary .= "accounts with " . $this->getProductSummary();
        return $summary;
    }
    
    public function getProductCode()
    {
        return $this->product_codes->first()->code;
    }

    /**
     * Unserialize the discounts from the database and fetch them
     * @return array
     */
    public function getDiscounts()
    {
        return unserialize($this->discounts);
    }
    
    /**
     * Calculate the lowest possible price for this order configuration
     * @return decimal price of this order configuration
     * @throws \Exception you should call this function only after attaching the order configuration to an order
     */
    public function calculateFinalPrice()
    {
        if (!$this->order) {
            throw new \Exception("This order configuration needs to be attached to an order before a price can be calculated.");
        }

        //Get the program for this order, either from the order, or from the logged in user
        $programId = $this->order->program->id ? $this->order->program->id : \Fisdap\Entity\User::getLoggedInUser()->getProgramId();
        
        //Grab the available packages unless we're dealing with an instructor account, then we know there are no packages
        if (!$this->certification_level->id) {
            $packages = array();
        } else {
            $packages = EntityUtils::getRepository("ProductPackage")->findByCertification($this->certification_level->id);
        }

        //Check for coupon attached to this order
        if ($this->order) {
            $couponId = $this->order->coupon->id;
        }
        
        $possible = array();

        $lowestPackagePrice = PHP_INT_MAX;
        $lowestPackageMsgs = array();

        foreach ($packages as $package) {
            //See if this package fits into our config
            if (($this->configuration & $package->configuration) == $package->configuration) {
                $remainingConfig = $this->configuration - $package->configuration;
                
                //If there are no remaining products after package is applied, get the price, otherwise add the remaining products
                if ($remainingConfig === 0) {
                    $price = $package->price;
                    $tmpMessages = array(array("discount" => $package->getFullPrice() - $package->price, "message" => $package->name));
                } else {
                    //Create a temporary array to store the package discount info, and then add any other discount info from the remaining products
                    $tmpMessages = array(array("discount" => $package->getFullPrice() - $package->price, "message" => $package->name));
                    
                    $packagePrice = $package->price;
                    $individualPrice = Product::calculatePrice($programId, $remainingConfig, $couponId, $tmpMessages);
                    $price = $packagePrice + $individualPrice;
                }
                
                //Compare the price of this package combo to the price of the current one
                if ($price <= $lowestPackagePrice) {
                    $lowestPackagePrice = $price;
                    $lowestPackageMsgs = $tmpMessages;
                    $lowestPackage = $package;
                }
            }
        }
        
        if ($lowestPackage) {
            $this->package = $lowestPackage;
        }
        
        //Calculate the price of the individual products in case it's cheaper than any of the packages
        $tmpMessages = array();

        $individualProductsSum = Product::calculatePrice($programId, $this->configuration, $couponId, $tmpMessages);
        
        
        //Compare the package combo to just buying the accounts individually
        if ($individualProductsSum < $lowestPackagePrice) {
            $this->individual_cost = $individualProductsSum;
            $this->discounts = serialize($tmpMessages);
        } else {
            $this->individual_cost = $lowestPackagePrice;
            $this->discounts = serialize($lowestPackageMsgs);
        }
        
        //Store the summary of products
        $this->product_summary = $this->getProductSummary();
        
        //Recalculate the entire order cost
        $this->calculateSubtotal();
        
        return $this->individual_cost;
    }
    
    /**
     *  @PrePersist
     *  @PreUpdate
     *
     *  Calculate the subtotal using the quantity and price per account
     */
    public function calculateSubtotal()
    {
        $this->subtotal_cost = $this->quantity * $this->individual_cost;
        $this->order->calculateTotal();

        return $this->subtotal_cost;
    }
    
    /**
     * Create serial numbers for this order configuration
     * @return array of \Fisdap\Entity\SerialNumberLegacy
     */
    public function generateSerialNumbers()
    {
        //Reset the counter for Serial Number generation so that a
        //grouping of serial numbers will increment
        SerialNumberLegacy::resetCounter();
        
        //Create a serial number for each account
        for ($i=0; $i<$this->quantity; $i++) {
            $serial = EntityUtils::getEntity("SerialNumberLegacy");
            $serial->program = $this->order->program;
            $serial->purchase_order = $this->order->po_number;
            $serial->configuration = $this->configuration;
            $serial->configuration_limit = $this->configuration_limits;
            $serial->certification_level = $this->certification_level;
            $serial->graduation_date = $this->graduation_date;
            $serial->group = $this->group;
            $serial->generateNumber();
            $this->addSerialNumber($serial);
            $serial->save(false);
        }
    }
    
    /**
     * Add a serial number entity to this order configuration
     * @param \Fisdap\Entity\SerialNumberLegacy
     * @return \Fisdap\Entity\OrderConfiguration
     */
    public function addSerialNumber(SerialNumberLegacy $serial)
    {
        $this->serial_numbers->add($serial);
        $serial->order_configuration = $this;
        $serial->order = $this->order;
        return $this;
    }

    /**
     * Get an array of invoice data to feed into our viewscript
     * @return array
     */
    public function getInvoiceArray()
    {
        $invoiceConfig = array();

        //Don't add this order configuration if it's for free PT accounts
        if ($this->free_accounts) {
            return $invoiceConfig;
        }

        //Add Student name for upgrade orders
        if ($this->order->upgrade_purchase) {
            $invoiceConfig[] = array("desc" => "Upgrade: " . $this->upgraded_user->getName());
        }

        //Grab the configuration of products for this part of the order, we'll manipulate it later
        $configuration = $this->configuration;

        $description = "";

        //handle any packages first
        if ($this->package->id) {
            $description .= "<b>" . $this->package->name . ":</b> <br>" . $this->package->description . "<br><br>";
            $configuration -= $this->package->configuration;
        }

        //if we have left over products remaining, handle those
        if ($configuration > 0) {
            $products = EntityUtils::getRepository("Product")->getProducts($configuration, true, false, false, true, $this->order->program->profession->id);

            foreach ($products as $product) {
                $description .= "<b>" . $product->name . ":</b> <br>" . $product->description . "<br><br>";
            }
        }

        $invoiceConfig[] = array(
            "item" => $this->product_summary,
            "qty" => $this->quantity,
            "desc" => $description,
            "rate" => $this->individual_cost
        );

        return $invoiceConfig;
    }
}
