<?php namespace Fisdap\Ascend\Greatplains\Contracts;

use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;

/**
 * Interface SalesInvoicePaymentsCollection
 *
 * A collection of sales invoice payments
 *
 * @package Fisdap\Ascend\Greatplains\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface SalesInvoicePaymentsCollection extends Arrayable
{
    /**
     * Add a new sales invoice payment to the collection
     *
     * @param SalesInvoicePayment $salesInvoicePayment
     * @return SalesInvoicePaymentsCollection
     */
    public function append(SalesInvoicePayment $salesInvoicePayment);
}
