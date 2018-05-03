<?php namespace Fisdap\Ascend\Greatplains\Models;

use Fisdap\Ascend\Greatplains\Contracts\SalesInvoicePayment as SalesInvoicePaymentInterface;

/**
 * Class SalesInvoicePayment
 *
 * Represent a sales invoice payment
 *
 * @package Fisdap\Ascend\Greatplains\Models
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class SalesInvoicePayment implements SalesInvoicePaymentInterface
{
    /**
     * @var float
     */
    private $paymentAmount;

    /**
     * @var string
     */
    private $paymentCardType;

    /**
     * @var string
     */
    private $paymentCardLastFour;

    /**
     * @var string
     */
    private $cardExpirationDate;

    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $authorizationCode;

    /**
     * SalesInvoicePayment constructor.
     *
     * @param $paymentAmount
     * @param $paymentCardType
     * @param $paymentCardLastFour
     * @param $cardExpirationDate
     * @param $transactionId
     * @param $authorizationCode
     */
    public function __construct(
        $paymentAmount,
        $paymentCardType,
        $paymentCardLastFour,
        $cardExpirationDate,
        $transactionId,
        $authorizationCode
    ) {
        $this->paymentAmount = $paymentAmount;
        $this->paymentCardType = $paymentCardType;
        $this->paymentCardLastFour = $paymentCardLastFour;
        $this->cardExpirationDate = $cardExpirationDate;
        $this->transactionId = $transactionId;
        $this->authorizationCode = $authorizationCode;
    }

    /**
     * Get payment amount
     *
     * @return float
     */
    public function getPaymentAmount()
    {
        return $this->paymentAmount;
    }

    /**
     * Get payment card type
     *
     * @return string
     */
    public function getPaymentCardType()
    {
        return $this->paymentCardType;
    }

    /**
     * Get payment card last four
     *
     * @return string
     */
    public function getPaymentCardLastFour()
    {
        return $this->paymentCardLastFour;
    }

    /**
     * Get card expiration date
     *
     * @return string
     */
    public function getCardExpirationDate()
    {
        return $this->cardExpirationDate;
    }

    /**
     * Get transaction id
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Get authorization code
     *
     * @return string
     */
    public function getAuthorizationCode()
    {
        return $this->authorizationCode;
    }

    /**
     * Get sales invoice payment as array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::PAYMENT_AMOUNT_FIELD       => $this->getPaymentAmount(),
            self::PAYMENT_CARD_TYPE_FIELD    => $this->getPaymentCardType(),
            self::PAYMENT_CARD_LAST_4_FIELD  => $this->getPaymentCardLastFour(),
            self::CARD_EXPIRATION_DATE_FIELD => $this->getCardExpirationDate(),
            self::TRANSACTION_ID             => $this->getTransactionId(),
            self::AUTHORIZATION_CODE_FIELD   => $this->getAuthorizationCode()
        ];
    }
}
