<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Models\Transformers;

use Fisdap\Ascend\Greatplains\Contracts\Support\JsonSerializable;
use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\EntityTransformer;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\PersistentEntityTransformer;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\SalesInvoiceTransformer as SalesInvoiceTransformerInterface;
use Fisdap\Ascend\Greatplains\Models\Transformers\SalesInvoiceTransformer;
use Fisdap\Ascend\Greatplains\Models\SalesInvoice;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoiceLinesCollection;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoicePaymentsCollection;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;
use Fisdap\Ascend\Greatplains\Contracts\Models\Entity;

/**
 * Class SalesInvoiceTransformerTest
 *
 * Tests for sales invoice transformer
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Models\Transformers
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class SalesInvoiceTransformerTest extends TestCase
{
    /**
     * Test sales invoice transformer has correct contracts
     */
    public function testSalesInvoiceTransformerHasCorrectContracts()
    {
        $transformer = new SalesInvoiceTransformer();

        $this->assertInstanceOf(JsonSerializable::class, $transformer);
        $this->assertInstanceOf(Arrayable::class, $transformer);
        $this->assertInstanceOf(EntityTransformer::class, $transformer);
        $this->assertInstanceOf(PersistentEntityTransformer::class, $transformer);
        $this->assertInstanceOf(SalesInvoiceTransformerInterface::class, $transformer);
    }

    /**
     * Test sales invoice transformer can get entity
     */
    public function testSalesInvoiceTransformerCanGetEntity()
    {
        $salesInvoice = new SalesInvoice(
            '123',
            'customerId',
            'batchId',
            'date',
            mockery::mock(SalesInvoiceLinesCollection::class),
            mockery::mock(SalesInvoicePaymentsCollection::class)
        );

        $transformer = new SalesInvoiceTransformer();

        $this->assertInstanceOf(SalesInvoiceTransformerInterface::class, $transformer->setSalesInvoice($salesInvoice));
        $this->assertInstanceOf(Entity::class, $transformer->getEntity());
    }

    /**
     * Test sales invoice transfomer is persistent entity
     */
    public function testSalesInvoiceTransformerIsPersistentEntity()
    {
        $salesInvoiceLinesCollection = mockery::mock(SalesInvoiceLinesCollection::class);
        $salesInvoiceLinesCollection->shouldReceive('toArray')->andReturn([]);
        $salesInvoicePaymentsCollection = mockery::mock(SalesInvoicePaymentsCollection::class);
        $salesInvoicePaymentsCollection->shouldReceive('toArray')->andReturn([]);

        $salesInvoice = new SalesInvoice(
            '123',
            'customerId',
            'batchId',
            'date',
            $salesInvoiceLinesCollection,
            $salesInvoicePaymentsCollection
        );

        $transformer = new SalesInvoiceTransformer();
        $transformer->setSalesInvoice($salesInvoice);

        $this->assertStringMatchesFormat("api/sales-invoices", $transformer->getPersistentLocation());
        $this->assertJson($transformer->getPersistentData());

        $this->assertCount(6, $transformer->toArray());

        $this->assertArrayHasKey('Id', $transformer->toArray());
        $this->assertArrayHasKey('CustomerId', $transformer->toArray());
        $this->assertArrayHasKey('BatchId', $transformer->toArray());
        $this->assertArrayHasKey('Date', $transformer->toArray());
        $this->assertArrayHasKey('Lines', $transformer->toArray());
        $this->assertArrayHasKey('Payments', $transformer->toArray());
    }

    /**
     * Test sales invoice transformer can receive response from api
     */
    public function testSalesInvoiceTransformerCanReceiveResponseFromApi()
    {
        $transformer = new SalesInvoiceTransformer();

        $this->assertInstanceOf(SalesInvoiceTransformerInterface::class, $transformer->setResponse(['Data']));
    }
}
