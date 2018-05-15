<?php namespace Fisdap\Members\Commerce\Listeners;

use Doctrine\ORM\EntityManager;
use Fisdap\Ascend\Greatplains\CreateCustomerCommand;
use Fisdap\Ascend\Greatplains\CreateSalesInvoiceCommand;
use Fisdap\Ascend\Greatplains\UpdateCustomerCommand;
use Fisdap\Data\Order\OrderRepository;
use Fisdap\Data\Program\ProgramLegacyRepository;
use Fisdap\Entity\Order;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Members\Commerce\Events\CustomerWasAdded;
use Fisdap\Members\Commerce\Events\CustomerWasUpdated;
use Fisdap\Members\Commerce\Events\OrderWasCompleted;
use Fisdap\Members\Commerce\Services\CustomerTransformer;
use Fisdap\Members\Commerce\Services\SalesInvoiceTransformer;
use Fisdap\Members\Queue\RefreshesDatabaseConnection;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

/**
 * Class SendOrderToGreatPlains
 *
 * @package Fisdap\Members\Commerce\Listeners
 * @author  Sam Tape <stape@fisdap.net>
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class SendOrderToGreatPlains implements ShouldQueue
{
    use RefreshesDatabaseConnection;


    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \ExceptionLogger
     */
    private $exceptionLogger;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var ProgramLegacyRepository
     */
    private $programLegacyRepository;

    /**
     * @var CreateCustomerCommand
     */
    private $createCustomerCommand;

    /**
     * @var CustomerTransformer
     */
    private $customerTransformer;

    /**
     * @var SalesInvoiceTransformer
     */
    private $salesInvoiceTransformer;

    /**
     * @var CreateSalesInvoiceCommand
     */
    private $createSalesInvoiceCommand;

    /**
     * @var UpdateCustomerCommand
     */
    private $updateCustomerCommand;


    /**
     * SendOrderToGreatPlains constructor.
     *
     * @param LoggerInterface           $logger
     * @param \ExceptionLogger          $exceptionLogger
     * @param EntityManager             $entityManager
     * @param OrderRepository           $orderRepository
     * @param ProgramLegacyRepository   $programLegacyRepository
     * @param CreateCustomerCommand     $createCustomerCommand
     * @param CustomerTransformer       $customerTransformer
     * @param SalesInvoiceTransformer   $salesInvoiceTransformer
     * @param CreateSalesInvoiceCommand $createSalesInvoiceCommand
     * @param UpdateCustomerCommand     $updateCustomerCommand
     */
    public function __construct(
        LoggerInterface $logger,
        \ExceptionLogger $exceptionLogger,
        EntityManager $entityManager,
        OrderRepository $orderRepository,
        ProgramLegacyRepository $programLegacyRepository,
        CreateCustomerCommand $createCustomerCommand,
        CustomerTransformer $customerTransformer,
        SalesInvoiceTransformer $salesInvoiceTransformer,
        CreateSalesInvoiceCommand $createSalesInvoiceCommand,
        UpdateCustomerCommand $updateCustomerCommand
    ) {
        $this->logger = $logger;
        $this->exceptionLogger = $exceptionLogger;
        $this->entityManager = $entityManager;
        $this->orderRepository = $orderRepository;
        $this->programLegacyRepository = $programLegacyRepository;
        $this->createCustomerCommand = $createCustomerCommand;
        $this->customerTransformer = $customerTransformer;
        $this->salesInvoiceTransformer = $salesInvoiceTransformer;
        $this->createSalesInvoiceCommand = $createSalesInvoiceCommand;
        $this->updateCustomerCommand = $updateCustomerCommand;
    }

    
    /**
     * @param OrderWasCompleted $event
     */
    public function whenOrderWasCompleted(OrderWasCompleted $event)
    {
        $this->refreshDbConnection();

        $this->entityManager->getConnection()->exec('SET SESSION wait_timeout = 300');

        /** @var Order $order */
        $order = $this->orderRepository->getOneById($event->getOrderId());

        try {
            if ($salesInvoiceBuilder = $this->salesInvoiceTransformer->transformToGPSalesInvoice($order)) {
                $this->logger->debug('Created sales invoice builder', ['orderId' => $order->id]);
                
                $salesInvoice = $this->createSalesInvoiceCommand->handle($salesInvoiceBuilder);
                
                $this->logger->info('Created sales invoice', [
                    'id' => $salesInvoice->getId(),
                    'customerId' => $salesInvoice->getCustomerId()
                ]);
            }

            $order->setAccountingProcessed(true);
            $this->orderRepository->update($order);
        } catch (\Exception $e) {
            $order->setAccountingProcessed(false);
            $this->orderRepository->update($order);
            
            $context = ['orderId' => $order->id];

            if ($e instanceof RequestException) {
                $context['response'] = json_decode($e->getResponse()->getBody(), true);
            }

            $this->logger->error('Failed sending order to Great Plains', $context);
            $this->exceptionLogger->log($e);
        }
    }

    
    /**
     * @param CustomerWasAdded $event
     */
    public function whenCustomerWasAdded(CustomerWasAdded $event)
    {
        /** @var ProgramLegacy $program */
        $program = $this->programLegacyRepository->getOneById($event->getProgramId());
        
        $customerBuilder = $this->customerTransformer->transformToGPCustomer($program);
        $customer = $this->createCustomerCommand->handle($customerBuilder);

        $this->logger->info('Created customer', [
            'id'   => $customer->getId(),
            'name' => $customer->getName()
        ]);
    }

    
    /**
     * @param CustomerWasUpdated $event
     */
    public function whenCustomerWasUpdated(CustomerWasUpdated $event)
    {
        /** @var ProgramLegacy $program */
        $program = $this->programLegacyRepository->getOneById($event->getProgramId());

        $customerBuilder = $this->customerTransformer->transformToGPCustomer($program);
        $customer = $this->updateCustomerCommand->handle($customerBuilder);

        $this->logger->info('Updated customer', [
            'id'   => $customer->getId(),
            'name' => $customer->getName()
        ]);
    }

    
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(
            OrderWasCompleted::class,
            self::class . '@whenOrderWasCompleted'
        );

        $events->listen(
            CustomerWasAdded::class,
            self::class . '@whenCustomerWasAdded'
        );

        $events->listen(
            CustomerWasUpdated::class,
            self::class . '@whenCustomerWasUpdated'
        );
    }
}
