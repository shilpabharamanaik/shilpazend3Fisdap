<?php namespace Fisdap\Ascend\Greatplains\Contracts;

use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;

/**
 * Interface SalesInvoiceLine
 *
 * Object representing a sales invoice line
 *
 * @package Fisdap\Ascend\Greatplains\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface SalesInvoiceLine extends Arrayable
{
    const ITEM_ID_FIELD = 'ItemId';
    const QUANTITY_FIELD = 'Quantity';
    const UNIT_PRICE_FIELD = 'UnitPrice';
    const DISCOUNT_AMOUNT_FIELD = 'DiscountAmount';
    const DISCOUNT_PERCENT_FIELD = 'DiscountPercent';
    const UNIT_COST_FIELD = 'UnitCost';

    /**
     * Get the item id of a sales invoice line
     *
     * @return string
     */
    public function getItemId();

    /**
     * Get the quantity of the sales invoice line
     *
     * @return integer
     */
    public function getQuantity();

    /**
     * Get the unit price of the sales invoice line
     *
     * @return float
     */
    public function getUnitPrice();

    /**
     * Get the discount amount of the sales invoice line
     *
     * @return float
     */
    public function getDiscountAmount();

    /**
     * Get the discount percent of the sales invoice
     *
     * @return float
     */
    public function getDiscountPercent();

    /**
     * Get the unit cost of the sales invoice line
     *
     * @return float
     */
    public function getUnitCost();
}
