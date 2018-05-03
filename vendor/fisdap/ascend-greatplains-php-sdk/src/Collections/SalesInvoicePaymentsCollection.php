<?php namespace Fisdap\Ascend\Greatplains\Collections;

use Fisdap\Ascend\Greatplains\Contracts\SalesInvoicePayment;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoicePaymentsCollection as SalesInvoicePaymentsCollectionInterface;

/**
 * Class SalesInvoicePaymentsCollection
 *
 *
 *
 * @package Fisdap\Ascend\Greatplains\Collections
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class SalesInvoicePaymentsCollection implements SalesInvoicePaymentsCollectionInterface
{
    /**
     * @var SalesInvoicePayment[]
     */
    protected $salesInvoicePayments = [];

    /**
     * Append a sales invoice payment to collection
     *
     * @param SalesInvoicePayment $salesInvoicePayment
     * @return $this
     */
    public function append(SalesInvoicePayment $salesInvoicePayment)
    {
        $this->salesInvoicePayments[] = $salesInvoicePayment;
        return $this;
    }

    /**
     * Return sales invoice payments as an array
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];

        foreach ($this->salesInvoicePayments as $salesInvoicePayment) {
            $data[] = $salesInvoicePayment->toArray();
        }

        return $data;
    }
}
