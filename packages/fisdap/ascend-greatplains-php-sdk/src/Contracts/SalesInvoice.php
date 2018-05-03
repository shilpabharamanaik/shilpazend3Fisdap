<?php namespace Fisdap\Ascend\Greatplains\Contracts;

use Fisdap\Ascend\Greatplains\Contracts\Models\Entity;

/**
 * Interface SalesInvoice
 *
 * Represent a sales invoice entity
 *
 * @package Fisdap\Ascend\Greatplains\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface SalesInvoice extends Entity
{
    const ID_FIELD = 'Id';
    const CUSTOMER_ID_FIELD = 'CustomerId';
    const BATCH_ID_FIELD = 'BatchId';
    const DATE_FIELD = 'Date';
    const LINES_FIELD = 'Lines';
    const PAYMENTS_FIELD = 'Payments';

    /**
     * Get sales invoice id
     *
     * @return string
     */
    public function getId();

    /**
     * Get sales invoice customer id
     *
     * @return string
     */
    public function getCustomerId();

    /**
     * Get sales invoice batch id
     *
     * @return string
     */
    public function getBatchId();

    /**
     * Get sales invoice date
     *
     * @return string
     */
    public function getDate();

    /**
     * Get sales invoice lines
     *
     * @return SalesInvoiceLinesCollection
     */
    public function getLines();

    /**
     * Get sales invoice payments
     *
     * @return SalesInvoicePaymentsCollection
     */
    public function getPayments();
}
