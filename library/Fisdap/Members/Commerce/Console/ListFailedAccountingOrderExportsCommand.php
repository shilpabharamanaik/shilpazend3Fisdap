<?php namespace Fisdap\Members\Commerce\Console;

use Fisdap\Data\Order\OrderRepository;
use Illuminate\Console\Command;

/**
 * Class ListFailedAccountOrderExportsCommand
 *
 * @package Fisdap\Members\Commerce
 * @author Sam Tape <stape@fisdap.net>
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class ListFailedAccountingOrderExportsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commerce:list-failed-order-exports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all orders that have failed to export to Great Plains accounting';

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    
    /**
     * Create a new command instance.
     * @param OrderRepository $orderRepository
     */
    public function __construct(OrderRepository $orderRepository)
    {
        parent::__construct();

        $this->orderRepository = $orderRepository;
    }

    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $headers = ['Order ID', 'Program', 'Order Date'];

        $orders = $this->orderRepository->getAllFailedAccountingExports();

        $orders = array_map(function ($order) {
            $order['order_date'] = $order['order_date']->format('Y-m-d H:i:s');

            return $order;
        }, $orders);

        $this->table($headers, $orders);
    }
}
