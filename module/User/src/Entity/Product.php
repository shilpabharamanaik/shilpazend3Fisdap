<?php namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;
use Fisdap\MoodleUtils;

/**
 * Entity class for Products
 *
 * @Entity(repositoryClass="Fisdap\Data\Product\DoctrineProductRepository")
 * @Table(name="fisdap2_product")
 */
class Product extends EntityBaseClass
{
    /**
     * @var array cache of products
     */
    protected static $products;

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $name;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $short_name;

    /**
     * @Column(type="text")
     */
    protected $description = "";

    /**
     * @var integer The bit value that corresponds to this project
     * @Column(type="integer")
     */
    protected $configuration = 0;

    /**
     * @var decimal The price of this product
     * @Column(type="decimal", scale=2, precision=6)
     */
    protected $price = 0.00;

    /**
     * @var \Fisdap\Entity\ProductCategory
     * @ManyToOne(targetEntity="ProductCategory")
     */
    protected $category;

    /**
     * @var string a possible discount message for this product.
     * This variable is populated by $this->getDiscountPrice()
     */
    protected $discountMessages = array();

    /**
     * @var ProductQuickbooksInfo
     * @OneToOne(targetEntity="ProductQuickbooksInfo", mappedBy="product")
     */
    protected $quickbooks_info;

    /**
     * @var integer the ID of the corresponding moodle course
     * @Column(type="integer", nullable=true)
     */
    protected $moodle_course_id;

    /**
     * @var boolean can this product be bought again to increment its number of attempts
     * @Column(type="boolean")
     */
    protected $has_multiple_attempts = false;

    /**
     * @var boolean can this product only be purchased by staff?
     * @Column(type="boolean")
     */
    protected $staff_only = false;

    /**
     * @var string the moodle that this product belongs to,
     * null if this product doesn't have an associated moodle
     * @Column(type="string", nullable=true)
     */
    protected $moodle_context;

    /**
     * @var Profession
     * @ManyToOne(targetEntity="Profession", fetch="EAGER")
     * @codeCoverageIgnore
     * @deprecated
     */
    protected $profession;

    /**
     * @var CertificationLevel
     * @ManyToOne(targetEntity="CertificationLevel", fetch="EAGER")
     */
    protected $certification_level;

    /**
     * @var ISBN for this product as assigned by Ascend
     * @Column(type="string", nullable=true)
     */
    protected $ISBN;

    /**
     * @var ArrayCollection
     * @ManyToMany(targetEntity="MoodleTestDataLegacy", inversedBy="products")
     * @JoinTable(name="fisdap2_products_moodle_quizzes",
     *      joinColumns={@JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="moodle_quiz_id", referencedColumnName="MoodleQuiz_id")}
     *      )
     */
    protected $moodle_quizzes;

    protected static $quizAttemptsCache = array();

    protected static $transitionCourseInfo = array(
        "EMT-B to EMT" => array("courseNumber" => "12-FISD-F3-0001", "courseHours" => "24", "courseType" => "Basic"),
        "EMT-I85 to AEMT" => array("courseNumber" => "12-FISD-F3-0002", "courseHours" => "24", "courseType" => "Advanced"),
        "EMT-P to Paramedic" => array("courseNumber" => "12-FISD-F3-0003", "courseHours" => "22", "courseType" => "Advanced"),
        "EMT-B to EMT (Allina)" => array("courseNumber" => "12-FISD-F3-0001", "courseHours" => "24", "courseType" => "Basic"),
        "EMT-I85 to AEMT (Allina)" => array("courseNumber" => "12-FISD-F3-0002", "courseHours" => "24", "courseType" => "Advanced"),
        "EMT-P to Paramedic (Allina)" => array("courseNumber" => "12-FISD-F3-0003", "courseHours" => "22", "courseType" => "Advanced"),
    );


    public function __construct()
    {
        $this->moodle_quizzes = new ArrayCollection;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->name;
    }


    /**
     * @return int
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }


    /**
     * @param int $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }
    

    /**
     * @return CertificationLevel
     */
    public function getCertificationLevel()
    {
        return $this->certification_level;
    }

    /**
     * @param CertificationLevel $certification_level
     */
    public function setCertificationLevel(CertificationLevel $certification_level)
    {
        $this->certification_level = $certification_level;
    }


    /**
     * @return string
     */
    public function getISBN()
    {
        return $this->ISBN;
    }


    /**
     * @return mixed
     */
    public function getShortName()
    {
        return ($this->short_name) ? $this->short_name : $this->name;
    }


    /**
     * @return int
     */
    public function getMoodleCourseId()
    {
        return $this->moodle_course_id;
    }


    /**
     * @return string
     */
    public function getMoodleContext()
    {
        return $this->moodle_context;
    }
    

    /**
     * @return string
     */
    public function getDiscountMessages()
    {
        return $this->discountMessages;
    }


    /**
     * @param string $lineBreak
     *
     * @return string
     */
    public function getDiscountMessagesText($lineBreak = "<br>")
    {
        $text = "";

        foreach ($this->discountMessages as $discount) {
            $text .= "$" . $discount["discount"] . " off for " . $discount["message"] . " discount" . $lineBreak;
        }

        return $text;
    }

    /**
     * Return an array of arrays with tutorials' name/url for this product
     * @return array
     */
    public function getTutorials()
    {
        $urlroot = "http://www.fisdap.net/support/";
        $tutorials = array();

        if ($this->id == 2 || $this->id == 11) {
            $tutorials[] = array(
                "name" => "Scheduler for Students",
                "url" => $urlroot . "scheduler/scheduler_students");
        } elseif ($this->id == 1 || $this->id == 10) {
            $tutorials[] = array(
                "name" => "Skills Tracker",
                "url" => "http://vimeo.com/39905485"
            );

            $tutorials[] = array(
                "name" => "Your Portfolio",
                "url" => $urlroot . "skills_tracker/portfolio"
            );
        } elseif ($this->category->id == 2) {
            $tutorials[] = array(
                "name" => "Secure Testing",
                "url" => $urlroot . "testing/secure_testing_0");
        } elseif ($this->category->id == 3) {
            $tutorials[] = array(
                "name" => "Study Tools",
                "url" => $urlroot . "testing/study_tools_0");
        } elseif ($this->id == 9) {
            $tutorials[] = array(
                "name" => "Preceptor Training",
                "url" => $urlroot . "accreditation/preceptor_training#log-in-to-preceptor-training"
            );
        }
        return $tutorials;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getDiscountedPrice($programId = null)
    {
        $messages = array();
        if (!$programId) {
            $programId = User::getLoggedInUser()->getCurrentProgram()->id;
        }
        $price = self::calculatePrice($programId, $this->configuration, null, $this->discountMessages);

        return $price;
    }

    public function getDisplayPrice()
    {
        $html = "";

        if (($newPrice = $this->getDiscountedPrice()) < ($oldPrice = $this->getPrice())) {
            $html .= "<div class='old-price'>$" . number_format($oldPrice, 2, ".", ",") . "</div>";
            $html .= "<div class='new-price'>$" . number_format($newPrice, 2, ".", ",") . "</div>";
        } else {
            $html .= "$" . number_format($newPrice, 2, ".", ",");
        }

        return $html;
    }

    /**
     * Loop over each Moodle quiz tied to this product
     * and increment OR decrement the number of attempts for the given user
     *
     * @param integer $userId
     * @return void;
     */
    public function modifyTestAttempts($userId, $operator = "+")
    {
        if ($operator != '+' && $operator != '-') {
            $operator = '+';
        }

        foreach ($this->moodle_quizzes as $quiz) {
            //Get attempts from cache or from moodle database and cache it
            if (array_key_exists($quiz->id, self::$quizAttemptsCache)) {
                $defaultAttempts = self::$quizAttemptsCache[$quiz->id];
            } else {
                $defaultAttempts = MoodleUtils::getQuizDefaultMaxAttempts($quiz);
                self::$quizAttemptsCache[$quiz->id] = $defaultAttempts;
            }

            MoodleUtils::setUsersQuizAttemptLimit(array($userId), $quiz, $operator . $defaultAttempts);
        }
    }

    /**
     * Given a set config, calculate the price
     *
     * @param integer $programId the ID of the program
     * @param integer $config the bitmask representing the products
     * @param integer $couponId the ID of a given coupon
     * @param array $message an array of messages to place any discount/coupon messages in
     * @return decimal the price of these products
     */
    public static function calculatePrice($programId, $config, $couponId = null, &$messages = array())
    {
        $price = 0;
        $coupon = EntityUtils::getEntity('Coupon', $couponId);
        $discounts = EntityUtils::getRepository('DiscountLegacy')->getCurrentDiscounts($programId);
        $program = EntityUtils::getEntity("ProgramLegacy", $programId);

        //Cache the product array
        if (!isset(self::$products)) {
            self::$products = EntityUtils::getRepository('Product')->getProducts(0, false, false, false, true, $program->profession->id);
        }

        //Loop over products to see if they fit into the configuration
        foreach (self::$products as $product) {
            if ($config & $product->configuration) {
                $currentPrice = $product->price;
                $currentMessage = array();

                //If a coupon applies to this product, calculate the discount and see if it's cheaper
                if ($coupon->id && ($coupon->configuration & $product->configuration)) {
                    $couponPrice = $coupon->getDiscountedPrice($product->price);

                    if ($couponPrice < $currentPrice) {
                        $currentPrice = $couponPrice;
                        $currentMessage = array("discount" => $product->price - $couponPrice, "message" => $coupon->description);
                    }
                }

                //Loop over applicable discounts to see if any apply
                foreach ($discounts as $discount) {
                    if ($product->configuration & $discount->configuration) {
                        //Calculate the discounted price
                        $discountPrice = $discount->getDiscountedPrice($product->price);

                        if ($discountPrice < $currentPrice) {
                            $currentPrice = $discountPrice;
                            $currentMessage = array("discount" => $product->price - $discountPrice, "message" => $discount->getSummary($product->configuration));
                        }
                    }
                }

                //Save the current price and message
                $price += $currentPrice;
                if (!empty($currentMessage)) {
                    $messages[] = $currentMessage;
                }

                //Subtract the product config from the requested config
                $config -= $product->configuration;
            }

            //If the config is zero, there's no reason to look at other products
            if ($config === 0) {
                return $price;
            }
        }

        return $price;
    }

    /**
     * Return a human readable list of products based on a given configuration
     *
     * @param int $config the bitmask of products return
     * @param int $professionId designate which type of products you're grabbing
     * @param string $separator string to separate list elements
     * @param string $finalSeparator string override for the last separator in a list
     *
     * @return string
     */
    public static function getProductSummary($config, $professionId = 1, $separator = ", ", $finalSeparator = ", ")
    {
        $productSummary = array();

        $products = EntityUtils::getRepository("Product")->getProducts($config, true, false, false, true, $professionId);
        foreach ($products as $product) {
            $productSummary[] = $product->name;
        }

        //if we're not overridding the final separator, just return the array imploded into a string
        if ($separator == $finalSeparator) {
            return implode($separator, $productSummary);
        } else {
            //if we're overriding the final element, pop it off the array, the implode the rest of the products and
            //tack it onto the end
            $finalProduct = array_pop($productSummary);

            //If there are no more elements in the list after grabbing the last one, just return it
            if (empty($productSummary)) {
                return $finalProduct;
            } else {
                return implode($separator, $productSummary) . $finalSeparator . $finalProduct;
            }
        }
    }

    /**
     * Get each product (will return just an array of IDs if param is true)
     * @param id only, false if not specified
     * @return array
     */
    public static function getProductArray($config, $idOnly = false, $professionId = 1)
    {
        $productsReturn = array();
        $products = EntityUtils::getRepository("Product")->getProducts($config, true, false, false, true, $professionId);

        foreach ($products as $product) {
            if ($idOnly) {
                $productsReturn[] = $product->id;
            } else {
                $thisProduct = $product->toArray();
                $thisProduct['category'] = $product->category->id;
                $productsReturn[] = $thisProduct;
            }
        }
        return $productsReturn;
    }

    /**
     * Get additional info required by CECBEMS for the Transition courses
     * @param string $coursename the name of the course in moodle
     * @return array
     */
    public static function getTransitionCourseInfo($coursename)
    {
        return self::$transitionCourseInfo[$coursename];
    }

    /**
     * Get all moodle quiz ids associated with this product
     * @return array
     */
    public function getMoodleQuizIds()
    {
        $ids = array();
        foreach ($this->moodle_quizzes as $quiz) {
            $ids[] = $quiz->moodle_quiz_id;
        }

        return $ids;
    }
}
