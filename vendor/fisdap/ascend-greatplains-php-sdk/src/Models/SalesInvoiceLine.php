<?php namespace Fisdap\Ascend\Greatplains\Models;

use Fisdap\Ascend\Greatplains\Contracts\SalesInvoiceLine as SalesInvoiceLineInterface;

/**
 * Class SalesInvoiceLine
 *
 * Entity representing a sales invoice line
 *
 * @package Fisdap\Ascend\Greatplains\Models
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class SalesInvoiceLine implements SalesInvoiceLineInterface
{
    /**
     * @var string
     */
    private $itemId;

    /**
     * @var integer
     */
    private $quantity;

    /**
     * @var float
     */
    private $unitPrice;

    /**
     * @var float
     */
    private $discountAmount;

    /**
     * @var float
     */
    protected $discountPercent;

    /**
     * @var float
     */
    private $unitCost;

    /**
     * SalesInvoiceLine constructor.
     * @param $itemId
     * @param $quantity
     * @param $unitPrice
     * @param $discountAmount
     * @param $discountPercent
     * @param $unitCost
     */
    public function __construct($itemId, $quantity, $unitPrice, $discountAmount, $discountPercent, $unitCost)
    {
        $this->itemId = $itemId;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->discountAmount = $discountAmount;
        $this->discountPercent = $discountPercent;
        $this->unitCost = $unitCost;
    }

    /**
     * Get item id
     *
     * @return string
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Get unit price
     *
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * Get discount amount
     *
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * Get discount percent
     *
     * @return float
     */
    public function getDiscountPercent()
    {
        return $this->discountPercent;
    }

    /**
     * Get unit cost
     *
     * @return float
     */
    public function getUnitCost()
    {
        return $this->unitCost;
    }

    /**
     * Get sales invoice line as array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::ITEM_ID_FIELD          => $this->getItemId(),
            self::QUANTITY_FIELD         => $this->getQuantity(),
            self::UNIT_PRICE_FIELD       => $this->getUnitPrice(),
            self::DISCOUNT_AMOUNT_FIELD  => $this->getDiscountAmount(),
            self::DISCOUNT_PERCENT_FIELD => $this->getDiscountPercent(),
            self::UNIT_COST_FIELD        => $this->getUnitCost()
        ];
    }
}
