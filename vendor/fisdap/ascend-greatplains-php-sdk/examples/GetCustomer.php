<?php
$config = require 'config.php';

require('../vendor/autoload.php');

use Fisdap\Ascend\Greatplains\Repositories\CustomerRepository;
use Fisdap\Ascend\Greatplains\Models\Transformers\FindCustomerFetcher;
use Fisdap\Ascend\Greatplains\GetCustomerCommand;
use Fisdap\Ascend\Greatplains\Repositories\ApiEntityManager;
use Fisdap\Ascend\Greatplains\Services\AscendGreatPlainsHttpGateway;
use Fisdap\Ascend\Greatplains\Services\LoggerAscendGreatPlainsHttpGateway;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

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

$customerRepository = new CustomerRepository();
$customerRepository->setEntityManager($entityManager);

$findCustomerFetcher = new FindCustomerFetcher();

$getCustomerCommand = new GetCustomerCommand($customerRepository, $findCustomerFetcher);

try {
    $customer = $getCustomerCommand->handle('customid123');
    var_dump($customer);
} catch (\GuzzleHttp\Exception\ClientException $e) {
    var_dump('This happens with a 401 unauthorized transaction and 400 level errors');
} catch (\GuzzleHttp\Exception\ServerException $e) {
    var_dump('This happens for 500 errors from server');
} catch (\GuzzleHttp\Exception\RequestException $e) {
    var_dump('This covers pretty much any other errors that happen not being able to communicated with server, timeout, DNS, etc...');
} catch (\Exception $e) {
    var_dump('Some other unexpected exception happened');
}