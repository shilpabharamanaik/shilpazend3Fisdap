<?php
date_default_timezone_set('America/Chicago');
$config = require 'config.php';

require('../vendor/autoload.php');

use Fisdap\JBL\Authentication\JblRestApiUserAuthentication;
use Fisdap\JBL\Authentication\CurlHttpClient;
use Fisdap\JBL\Authentication\LoggerCurlHttpClient;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('JBLAuthenticationHttpClient');
$logger->pushHandler(new StreamHandler(__DIR__.'/../logs/default.log', Logger::DEBUG));

$httpClient = new LoggerCurlHttpClient($logger, new CurlHttpClient());

// Make sure the base url has an ending slash "/"
$authentication = new JblRestApiUserAuthentication($config['baseUrl'], $httpClient);
$data = $authentication->authenticateUserByProductId('E69AFA4C-3894-4A1D-9810-A490CA7FC53E');

echo "<pre>";
var_dump($data);
die();
