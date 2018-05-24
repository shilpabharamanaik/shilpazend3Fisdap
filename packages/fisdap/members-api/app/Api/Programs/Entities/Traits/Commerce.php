<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Fisdap\Entity\OrderPermission;


/**
 * Class Commerce
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait Commerce
{
    /**
     * @var string|null
     * @Column(name="QuickbooksID", type="string", nullable=true)
     */
    protected $customer_name = null;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $customer_id = 0;

    /**
     * @var bool
     * @Column(name="RequiresPO", type="boolean")
     */
    protected $requires_po = false;

    /**
     * @var string
     * @Column(name="OrderId", type="string")
     */
    protected $product_code_id = "Unset";


    /**
     * @var OrderPermission
     * @ManyToOne(targetEntity="OrderPermission")
     * @JoinColumn(name="CanBuyAccounts")
     */
    protected $order_permission;

    /**
     * @OneToMany(targetEntity="SerialNumberLegacy", mappedBy="program")
     * @JoinColumn(name="Program_id", referencedColumnName="Program_id")
     */
    protected $serialNumbers;


    /**
     * Generate a customer name (QuickbooksID) for this program
     */
    public function generateCustomerName()
    {
        $this->customer_id = $this->getEntityRepository()->getMaxCustomerId() + 1;
        $customer_name = $this->customer_id . "-" . $this->name;

        //Quickbooks says the customer name cannot be larger than 41 characters
        if (strlen($customer_name) > 41) {
            $customer_name = substr($customer_name, 0, 41);
        }

        $this->customer_name = $customer_name;
    }


    /**
     * Get the customer name for this program
     *
     * @return string
     */
    public function getCustomerName()
    {
        return $this->customer_name;
    }


    /**
     * @return bool
     */
    public function getRequiresPo()
    {
        return $this->requires_po;
    }


    /**
     * @param bool $requires_po
     */
    public function setRequiresPo($requires_po)
    {
        $this->requires_po = $requires_po;
    }

    
    public function generateProductCodeId()
    {
        if (!$this->id) {
            throw new \Exception("The product code ID cannot be created if there's no program ID.");
        }

        // Form the order ID.
        $abbreviation = $this->abbreviation;

        $remove = '!@#$%^&*()_+-=~<>?0123456789';
        $replace = str_pad('', strlen($remove));
        $abbreviation_scrubbed = strtr($abbreviation, $remove, $replace);

        $abbreviation_scrubbed = str_replace(' ', '', $abbreviation_scrubbed);
        if (!strlen($abbreviation_scrubbed)) {
            $abbreviation_scrubbed = $this->name;
            $abbreviation_scrubbed = str_replace(' ', '', $abbreviation_scrubbed);
        }

        $max = min(7, 10-strlen((string)$this->id));
        $this->product_code_id = substr($abbreviation_scrubbed, 0, $max) . $this->id;
    }


    /**
     * @return string
     */
    public function getProductCodeId()
    {
        return $this->product_code_id;
    }
    

    /**
     * @return OrderPermission
     */
    public function getOrderPermission()
    {
        return $this->order_permission;
    }
    
    
    /**
     * @param $value
     *
     * @throws \Exception
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_order_permission($value)
    {
        $this->order_permission = self::id_or_entity_helper($value, "OrderPermission");
    }


    /**
     * @param OrderPermission $orderPermission
     */
    public function setOrderPermission(OrderPermission $orderPermission)
    {
        $this->order_permission = $orderPermission;
    }
}
