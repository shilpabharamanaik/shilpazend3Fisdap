<?php namespace Fisdap\Members\Commerce\Console;

use Fisdap\Data\Discount\DiscountLegacyRepository;
use Fisdap\Data\Order\OrderRepository;
use Fisdap\Data\Product\ProductRepository;
use Fisdap\Entity\OrderConfiguration;
use Fisdap\Entity\Product;
use Illuminate\Console\Command;

/**
 * Class ExportOrdersCommand
 * @package Fisdap\Members\Commerce
 * @author Sam Tape <stape@fisdap.net>
 */
class ExportOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commerce:export-orders
                            {startDate : The start date of what orders should be included}
                            {endDate? : The end date of what orders should be included}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export a CSV file container Fisdap order information';

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var DiscountLegacyRepository
     */
    private $discountLegacyRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * Create a new command instance.
     * @param OrderRepository $orderRepository
     * @param ProductRepository $productRepository
     * @param DiscountLegacyRepository $discountLegacyRepository
     */
    public function __construct(OrderRepository $orderRepository, ProductRepository $productRepository, DiscountLegacyRepository $discountLegacyRepository)
    {
        parent::__construct();

        $this->productRepository = $productRepository;
        $this->discountLegacyRepository = $discountLegacyRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $startDate = $this->argument('startDate');
        $endDate = $this->argument('endDate') ? $this->argument('endDate') : null;

        $orders = $this->orderRepository->getAllOrders(['startDate' => $startDate, 'endDate' => $endDate]);

        foreach ($orders as $order) {
            $discounts = $this->discountLegacyRepository->getCurrentDiscounts($order->program->id, $order->order_date);

            // If this is a free order, skip
            if ($order->isFreeOrder()) {
                continue;
            }

            switch ($order->payment_method->id) {
                case 1:
                    $type = 'Invoice';
                    break;
                case 2:
                    $type = 'Sales Receipt';
                    break;
                default:
                    $type = $order->individual_purchase ? 'Sales Receipt' : 'N/A';
            }

            /** @var OrderConfiguration $configs */
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
                        $packageProducts = $this->productRepository->getProducts($config->package->configuration, true, false, false, true, $order->program->profession->id);

                        foreach ($packageProducts as $product) {
                            $price = $product->quickbooks_info->package_discount_price;

                            $row = [];
                            $row[] = $type;
                            $row[] = $order->order_date->format('m/d/Y');
                            $row[] = $order->id;
                            $row[] = $order->invoice_number ? $order->invoice_number : "N/A";
                            $row[] = $order->state;
                            $row[] = $order->program->customer_name;
                            $row[] = $order->program->customer_id;
                            $row[] = $product->quickbooks_info->package_item_name;
                            $row[] = $config->quantity;
                            $row[] = $price;
                            $row[] = $config->quantity * $price;
                            echo implode("\t", $row) . "\n";
                        }

                        //Subtract these products from the overall configuration so we don't list them twice
                        $configuration -= $config->package->configuration;
                    }
                }

                //If there are still products left, add them to the invoice/sales receipt
                if ($configuration > 0) {
                    $products = $this->productRepository->getProducts($configuration, true, false, false, true, $order->program->profession->id);
                    foreach ($products as $product) {
                        //Add row for this item
                        $row = [];
                        $row[] = $type;
                        $row[] = $order->order_date->format('m/d/Y');
                        $row[] = $order->id;
                        $row[] = $order->invoice_number ? $order->invoice_number : "N/A";
                        $row[] = $order->state;
                        $row[] = $order->program->customer_name;
                        $row[] = $order->program->customer_id;
                        $row[] = $product->quickbooks_info->item_name;
                        $row[] = $config->quantity;
                        $row[] = $product->price;
                        $row[] = $config->quantity * $product->price;
                        echo implode("\t", $row) . "\n";

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
                            $priceDiff = round($product->price - $currentPrice, 2);
                            $row = [];
                            $row[] = $type;
                            $row[] = $order->order_date->format('m/d/Y');
                            $row[] = $order->id;
                            $row[] = $order->invoice_number ? $order->invoice_number : "N/A";
                            $row[] = $order->state;
                            $row[] = $order->program->customer_name;
                            $row[] = $order->program->customer_id;
                            $row[] = $product->quickbooks_info->item_name;
                            $row[] = $config->quantity;
                            $row[] = "-" . $priceDiff;
                            $row[] = "-" . $config->quantity * $priceDiff;
                            echo implode("\t", $row) . "\n";
                        }
                    }
                }
            }
        }
    }
}
