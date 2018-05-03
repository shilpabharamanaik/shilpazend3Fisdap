<?php namespace Fisdap\Ascend\Greatplains\Contracts;

use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;

/**
 * Interface SalesInvoicePayment
 *
 * Represent a sales invoice payment
 *
 * @package Fisdap\Ascend\Greatplains\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface SalesInvoicePayment extends Arrayable
{
    const PAYMENT_AMOUNT_FIELD = 'PaymentAmount';
    const PAYMENT_CARD_TYPE_FIELD = 'PaymentCardType';
    const PAYMENT_CARD_LAST_4_FIELD = 'PaymentCardLast4';
    const CARD_EXPIRATION_DATE_FIELD = 'CardExpirationDate';
    const TRANSACTION_ID = 'TransactionId';
    const AUTHORIZATION_CODE_FIELD = 'AuthorizationCode';

    const CARD_TYPE_MVD = 'MVD';
    const CARD_TYPE_AMEX = 'AMEX';

    /**
     * Get payment amount
     *
     * @return float
     */
    public function getPaymentAmount();

    /**
     * Get payment card type
     *
     * @return string
     */
    public function getPaymentCardType();

    /**
     * Get payment card last four
     *
     * @return string
     */
    public function getPaymentCardLastFour();

    /**
     * Get card expiration date
     *
     * @return string
     */
    public function getCardExpirationDate();

    /**
     * Get transaction id
     *
     * @return string
     */
    public function getTransactionId();

    /**
     * Get authorization code
     *
     * @return string
     */
    public function getAuthorizationCode();
}
