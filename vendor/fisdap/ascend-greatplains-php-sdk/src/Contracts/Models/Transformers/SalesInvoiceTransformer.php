<?php namespace Fisdap\Ascend\Greatplains\Contracts\Models\Transformers;

use Fisdap\Ascend\Greatplains\Contracts\SalesInvoice;

/**
 * Interface SalesInvoiceTransformer
 *
 * Sales invoice transformer to transform data to use in storage layer
 *
 * @package Fisdap\Ascend\Greatplains\Contracts\Models\Transformers
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface SalesInvoiceTransformer extends PersistentEntityTransformer, EntityTransformer
{
    /**
     * Set the sales invoice
     *
     * @param SalesInvoice $salesInvoice
     * @return SalesInvoiceTransformer
     */
    public function setSalesInvoice(SalesInvoice $salesInvoice);
}
