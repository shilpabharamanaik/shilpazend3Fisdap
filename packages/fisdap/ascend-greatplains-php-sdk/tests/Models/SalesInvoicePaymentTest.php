<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Models;

use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoicePayment as SalesInvoicePaymentInterface;
use Fisdap\Ascend\Greatplains\Models\SalesInvoicePayment;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class SalesInvoicePaymentTest
 *
 * Sales invoice payment tests
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Models
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class SalesInvoicePaymentTest extends TestCase
{
    /**
     * Test sales invoice payment has correct contracts
     */
    public function testSalesInvoicePaymentHasCorrectContracts()
    {
        $payment = new SalesInvoicePayment(2.50, 'VISA', '1234', 'expdate', 'transactionabc', '123auth');

        $this->assertInstanceOf(Arrayable::class, $payment);
        $this->assertInstanceOf(SalesInvoicePaymentInterface::class, $payment);
    }

    /**
     * Test sales invoice has correct fields
     */
    public function testSalesInvoiceHasCorrectFields()
    {
        $this->assertStringMatchesFormat(SalesInvoicePaymentInterface::PAYMENT_AMOUNT_FIELD, 'PaymentAmount');
        $this->assertStringMatchesFormat(SalesInvoicePaymentInterface::PAYMENT_CARD_TYPE_FIELD, 'PaymentCardType');
        $this->assertStringMatchesFormat(SalesInvoicePaymentInterface::PAYMENT_CARD_LAST_4_FIELD, 'PaymentCardLast4');
        $this->assertStringMatchesFormat(SalesInvoicePaymentInterface::CARD_EXPIRATION_DATE_FIELD, 'CardExpirationDate');
        $this->assertStringMatchesFormat(SalesInvoicePaymentInterface::TRANSACTION_ID, 'TransactionId');
        $this->assertStringMatchesFormat(SalesInvoicePaymentInterface::AUTHORIZATION_CODE_FIELD, 'AuthorizationCode');
    }

    /**
     * Test sales invoice has correct getters
     */
    public function testSalesInvoiceHasCorrectGetters()
    {
        $payment = new SalesInvoicePayment(2.50, 'VISA', '1234', 'expdate', 'transaction123', '123auth');

        $this->assertEquals(2.50, $payment->getPaymentAmount());
        $this->assertStringMatchesFormat('VISA', $payment->getPaymentCardType());
        $this->assertStringMatchesFormat('1234', $payment->getPaymentCardLastFour());
        $this->assertStringMatchesFormat('expdate', $payment->getCardExpirationDate());
        $this->assertStringMatchesFormat('transaction123', $payment->getTransactionId());
        $this->assertStringMatchesFormat('123auth', $payment->getAuthorizationCode());

        $this->assertCount(6, $payment->toArray());

        $this->assertArrayHasKey('PaymentAmount', $payment->toArray());
        $this->assertArrayHasKey('PaymentCardType', $payment->toArray());
        $this->assertArrayHasKey('PaymentCardLast4', $payment->toArray());
        $this->assertArrayHasKey('CardExpirationDate', $payment->toArray());
        $this->assertArrayHasKey('TransactionId', $payment->toArray());
        $this->assertArrayHasKey('AuthorizationCode', $payment->toArray());
    }
}
