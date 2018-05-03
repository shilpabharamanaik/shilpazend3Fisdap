<?php namespace Fisdap\Ascend\Greatplains\Contracts;

use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;

/**
 * Interface SalesInvoiceLinesCollection
 *
 * Collection of sales invoice lines
 *
 * @package Fisdap\Ascend\Greatplains\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface SalesInvoiceLinesCollection extends Arrayable
{
    /**
     * Add a new sales invoice line
     *
     * @param SalesInvoiceLine $salesInvoiceLine
     * @return SalesInvoiceLinesCollection
     */
    public function append(SalesInvoiceLine $salesInvoiceLine);
}
