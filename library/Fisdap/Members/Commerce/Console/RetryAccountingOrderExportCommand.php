<?php namespace Fisdap\Members\Commerce\Console;

use Fisdap\Data\Order\OrderRepository;
use Fisdap\Members\Commerce\Events\OrderWasCompleted;
use Fisdap\Members\Commerce\Listeners\SendOrderToGreatPlains;
use Illuminate\Console\Command;


/**
 * Class RetryAccountingOrderExportCommand
 * 
 * @package Fisdap\Members\Commerce
 * @author Sam Tape <stape@fisdap.net>
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class RetryAccountingOrderExportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commerce:retry-order-exports
                            {orders? : Comma-separated list of order IDs to resend to accounting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry sending orders to Great Plains accounting software';

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var SendOrderToGreatPlains
     */
    private $sendOrderToGreatPlains;

    /**
     * Create a new command instance.
     * @param OrderRepository $orderRepository
     * @param SendOrderToGreatPlains $sendOrderToGreatPlains
     */
    public function __construct(OrderRepository $orderRepository, SendOrderToGreatPlains $sendOrderToGreatPlains)
    {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->sendOrderToGreatPlains = $sendOrderToGreatPlains;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $failedOrderIds = array_column($this->orderRepository->getAllFailedAccountingExports(), 'id');

        //If the user didn't specify any specific orders to resend, use all failed orders
        if (!empty($this->argument('orders'))) {
            $orderIds = explode(',', rtrim($this->argument('orders'), ','));
        } else {
            $orderIds = $failedOrderIds;
        }

        $bar = $this->output->createProgressBar(count($orderIds));

        foreach($orderIds as $orderId) {

            if (!in_array($orderId, $failedOrderIds)) {
                if (!$this->confirm("Order #$orderId is not marked as failed, do you wish to continue resending it to accounting? [y|N]")) {
                    continue;
                }
            }

            $this->sendOrderToGreatPlains->whenOrderWasCompleted(new OrderWasCompleted($orderId));
            $bar->advance();
        }

        $bar->finish();
        
        $this->line(PHP_EOL);
    }
}
