<?php namespace Fisdap\Ascend\Greatplains\Contracts\Factories;

use Fisdap\Ascend\Greatplains\Contracts\SalesInvoice;

/**
 * Interface CreateSalesInvoiceBuilder
 *
 * Create and return sales invoice entity
 *
 * @package Fisdap\Ascend\Greatplains\Contracts\Factories
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface CreateSalesInvoiceBuilder
{
    /**
     * Build and return a sales invoice entity
     *
     * @return SalesInvoice
     */
    public function buildSalesInvoiceEntity();
}
