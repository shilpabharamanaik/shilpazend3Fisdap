<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Models;

use Fisdap\Ascend\Greatplains\Models\SalesInvoiceLine;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoiceLine as SalesInvoiceLineInterface;
use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class SalesInvoiceLineTest
 *
 * Tests for sales invoice line model
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Models
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class SalesInvoiceLineTest extends TestCase
{
    /**
     * Test sales invoice line has correct contracts
     */
    public function testSalesInvoiceLineHasCorrectContracts()
    {
        $line = new SalesInvoiceLine('itemId123', 2, 10.99, 2.00, 1.0, 3.50);

        $this->assertInstanceOf(SalesInvoiceLineInterface::class, $line);
        $this->assertInstanceOf(Arrayable::class, $line);
    }

    /**
     * Test sales invoice line has correct fields
     */
    public function testSalesInvoiceLineHasCorrectFields()
    {
        $this->assertStringMatchesFormat(SalesInvoiceLineInterface::ITEM_ID_FIELD, 'ItemId');
        $this->assertStringMatchesFormat(SalesInvoiceLineInterface::QUANTITY_FIELD, 'Quantity');
        $this->assertStringMatchesFormat(SalesInvoiceLineInterface::UNIT_PRICE_FIELD, 'UnitPrice');
        $this->assertStringMatchesFormat(SalesInvoiceLineInterface::DISCOUNT_AMOUNT_FIELD, 'DiscountAmount');
        $this->assertStringMatchesFormat(SalesInvoiceLineInterface::DISCOUNT_PERCENT_FIELD, 'DiscountPercent');
        $this->assertStringMatchesFormat(SalesInvoiceLineInterface::UNIT_COST_FIELD, 'UnitCost');
    }

    /**
     * Test sales invoice line has correct getters
     */
    public function testSalesInvoiceLineHasCorrectGetters()
    {
        $line = new SalesInvoiceLine('itemId123', 2, 10.99, 2.00, 1.0, 3.50);

        $this->assertStringMatchesFormat('itemId123', $line->getItemId());
        $this->assertEquals(2, $line->getQuantity());
        $this->assertEquals(10.99, $line->getUnitPrice());
        $this->assertEquals(2.00, $line->getDiscountAmount());
        $this->assertEquals(1.0, $line->getDiscountPercent());
        $this->assertEquals(3.50, $line->getUnitCost());
    }

    /**
     * Test sales invoice line has correct to array
     */
    public function testSalesInvoiceLineHasCorrectToArray()
    {
        $line = new SalesInvoiceLine('itemId123', 2, 10.99, 2.00, 1.0, 3.50);
        $array = $line->toArray();

        $this->assertArrayHasKey('ItemId', $array);
        $this->assertArrayHasKey('Quantity', $array);
        $this->assertArrayHasKey('UnitPrice', $array);
        $this->assertArrayHasKey('DiscountAmount', $array);
        $this->assertArrayHasKey('DiscountPercent', $array);
        $this->assertArrayHasKey('UnitCost', $array);

        $this->assertCount(6, $array);
    }
}
