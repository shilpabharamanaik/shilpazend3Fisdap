<?php namespace Fisdap\Members\Commerce\Events;


/**
 * Class OrderWasCompleted
 * 
 * @package Fisdap\Members\Commerce\Events
 * @author Sam Tape <sctape@gmail.com>
 */
class OrderWasCompleted
{
    /**
     * @var int
     */
    private $orderId;

    /**
     * @param int $orderId
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }
}
