<?php namespace Fisdap\Members\Commerce\Console;

use Doctrine\ORM\EntityManager;
use Fisdap\Data\Order\OrderRepository;
use Fisdap\Entity\Order;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Queries\Specifications\QueryModifiers\LeftFetchJoin;
use Happyr\DoctrineSpecification\Spec;
use Illuminate\Console\Command;
use Psr\Log\LoggerInterface;

/**
 * Class GenerateCustomerInfoCommand
 *
 * @package Fisdap\Members\Commerce
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class GenerateCustomerInfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commerce:generate-customer-info
                            {startDate : The start date of what orders should be included}
                            {endDate? : The end date of what orders should be included}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create customer names and IDs for programs and populate existing orders';

    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @param OrderRepository $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(OrderRepository $orderRepository, LoggerInterface $logger)
    {
        parent::__construct();

        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
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

        $spec = Spec::andX(
            new LeftFetchJoin('program', 'program'),
            Spec::orX(Spec::neq('order_type', 2), Spec::isNull('order_type')),
            Spec::eq('completed', 1),
            Spec::eq('customer_id', 0, 'program'),
            Spec::orderBy('order_date', 'DESC')
        );

        if (isset($startDate)) {
            $spec->andX(Spec::gt('order_date', (new \DateTime($startDate))->format('Y-m-d')));
        }

        if (isset($endDate)) {
            $spec->andX(Spec::lte('order_date', (new \DateTime($endDate))->format('Y-m-d 23:59:59')));
        }

        /** @var Order[] $orders */
        $orders = $this->orderRepository->match($spec);

        /** @var ProgramLegacy[] $programs */
        $programs = [];

        foreach ($orders as $order) {

            // If this is a free order, skip
            if ($order->staff_free_order || $order->total_cost == 0) {
                continue;
            }

            $this->logger->info(
                "Found completed order ID {$order->id} with missing customer_id for program ID {$order->program->getId()} ({$order->program->name})"
            );

            $programs[$order->program->id] = $order->program;
        }

        foreach ($programs as $program) {
            $program->generateCustomerName();

            $this->orderRepository->update($program);

            $this->logger->notice(
                "Generated customer name and ID for program ID {$program->getId()}",
                ['customer_id' => $program->customer_id, 'customer_name' => $program->customer_name]
            );
        }
    }
}
