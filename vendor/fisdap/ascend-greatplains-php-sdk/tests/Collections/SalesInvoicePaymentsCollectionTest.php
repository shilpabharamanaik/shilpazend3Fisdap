<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Collections;

use Fisdap\Ascend\Greatplains\Collections\SalesInvoicePaymentsCollection;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoicePayment;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoicePaymentsCollection as SalesInvoicePaymentsCollectionInterface;
use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class SalesInvoicePaymentsCollectionTest
 *
 * Tests for sales invoice payments collection
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Collections
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class SalesInvoicePaymentsCollectionTest extends TestCase
{
    /**
     * Test sales invoice payments collection has correct contracts
     */
    public function testSalesInvoicePaymentsCollectionHasCorrectContracts()
    {
        $salesInvoicePaymentsCollection = new SalesInvoicePaymentsCollection();

        $this->assertInstanceOf(SalesInvoicePaymentsCollectionInterface::class, $salesInvoicePaymentsCollection);
        $this->assertInstanceOf(Arrayable::class, $salesInvoicePaymentsCollection);
    }

    /**
     * Test can append sales invoice payment to collection
     */
    public function testCanAppendSalesInvoicePaymentToCollection()
    {
        $salesInvoicePaymentsCollection = new SalesInvoicePaymentsCollection();
        $salesInvoicePayment = mockery::mock(SalesInvoicePayment::class);

        $this->assertInstanceOf(
            SalesInvoicePaymentsCollectionInterface::class,
            $salesInvoicePaymentsCollection->append($salesInvoicePayment)
        );
    }

    /**
     * Test can get sales invoice payments collection as array
     */
    public function testCanGetSalesInvoicePaymentsCollectionAsArray()
    {
        $salesInvoicePaymentsCollection = new SalesInvoicePaymentsCollection();
        $salesInvoicePayment = mockery::mock(SalesInvoicePayment::class);
        $salesInvoicePayment->shouldReceive('toArray')->andReturn([]);

        $salesInvoicePaymentsCollection->append($salesInvoicePayment);

        $this->assertCount(1, $salesInvoicePaymentsCollection->toArray());
    }
}
