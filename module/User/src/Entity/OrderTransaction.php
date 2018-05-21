<?php namespace User\Entity;

use Braintree_Result_Successful;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Order Transaction History
 *
 * @Entity
 * @Table(name="fisdap2_order_transactions")
 * @HasLifecycleCallbacks
 */
class OrderTransaction extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var \Fisdap\Entity\Order
     * @ManyToOne(targetEntity="Order", inversedBy="order_transactions")
     */
    protected $order;
    
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $success = false;
    
    /**
     * @var string
     * @Column(type="text")
     */
    protected $response = "";
    
    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $timestamp;

    /**
     * @return Braintree_Result_Successful
     */
    public function getResponseObject()
    {
        return unserialize($this->response);
    }
}
