<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Models;

use Fisdap\Ascend\Greatplains\Collections\SalesInvoiceLinesCollection;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoiceLinesCollection as SalesInvoiceLinesCollectionInterface;
use Fisdap\Ascend\Greatplains\Collections\SalesInvoicePaymentsCollection;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoicePaymentsCollection as SalesInvoicePaymentsCollectionInterface;
use Fisdap\Ascend\Greatplains\Models\SalesInvoice;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoice as SalesInvoiceInterface;
use Fisdap\Ascend\Greatplains\Contracts\Models\Entity;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class SalesInvoiceTest
 *
 * Tests for the sales invoice model
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Models
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class SalesInvoiceTest extends TestCase
{
    /**
     * @var SalesInvoice
     */
    protected $salesInvoice;

    /**
     * Get sales invoice
     *
     * @return SalesInvoice
     */
    protected function getSalesInvoice()
    {
        if (!$this->salesInvoice) {
            $lines = new SalesInvoiceLinesCollection();
            $payments = new SalesInvoicePaymentsCollection();

            $this->salesInvoice = new SalesInvoice(
                '123',
                'customer123',
                'batch123',
                'datestuff',
                $lines,
                $payments
            );
        }

        return $this->salesInvoice;
    }

    /**
     * Test sales invoice model has correct contracts
     */
    public function testSalesInvoiceModelHasCorrectContracts()
    {
        $this->assertInstanceOf(SalesInvoiceInterface::class, $this->getSalesInvoice());
        $this->assertInstanceOf(Entity::class, $this->getSalesInvoice());
    }

    /**
     * Test sales invoice has correct fields
     */
    public function testSalesInvoiceHasCorrectFields()
    {
        $this->assertStringMatchesFormat(SalesInvoiceInterface::ID_FIELD, 'Id');
        $this->assertStringMatchesFormat(SalesInvoiceInterface::CUSTOMER_ID_FIELD, 'CustomerId');
        $this->assertStringMatchesFormat(SalesInvoiceInterface::BATCH_ID_FIELD, 'BatchId');
        $this->assertStringMatchesFormat(SalesInvoiceInterface::DATE_FIELD, 'Date');
        $this->assertStringMatchesFormat(SalesInvoiceInterface::LINES_FIELD, 'Lines');
        $this->assertStringMatchesFormat(SalesInvoiceInterface::PAYMENTS_FIELD, 'Payments');
    }

    /**
     * Test sales invoice has correct getters
     */
    public function testSalesInvoiceHasCorrectGetters()
    {
        $this->assertStringMatchesFormat('123', $this->getSalesInvoice()->getId());
        $this->assertStringMatchesFormat('customer123', $this->getSalesInvoice()->getCustomerId());
        $this->assertStringMatchesFormat('batch123', $this->getSalesInvoice()->getBatchId());
        $this->assertStringMatchesFormat('datestuff', $this->getSalesInvoice()->getDate());
        $this->assertInstanceOf(SalesInvoiceLinesCollectionInterface::class, $this->getSalesInvoice()->getLines());
        $this->assertInstanceOf(SalesInvoicePaymentsCollectionInterface::class, $this->getSalesInvoice()->getPayments());
    }
}
