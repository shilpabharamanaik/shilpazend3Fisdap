<?php
$config = require 'config.php';

require('../vendor/autoload.php');

use Fisdap\Ascend\Greatplains\CreateSalesInvoiceCommand;
use Fisdap\Ascend\Greatplains\Repositories\SalesInvoiceRepository;
use Fisdap\Ascend\Greatplains\Models\Transformers\SalesInvoiceTransformer;
use Fisdap\Ascend\Greatplains\Factories\CreateSalesInvoiceBuilder;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoiceLine;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoicePayment;
use Fisdap\Ascend\Greatplains\Repositories\ApiEntityManager;
use Fisdap\Ascend\Greatplains\Services\AscendGreatPlainsHttpGateway;
use Fisdap\Ascend\Greatplains\Services\LoggerAscendGreatPlainsHttpGateway;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$id = 'uniqueSalesID234';
$customerId = 'uniqueCustomerID223';
$batchId = 'someString';
$date = '2015-12-02 00:00:00';
$lines = [
    [
        SalesInvoiceLine::ITEM_ID_FIELD          => '9781284107395',
        SalesInvoiceLine::QUANTITY_FIELD         => 2,
        SalesInvoiceLine::UNIT_PRICE_FIELD       => 100.00,
        SalesInvoiceLine::DISCOUNT_AMOUNT_FIELD  => 1.00,
        SalesInvoiceLine::DISCOUNT_PERCENT_FIELD => 1.0,
        SalesInvoiceLine::UNIT_COST_FIELD        => 25.0,
    ],
    [
        SalesInvoiceLine::ITEM_ID_FIELD          => '9781284107395',
        SalesInvoiceLine::QUANTITY_FIELD         => 50,
        SalesInvoiceLine::UNIT_PRICE_FIELD       => 100.00,
        SalesInvoiceLine::DISCOUNT_AMOUNT_FIELD  => 1.00,
        SalesInvoiceLine::DISCOUNT_PERCENT_FIELD => 1.0,
        SalesInvoiceLine::UNIT_COST_FIELD        => 25.0,
    ]
];
$payments = [
    [
        SalesInvoicePayment::PAYMENT_AMOUNT_FIELD       => 10.0,
        SalesInvoicePayment::PAYMENT_CARD_TYPE_FIELD    => SalesInvoicePayment::CARD_TYPE_MVD,
        SalesInvoicePayment::PAYMENT_CARD_LAST_4_FIELD  => '1234',
        SalesInvoicePayment::CARD_EXPIRATION_DATE_FIELD => '10/2016',
        SalesInvoicePayment::TRANSACTION_ID             => '1234abc',
        SalesInvoicePayment::AUTHORIZATION_CODE_FIELD   => '123'
    ],
    [
        SalesInvoicePayment::PAYMENT_AMOUNT_FIELD       => 60.0,
        SalesInvoicePayment::PAYMENT_CARD_TYPE_FIELD    => SalesInvoicePayment::CARD_TYPE_AMEX,
        SalesInvoicePayment::PAYMENT_CARD_LAST_4_FIELD  => '1234',
        SalesInvoicePayment::CARD_EXPIRATION_DATE_FIELD => '05/2016',
        SalesInvoicePayment::TRANSACTION_ID             => '1234abcdefg',
        SalesInvoicePayment::AUTHORIZATION_CODE_FIELD   => '123'
    ]
];

$logger = new Logger('AscendGreatPlainsHttpGateway');
$logger->pushHandler(new StreamHandler(__DIR__.'/../logs/default.log', Logger::DEBUG));

$apiClient = new AscendGreatPlainsHttpGateway(
    $config['baseUri'],
    $config['apiKey'],
    $config['appId'],
    $config['timeout'],
    $config['debug'],
    $config['verify']
);

$loggerApiClient = new LoggerAscendGreatPlainsHttpGateway($logger, $apiClient);

$entityManager = new ApiEntityManager();
$entityManager->setApiClient($loggerApiClient);

$salesInvoiceRepository = new SalesInvoiceRepository();
$salesInvoiceRepository->setEntityManager($entityManager);

$salesInvoiceTransformer = new SalesInvoiceTransformer();

$createSalesInvoiceCommand = new CreateSalesInvoiceCommand($salesInvoiceRepository, $salesInvoiceTransformer);

$salesInvoiceBuilder = new CreateSalesInvoiceBuilder($id, $customerId, $batchId, $date, $lines, $payments);

try {
    $salesInvoice = $createSalesInvoiceCommand->handle($salesInvoiceBuilder);
    var_dump($salesInvoice);
} catch (\GuzzleHttp\Exception\ClientException $e) {
    var_dump('This happens with a 401 unauthorized transaction and 400 level errors');
} catch (\GuzzleHttp\Exception\ServerException $e) {
    var_dump('This happens for 500 errors from server');
} catch (\GuzzleHttp\Exception\RequestException $e) {
    var_dump('This covers pretty much any other errors that happen not being able to communicated with server, timeout, DNS, etc...');
} catch (\Exception $e) {
    var_dump('Some other unexpected exception happened');
}