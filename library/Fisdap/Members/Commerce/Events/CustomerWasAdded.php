<?php namespace Fisdap\Members\Commerce\Events;

/**
 * Class CustomerWasAdded
 * @package Fisdap\Members\Commerce\Events
 * @author Sam Tape <stape@fisdap.net>
 */
class CustomerWasAdded
{

    /**
     * @var int
     */
    private $programId;

    /**
     * @var string
     */
    private $customerName;

    /**
     * @var int
     */
    private $customerId;

    /**
     * CustomerWasAdded constructor.
     * @param int $programId
     * @param string $customerName
     * @param int $customerId
     */
    public function __construct($programId, $customerName, $customerId)
    {
        $this->programId = $programId;
        $this->customerName = $customerName;
        $this->customerId = $customerId;
    }

    /**
     * @return int
     */
    public function getProgramId()
    {
        return $this->programId;
    }

    /**
     * @param int $programId
     */
    public function setProgramId($programId)
    {
        $this->programId = $programId;
    }

    /**
     * @return string
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * @param string $customerName
     */
    public function setCustomerName($customerName)
    {
        $this->customerName = $customerName;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param int $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }
}
