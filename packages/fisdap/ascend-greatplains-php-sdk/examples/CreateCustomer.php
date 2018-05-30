<?php
$config = require 'config.php';

require('../vendor/autoload.php');

use Fisdap\Ascend\Greatplains\Factories\CreateCustomerBuilder;
use Fisdap\Ascend\Greatplains\Contracts\InternetAddress;
use Fisdap\Ascend\Greatplains\Contracts\Address;
use Fisdap\Ascend\Greatplains\Contracts\Phone;
use Fisdap\Ascend\Greatplains\Repositories\CustomerRepository;
use Fisdap\Ascend\Greatplains\Models\Transformers\CustomerTransformer;
use Fisdap\Ascend\Greatplains\CreateCustomerCommand;
use Fisdap\Ascend\Greatplains\Repositories\ApiEntityManager;
use Fisdap\Ascend\Greatplains\Services\AscendGreatPlainsHttpGateway;
use Fisdap\Ascend\Greatplains\Services\LoggerAscendGreatPlainsHttpGateway;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$id = 'customid123';
$name = 'Jason Michels';
$addresses = [
    [
        Address::ID_FIELD                 => '123ghfe3',
        Address::LINE_1_FIELD             => '4908 Upton Ave S',
        Address::LINE_2_FIELD             => null,
        Address::LINE_3_FIELD             => null,
        Address::CITY_FIELD               => 'Minneapolis',
        Address::STATE_FIELD              => 'MN',
        Address::POSTAL_CODE_FIELD        => '55410',
        Address::COUNTRY_REGION_FIELD     => 'US',
        Address::INTERNET_ADDRESSES_FIELD => [
            InternetAddress::EMAIL_TO_ADDRESS_TYPE  => 'jmichels@fisdap.net',
            InternetAddress::EMAIL_CC_ADDRESS_TYPE  => 'test@test.com',
            InternetAddress::EMAIL_BCC_ADDRESS_TYPE => 'bcc@test.com'
        ],
        Address::CONTACT_PERSON_FIELD     => 'jason',
        Address::PHONE_1_FIELD            => [Phone::VALUE_FIELD => '515-494-0511', Phone::COUNTRY_CODE_FIELD => 1, Phone::EXTENSION_FIELD => null],
        Address::PHONE_2_FIELD            => null,
        Address::PHONE_3_FIELD            => null,
        Address::FAX_FIELD                => null
    ],
    [
        Address::ID_FIELD                 => '32asdf',
        Address::LINE_1_FIELD             => '5000 Test Ave S',
        Address::LINE_2_FIELD             => null,
        Address::LINE_3_FIELD             => null,
        Address::CITY_FIELD               => 'Test',
        Address::STATE_FIELD              => 'MN',
        Address::POSTAL_CODE_FIELD        => '55123',
        Address::COUNTRY_REGION_FIELD     => 'US',
        Address::INTERNET_ADDRESSES_FIELD => [
            InternetAddress::EMAIL_TO_ADDRESS_TYPE  => 'test@testing.com',
            InternetAddress::EMAIL_CC_ADDRESS_TYPE  => 'test1@test.com',
            InternetAddress::EMAIL_BCC_ADDRESS_TYPE => 'bcc2@test.com'
        ],
        Address::CONTACT_PERSON_FIELD     => 'jason',
        Address::PHONE_1_FIELD            => [Phone::VALUE_FIELD => '515-555-1234', Phone::COUNTRY_CODE_FIELD => 1, Phone::EXTENSION_FIELD => null],
        Address::PHONE_2_FIELD            => null,
        Address::PHONE_3_FIELD            => null,
        Address::FAX_FIELD                => null
    ],
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

$customerRepository = new CustomerRepository();
$customerRepository->setEntityManager($entityManager);

$customerTransformer = new CustomerTransformer();

$createCustomerCommand = new CreateCustomerCommand($customerRepository, $customerTransformer);

$customerBuilder = new CreateCustomerBuilder($id, $name, $addresses);

try {
    $customer = $createCustomerCommand->handle($customerBuilder);
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
