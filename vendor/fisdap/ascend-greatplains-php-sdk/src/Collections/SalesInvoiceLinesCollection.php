<?php namespace Fisdap\Ascend\Greatplains\Collections;

use Fisdap\Ascend\Greatplains\Contracts\SalesInvoiceLine;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoiceLinesCollection as SalesInvoiceLinesCollectionInterface;

/**
 * Class SalesInvoiceLinesCollection
 *
 * A collection of sales invoice line items
 *
 * @package Fisdap\Ascend\Greatplains\Collections
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class SalesInvoiceLinesCollection implements SalesInvoiceLinesCollectionInterface
{
    /**
     * @var SalesInvoiceLine[]
     */
    protected $salesInvoiceLines = [];

    /**
     * Append a sales invoice line to the collection
     *
     * @param SalesInvoiceLine $salesInvoiceLine
     * @return $this
     */
    public function append(SalesInvoiceLine $salesInvoiceLine)
    {
        $this->salesInvoiceLines[] = $salesInvoiceLine;
        return $this;
    }

    /**
     * Return array of sales invoice lines
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];

        foreach ($this->salesInvoiceLines as $salesInvoiceLine) {
            $data[] = $salesInvoiceLine->toArray();
        }

        return $data;
    }
}
