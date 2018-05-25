<?php namespace Fisdap\Members\Commerce\Services;

use Fisdap\Ascend\Greatplains\Contracts\SalesInvoiceLine;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoicePayment;
use Fisdap\Ascend\Greatplains\Factories\CreateSalesInvoiceBuilder;
use Fisdap\Data\Discount\DiscountLegacyRepository;
use Fisdap\Data\Product\ProductRepository;
use Fisdap\Entity\Order;
use Fisdap\Entity\OrderConfiguration;
use Fisdap\Entity\OrderTransaction;
use Fisdap\Entity\Product;
use Fisdap\Members\Commerce\Exceptions\MissingSuccessfulBraintreeTransaction;
use Fisdap\Members\Commerce\Exceptions\SalesItemMissingId;


/**
 * Class SalesInvoiceTransformer
 * 
 * @package Fisdap\Members\Commerce\Services
 * @author Sam Tape <stape@fisdap.net>
 */
class SalesInvoiceTransformer
{
    /**
     * @var DiscountLegacyRepository
     */
    private $discountLegacyRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * SalesInvoiceTransformer constructor.
     * @param DiscountLegacyRepository $discountLegacyRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(DiscountLegacyRepository $discountLegacyRepository, ProductRepository $productRepository)
    {
        $this->discountLegacyRepository = $discountLegacyRepository;
        $this->productRepository = $productRepository;
    }


    /**
     * @param Order $order
     *
     * @return CreateSalesInvoiceBuilder|null
     * @throws MissingSuccessfulBraintreeTransaction
     * @throws SalesItemMissingId
     */
    public function transformToGPSalesInvoice(Order $order)
    {
        $lines = [];
        $payments = [];

        $discounts = $this->discountLegacyRepository->getCurrentDiscounts($order->program->id, $order->order_date);

        // if this is a free order, skip
        if ($order->isFreeOrder()) {
            return null;
        }

        if ($order->isCreditCardPurchase()) {
            $transaction = null;
            
            /** @var OrderTransaction[] $orderTransactions */
            $orderTransactions = $order->order_transactions;
            
            foreach ($orderTransactions as $orderTransaction) {

                if ($orderTransaction->success === false) continue;
                
                $transaction = $orderTransaction->getResponseObject()->transaction;
            }

            if (is_null($transaction)) {
                throw new MissingSuccessfulBraintreeTransaction(
                    "No Braintree transactions were successful for order ID {$order->id}"
                );
            }

            $payments[] = [
                SalesInvoicePayment::PAYMENT_AMOUNT_FIELD       => $transaction->amount,
                SalesInvoicePayment::PAYMENT_CARD_TYPE_FIELD    => $transaction->creditCardDetails->cardType == 'American Express' ? SalesInvoicePayment::CARD_TYPE_AMEX : SalesInvoicePayment::CARD_TYPE_MVD,
                SalesInvoicePayment::PAYMENT_CARD_LAST_4_FIELD  => $transaction->creditCardDetails->last4,
                SalesInvoicePayment::CARD_EXPIRATION_DATE_FIELD => $transaction->creditCardDetails->expirationDate,
                SalesInvoicePayment::TRANSACTION_ID             => $transaction->id,
                SalesInvoicePayment::AUTHORIZATION_CODE_FIELD   => $transaction->processorAuthorizationCode
            ];
        }

        /** @var OrderConfiguration[] $configs */
        $configs = $order->order_configurations;

        foreach ($configs as $config) {
            if ($config->free_accounts) {
                continue;
            }

            //Grab the configuration of products for this part of the order, we'll manipulate it later
            $configuration = $config->configuration;

            //Check to see if we have a package, then handle them differently
            if ($config->package->id) {
                //Compute the price of the package products individually, taking into consideration rewards discounts and coupons
                $individualPrice = Product::calculatePrice($order->program->id, $config->package->configuration, $order->coupon->id);

                //If the the package price is cheaper than the individual product sum, add it to the invoice/sales receipt
                if ($individualPrice > $config->package->price) {

                    $lines[] = $this->generateSalesInvoiceLineArray($config->package->package_ISBN, $config->quantity, $config->package->getPrice());

                    //Subtract these products from the overall configuration so we don't list them twice
                    $configuration -= $config->package->configuration;
                }
            }

            //If there are still products left, add them to the invoice/sales receipt
            if ($configuration > 0) {
                $products = $this->productRepository->getProducts($configuration, true, false, false, true, $order->program->profession->id);
                foreach($products as $product) {

                    $salesInvoiceLine = $this->generateSalesInvoiceLineArray($product->ISBN, $config->quantity, $product->price);

                    //Determine if we need to add a discount line
                    $currentPrice = $product->price;

                    //Is there a coupon for this order and does it apply to this product
                    if ($config->order->coupon->id && ($config->order->coupon->configuration & $product->configuration)) {
                        $couponPrice = $config->order->coupon->getDiscountedPrice($product->price);

                        if ($couponPrice < $currentPrice) {
                            $currentPrice = $couponPrice;
                        }
                    }

                    //Loop over applicable discounts to see if any apply
                    foreach ($discounts as $discount) {
                        if ($product->configuration & $discount->configuration) {
                            //Calculate the discounted price
                            $discountPrice = $discount->getDiscountedPrice($product->price);

                            if ($discountPrice < $currentPrice) {
                                $currentPrice = $discountPrice;
                            }
                        }
                    }

                    //Finally, let's see if we've discounted the price lower than the package,
                    //then add a line for the discount
                    if ($currentPrice < $product->price) {
                        $salesInvoiceLine[SalesInvoiceLine::DISCOUNT_AMOUNT_FIELD] = round($product->price - $currentPrice, 2);
                    }

                    $lines[] = $salesInvoiceLine;
                }
            }
        }

        return new CreateSalesInvoiceBuilder($order->id, $order->program->customer_id, '', $order->order_date->format('Y-m-d H:i:s'), $lines, $payments);
    }

    
    /**
     * Generate a sales invoice line array of data
     *
     * @param $itemId
     * @param $quantity
     * @param $unitPrice
     * @return array
     * @throws \Exception
     */
    protected function generateSalesInvoiceLineArray($itemId, $quantity, $unitPrice)
    {
        // todo - we shouldn't even display or allow products without ISBNs to be ordered, so this exception could be prevented
        if (empty($itemId)) {
            throw new SalesItemMissingId('Sales Invoice Item ID Is Empty');
        }

        return [
            SalesInvoiceLine::ITEM_ID_FIELD    => $itemId,
            SalesInvoiceLine::QUANTITY_FIELD   => $quantity,
            SalesInvoiceLine::UNIT_PRICE_FIELD => $unitPrice,
        ];
    }
}
