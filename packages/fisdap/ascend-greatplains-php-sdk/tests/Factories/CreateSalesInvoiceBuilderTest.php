<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Factories;

use Fisdap\Ascend\Greatplains\Contracts\Factories\CreateSalesInvoiceBuilder as CreateSalesInvoiceBuilderInterface;
use Fisdap\Ascend\Greatplains\Factories\CreateSalesInvoiceBuilder;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoice;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class CreateSalesInvoiceBuilderTest
 *
 * Tests for create sales invoice builder
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Factories
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class CreateSalesInvoiceBuilderTest extends TestCase
{
    /**
     * Test create sales invoice builder has correct contracts
     */
    public function testCreateSalesInvoiceBuilderHasCorrectContracts()
    {
        $builder = new CreateSalesInvoiceBuilder('salesInvoiceId', 'customerId', 'batchId', 'date', [], []);

        $this->assertInstanceOf(CreateSalesInvoiceBuilderInterface::class, $builder);
    }

    /**
     * Test create sales invoice builder can build sales invoice
     */
    public function testCreateSalesInvoiceBuilderCanBuildSalesInvoice()
    {
        $line = [
            'ItemId'    => 'id123',
            'Quantity'  => 100,
            'UnitPrice' => 50.00,
            'DiscountAmount'  => 10.00,
            'DiscountPercent'  => 1.0,
            'UnitCost'  => 2.00
        ];

        $payment = [
            'PaymentAmount'      => 10.00,
            'PaymentCardType'    => 'Visa',
            'PaymentCardLast4'   => '1234',
            'CardExpirationDate' => 'stuff',
            'TransactionId'      => '123abc',
            'AuthorizationCode'  => '123456',
        ];

        $builder = new CreateSalesInvoiceBuilder(
            'salesInvoiceId',
            'customerId',
            'batchId',
            'date',
            [$line, $line],
            [$payment, $payment]
        );

        $this->assertInstanceOf(SalesInvoice::class, $builder->buildSalesInvoiceEntity());
    }
}
