<?php namespace Fisdap\Ascend\Greatplains\Phpunit;

use Fisdap\Ascend\Greatplains\Contracts\Factories\CreateSalesInvoiceBuilder;
use Fisdap\Ascend\Greatplains\Models\Transformers\SalesInvoiceTransformer;
use Fisdap\Ascend\Greatplains\Contracts\Repositories\SalesInvoiceRepository;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoice;
use Fisdap\Ascend\Greatplains\CreateSalesInvoiceCommand;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class CreateSalesInvoiceCommandTest
 *
 * Test create sales invoice command
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class CreateSalesInvoiceCommandTest extends TestCase
{
    /**
     * Test create sales invoice command can create sales invoice
     */
    public function testCreateSalesInvoiceCommandCanCreateSalesInvoice()
    {
        $salesInvoice = mockery::mock(SalesInvoice::class);

        $salesInvoiceTransformer = new SalesInvoiceTransformer();

        $repo = mockery::mock(SalesInvoiceRepository::class);
        $repo->shouldReceive('store')->andReturn($salesInvoiceTransformer);

        $createSalesInvoiceBuilder = mockery::mock(CreateSalesInvoiceBuilder::class);
        $createSalesInvoiceBuilder->shouldReceive('buildSalesInvoiceEntity')->andReturn($salesInvoice);

        $command = new CreateSalesInvoiceCommand($repo, $salesInvoiceTransformer);

        $this->assertInstanceOf(SalesInvoice::class, $command->handle($createSalesInvoiceBuilder));
    }
}
