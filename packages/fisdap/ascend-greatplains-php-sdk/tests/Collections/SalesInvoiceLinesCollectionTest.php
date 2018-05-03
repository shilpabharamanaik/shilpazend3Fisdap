<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Collections;

use Fisdap\Ascend\Greatplains\Collections\SalesInvoiceLinesCollection;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoiceLine;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoiceLinesCollection as SalesInvoiceLinesCollectionInterface;
use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class SalesInvoiceLinesCollectionTest
 *
 * Tests for sales invoice lines collection
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Collections
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class SalesInvoiceLinesCollectionTest extends TestCase
{
    /**
     * Test sales invoice lines collection has correct contracts
     */
    public function testSalesInvoiceLinesCollectionHasCorrectContracts()
    {
        $salesInvoiceLinesCollection = new SalesInvoiceLinesCollection();

        $this->assertInstanceOf(SalesInvoiceLinesCollectionInterface::class, $salesInvoiceLinesCollection);
        $this->assertInstanceOf(Arrayable::class, $salesInvoiceLinesCollection);
    }

    /**
     * Test can append sales invoice line
     */
    public function testCanAppendSalesInvoiceLine()
    {
        $salesInvoiceLinesCollection = new SalesInvoiceLinesCollection();
        $salesInvoiceLine = mockery::mock(SalesInvoiceLine::class);

        $this->assertInstanceOf(SalesInvoiceLinesCollectionInterface::class, $salesInvoiceLinesCollection->append($salesInvoiceLine));
    }

    /**
     * Test can get sales invoice lines collection as array
     */
    public function testCanGetSalesInvoiceLinesCollectionAsArray()
    {
        $salesInvoiceLinesCollection = new SalesInvoiceLinesCollection();
        $salesInvoiceLine = mockery::mock(SalesInvoiceLine::class);
        $salesInvoiceLine->shouldReceive('toArray')->andReturn([]);

        $salesInvoiceLinesCollection->append($salesInvoiceLine);

        $this->assertCount(1, $salesInvoiceLinesCollection->toArray());
    }
}
