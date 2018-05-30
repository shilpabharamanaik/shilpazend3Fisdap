<?php namespace Fisdap\Members\Commerce;

use Fisdap\Ascend\Greatplains\Contracts\Repositories\SalesInvoiceRepository as SalesInvoiceRepositoryInterface;
use Fisdap\Ascend\Greatplains\Contracts\Services\ApiClient;
use Fisdap\Ascend\Greatplains\CreateCustomerCommand;
use Fisdap\Ascend\Greatplains\CreateSalesInvoiceCommand;
use Fisdap\Ascend\Greatplains\Models\Transformers\CustomerTransformer;
use Fisdap\Ascend\Greatplains\Models\Transformers\SalesInvoiceTransformer;
use Fisdap\Ascend\Greatplains\Repositories\ApiEntityManager;
use Fisdap\Ascend\Greatplains\Contracts\Repositories\ApiEntityManager as ApiEntityManagerInterface;
use Fisdap\Ascend\Greatplains\Repositories\CustomerRepository;
use Fisdap\Ascend\Greatplains\Contracts\Repositories\CustomerRepository as CustomerRepositoryInterface;
use Fisdap\Ascend\Greatplains\Repositories\SalesInvoiceRepository;
use Fisdap\Ascend\Greatplains\Services\AscendGreatPlainsHttpGateway;
use Fisdap\Ascend\Greatplains\Services\LoggerAscendGreatPlainsHttpGateway;
use Fisdap\Ascend\Greatplains\UpdateCustomerCommand;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

/**
 * Class GreatPlainsServiceProvider
 * @package Fisdap\Members\Commerce
 * @author Sam Tape <stape@fisdap.net>
 */
class GreatPlainsServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ApiClient::class, function () {
            $gpApiConfig = $this->app->make('config')->get('great-plains-api');

            $apiClient = new AscendGreatPlainsHttpGateway(
                $gpApiConfig['baseUri'],
                $gpApiConfig['apiKey'],
                $gpApiConfig['appId'],
                $gpApiConfig['timeout'],
                $gpApiConfig['debug']
            );

            return new LoggerAscendGreatPlainsHttpGateway($this->app->make(LoggerInterface::class), $apiClient);
        });

        $this->app->singleton(ApiEntityManagerInterface::class, function () {
            $entityManager = new ApiEntityManager();
            $entityManager->setApiClient($this->app->make(ApiClient::class));

            return $entityManager;
        });

        $this->app->bind(CustomerRepositoryInterface::class, function () {
            $customerRepository = new CustomerRepository();
            $customerRepository->setEntityManager($this->app->make(ApiEntityManagerInterface::class));

            return $customerRepository;
        });

        $this->app->bind(CreateCustomerCommand::class, function () {
            $customerTransformer = new CustomerTransformer();
            $createCustomerCommand = new CreateCustomerCommand($this->app->make(CustomerRepositoryInterface::class), $customerTransformer);

            return $createCustomerCommand;
        });

        $this->app->bind(SalesInvoiceRepositoryInterface::class, function () {
            $customerRepository = new SalesInvoiceRepository();
            $customerRepository->setEntityManager($this->app->make(ApiEntityManagerInterface::class));

            return $customerRepository;
        });

        $this->app->bind(CreateSalesInvoiceCommand::class, function () {
            $salesTransformer = new SalesInvoiceTransformer();
            $salesInvoiceRepository = $this->app->make(SalesInvoiceRepositoryInterface::class);

            return new CreateSalesInvoiceCommand($salesInvoiceRepository, $salesTransformer);
        });

        $this->app->bind(UpdateCustomerCommand::class, function () {
            $customerTransformer = new CustomerTransformer();
            $updateCustomerCommand = new UpdateCustomerCommand($this->app->make(CustomerRepositoryInterface::class), $customerTransformer);

            return $updateCustomerCommand;
        });
    }
}
