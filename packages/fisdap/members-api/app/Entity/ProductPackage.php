<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Product Packages
 *
 * @Entity(repositoryClass="Fisdap\Data\Product\Package\DoctrineProductPackageRepository")
 * @Table(name="fisdap2_product_package")
 */
class ProductPackage extends EntityBaseClass
{
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
     * @Column(type="text")
     */
    protected $description = "";

    /**
     * @var integer The binary configuration code that maps products to this package.
     * For more info, look at the bit_value column in the fisdap2_products table
     *
     * @Column(type="integer")
     */
    protected $configuration = 0;

    /**
     * @var \Fisdap\Entity\CertificationLevel
     *
     * @ManyToOne(targetEntity="CertificationLevel", fetch="EAGER")
     */
    protected $certification;

    /**
     * @var decimal The price for this package
     *
     * @Column(type="decimal", precision=6, scale=2)
     */
    protected $price = 0;

    /**
     * @var string
     * @Column(type="text")
     */
    protected $products_description;

    /**
     * @var string
     * @Column(type="text", nullable=true)
     */
    protected $products_sub_description;

    /**
     * @var string name of package in quickbooks
     * @Column(type="string", nullable=true)
     */
    protected $quickbooks_name;

    /**
     * @var ISBN for this package as assigned by Ascend
     * @Column(type="string", nullable=true)
     */
    protected $package_ISBN;

    protected $discountMessages = array();

    public function init()
    {
        //$this->certifications = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
        return $this->certification;
    }


    /**
     * @param CertificationLevel $certificationLevel
     */
    public function setCertificationLevel(CertificationLevel $certificationLevel)
    {
        $this->certification = $certificationLevel;
    }
    

    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Get the sum of all the individual product prices for this package
     * @return decimal
     */
    public function getFullPrice()
    {
        $price = 0;

        $products = \Fisdap\EntityUtils::getRepository('Product')->getProducts($this->configuration, true);
        foreach ($products as $product) {
            $price += $product->price;
        }

        return $price;
    }

    public function getDiscountedPrice()
    {
        $discountMessages = array();
        $individualPrice = \Fisdap\Entity\Product::calculatePrice(\Fisdap\Entity\User::getLoggedInUser()->getProgramId(), $this->configuration, null, $discountMessages);

        if ($individualPrice < $this->price) {
            $this->discountMessages = $discountMessages;
            return $individualPrice;
        } else {
            $this->discountMessages = array(array("discount" => $this->getFullPrice() - $this->price, "message" => $this->name));
            return $this->price;
        }
    }

    public function getDiscountMessages()
    {
        return $this->discountMessages;
    }

    public function getDiscountMessagesText($lineBreak = "<br>")
    {
        $text = "";

        foreach ($this->discountMessages as $discount) {
            $text .= "$" . $discount["discount"] . " off for " . $discount["message"] . " discount" . $lineBreak;
        }

        return $text;
    }


    public function getProductDescription($student)
    {
        $configuration = $this->configuration;
        $professionId = $this->certification->profession->id;
        return \Fisdap\EntityUtils::getRepository('Product')->getProductList($configuration, $professionId, $student);
    }
}
