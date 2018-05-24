<?php namespace Fisdap\Ascend\Greatplains\Factories;

use Fisdap\Ascend\Greatplains\Contracts\Factories\CreateSalesInvoiceBuilder as CreateSalesInvoiceBuilderInterface;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoiceLine as SalesInvoiceLineInterface;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoicePayment as SalesInvoicePaymentInterface;
use Fisdap\Ascend\Greatplains\Collections\SalesInvoiceLinesCollection;
use Fisdap\Ascend\Greatplains\Collections\SalesInvoicePaymentsCollection;
use Fisdap\Ascend\Greatplains\Models\SalesInvoice;
use Fisdap\Ascend\Greatplains\Models\SalesInvoiceLine;
use Fisdap\Ascend\Greatplains\Models\SalesInvoicePayment;

/**
 * Class CreateSalesInvoiceBuilder
 *
 * Create and return sales invoice entity
 *
 * @package Fisdap\Ascend\Greatplains\Factories
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class CreateSalesInvoiceBuilder implements CreateSalesInvoiceBuilderInterface
{
    /**
     * Sales invoice id
     *
     * @var string
     */
    private $id;

    /**
     * Sales invoice customer id
     *
     * @var string
     */
    private $customerId;

    /**
     * Sales invoice batch id
     *
     * @var string
     */
    private $batchId;

    /**
     * Sales invoice date
     *
     * @var string
     */
    private $date;

    /**
     * Sales invoice lines
     *
     * @var array
     */
    private $lines;

    /**
     * Sales invoice payments
     *
     * @var array
     */
    private $payments;

    /**
     * CreateSalesInvoiceBuilder constructor.
     *
     * @param $id
     * @param $customerId
     * @param $batchId
     * @param $date
     * @param array $lines
     * @param array $payments
     */
    public function __construct($id, $customerId, $batchId, $date, $lines = [], $payments = [])
    {
        $this->id = $id;
        $this->customerId = $customerId;
        $this->batchId = $batchId;
        $this->date = $date;
        $this->lines = $lines;
        $this->payments = $payments;
    }

    /**
     * Build and return a sales invoice entity
     *
     * @return SalesInvoice
     */
    public function buildSalesInvoiceEntity()
    {
        return new SalesInvoice(
            $this->id,
            $this->customerId,
            $this->batchId,
            $this->date,
            $this->buildSalesInvoiceLinesCollection(),
            $this->buildSalesInvoicePaymentsCollection()
        );
    }

    /**
     * Build the sales invoice lines collection
     *
     * @return SalesInvoiceLinesCollection
     */
    protected function buildSalesInvoiceLinesCollection()
    {
        $salesInvoiceLinesCollection = new SalesInvoiceLinesCollection();

        if ($this->lines && is_array($this->lines)) {

            foreach ($this->lines as $line) {

                $salesInvoiceLine = new SalesInvoiceLine(
                    $line[SalesInvoiceLineInterface::ITEM_ID_FIELD],
                    $line[SalesInvoiceLineInterface::QUANTITY_FIELD],
                    $line[SalesInvoiceLineInterface::UNIT_PRICE_FIELD],
                    $line[SalesInvoiceLineInterface::DISCOUNT_AMOUNT_FIELD],
                    $line[SalesInvoiceLineInterface::DISCOUNT_PERCENT_FIELD],
                    $line[SalesInvoiceLineInterface::UNIT_COST_FIELD]
                );

                $salesInvoiceLinesCollection->append($salesInvoiceLine);

            }
        }
        return $salesInvoiceLinesCollection;
    }

    /**
     * Build the sales invoice payments collection
     *
     * @return SalesInvoicePaymentsCollection
     */
    protected function buildSalesInvoicePaymentsCollection()
    {
        $salesInvoicePaymentsCollection = new SalesInvoicePaymentsCollection();

        if ($this->payments && is_array($this->payments)) {

            foreach ($this->payments as $payment) {

                $salesInvoicePayment = new SalesInvoicePayment(
                    $payment[SalesInvoicePaymentInterface::PAYMENT_AMOUNT_FIELD],
                    $payment[SalesInvoicePaymentInterface::PAYMENT_CARD_TYPE_FIELD],
                    $payment[SalesInvoicePaymentInterface::PAYMENT_CARD_LAST_4_FIELD],
                    $payment[SalesInvoicePaymentInterface::CARD_EXPIRATION_DATE_FIELD],
                    $payment[SalesInvoicePaymentInterface::TRANSACTION_ID],
                    $payment[SalesInvoicePaymentInterface::AUTHORIZATION_CODE_FIELD]
                );

                $salesInvoicePaymentsCollection->append($salesInvoicePayment);
            }
        }
        return $salesInvoicePaymentsCollection;
    }
}
