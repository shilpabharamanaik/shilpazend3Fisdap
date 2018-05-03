<?php namespace Fisdap\Ascend\Greatplains\Models;

use Fisdap\Ascend\Greatplains\Contracts\SalesInvoiceLinesCollection;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoicePaymentsCollection;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoice as SalesInvoiceInterface;

/**
 * Class SalesInvoice
 *
 * An individual sales invoice entity
 *
 * @package Fisdap\Ascend\Greatplains\Models
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class SalesInvoice implements SalesInvoiceInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $customerId;

    /**
     * @var string
     */
    private $batchId;

    /**
     * @var string
     */
    private $date;

    /**
     * @var SalesInvoiceLinesCollection
     */
    private $lines;

    /**
     * @var SalesInvoicePaymentsCollection
     */
    private $payments;

    /**
     * SalesInvoice constructor.
     * @param $id
     * @param $customerId
     * @param $batchId
     * @param $date
     * @param SalesInvoiceLinesCollection $lines
     * @param SalesInvoicePaymentsCollection $payments
     */
    public function __construct(
        $id,
        $customerId,
        $batchId,
        $date,
        SalesInvoiceLinesCollection $lines,
        SalesInvoicePaymentsCollection $payments
    ) {
        $this->id = $id;
        $this->customerId = $customerId;
        $this->batchId = $batchId;
        $this->date = $date;
        $this->lines = $lines;
        $this->payments = $payments;
    }

    /**
     * Get sales invoice id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get sales invoice customer id
     *
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Get sales invoice batch id
     *
     * @return string
     */
    public function getBatchId()
    {
        return $this->batchId;
    }

    /**
     * Get sales invoice date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get sales invoice lines
     *
     * @return SalesInvoiceLinesCollection
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * Get sales invoice payments
     *
     * @return SalesInvoicePaymentsCollection
     */
    public function getPayments()
    {
        return $this->payments;
    }
}
