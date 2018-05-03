# Members REST API Client - PHP

This library provides an API client and PHP SDK for use with the Fisdap Members REST API (MRAPI) and Identity Management System (IDMS) web services.

## Installation

Installation in a PHP project is accomplished with [Composer](http://getcomposer.org). As Fisdap uses [Toran Proxy](https://toran.fisdap.net) to handle private Composer packages, the following repository configuration should be added to your project's `composer.json`:

```
#!json
"repositories": [
    {
      "type": "composer",
      "url": "https://toran.fisdap.net/repo/private/"
    },
    {
      "type": "composer",
      "url": "https://toran.fisdap.net/repo/packagist/"
    },
    {"packagist": false}
  ]
```

Then run: `composer require fisdap/members-rest-api-client:1.*`

## Configuration

If using Laravel 4, you can run `php artisan config:publish fisdap/members-rest-api-client` to copy the configuration file to `app/config/packages/fisdap/members-rest-api-client/config.php`. Otherwise you'll need to manually copy the file from `vendor/fisdap/members-rest-api-client/config` to your project's root.

By default, the client will look for the `MRAPI_CLIENT_BASEURL` environment variable to determine which MRAPI server to use.  Alternatively, you may edit the config file, setting the `baseUrl` parameter accordingly.

### Application Bootstrap

1. If your application uses Laravel's Service Container, you should add the `Fisdap\Api\Client\ApiClientServiceProvider` to your list of providers, or otherwise call the provider's `register()` method during your application's bootstrap.

2. As the MRAPI web service requires an IDMS (OAuth2) token to be sent on each request, your application will need to be responsible for retrieving an IDMS token on behalf of a user, and use that data to instantiate a `Fisdap\Api\Client\Auth\UserAuthorization` class and bind it to the container. Here's an example Zend Framework 1.x Controller plugin, which uses the Guzzle HTTP client (bundled with this library) to help achieve this:

```
#!php

<?php namespace Fisdap\Controller\Plugin;

use Fisdap\Api\Client\Auth\UserAuthorization;
use Fisdap\Entity\User;
use GuzzleHttp\Client;
use Illuminate\Container\Container;
use Psr\Log\LoggerInterface;
use Zend_Auth;
use Zend_Config;
use Zend_Controller_Plugin_Abstract;
use Zend_Controller_Request_Abstract;
use Zend_Registry;
use Zend_Session_Namespace;


/**
 * Class IdmsToken
 *
 * @package Fisdap\Controller\Plugin
 */
class IdmsToken extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var Zend_Config
     */
    private $config;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Zend_Session_Namespace
     */
    private $idmsSessionNamespace;



    public function __construct()
    {
        $this->config = Zend_Registry::get('config');
        $this->container = Zend_Registry::get('container');
        $this->logger = Zend_Registry::get('logger');

        $this->idmsSessionNamespace = new Zend_Session_Namespace('IDMS');
    }


    /**
     * @inheritdoc
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {

            // todo - update role id retrieval after MRAPI 2.1.0 is released
            $userRoleId = User::getLoggedInUser()->getCurrentRole()->id;


            if ( ! isset($this->idmsSessionNamespace->token)) {
                $this->saveIdmsToken();
            } elseif ($this->idmsSessionNamespace->expiresAt <= time()) {
                $this->logger->debug("IDMS token expired at {$this->idmsSessionNamespace->expiresAt}");
                $this->saveIdmsToken();
            }

            // bind UserAuthorization class in container for use by MRAPI Client
            $userAuthorization = new UserAuthorization($this->idmsSessionNamespace->token, $userRoleId);

            $this->container->instance('Fisdap\Api\Client\Auth\UserAuthorization', $userAuthorization);

//            $this->logger->debug("User authorized for MRAPI client", (array) $userAuthorization);
        }
    }


    private function saveIdmsToken()
    {
        $this->logger->debug('Getting access token from IDMS...');

        $idmsConfig = $this->config->get('idms');

        $idmsClient = new Client();

        $idmsRequest = $idmsClient->createRequest('POST', $idmsConfig->token_url, [
            'auth' => [$idmsConfig->client_id, $idmsConfig->client_secret]
        ]);
        $idmsRequestBody = $idmsRequest->getBody();
        $idmsRequestBody->setField('grant_type', 'client_credentials');

        $idmsResponse = $idmsClient->send($idmsRequest)->json();

        $this->logger->debug('IDMS Token', $idmsResponse);

        $this->idmsSessionNamespace->token = $idmsResponse['access_token'];
        $this->idmsSessionNamespace->expiresAt = time() + $idmsResponse['expires_in'];
    }
}
```

## Usage

Instead of accessing data through a Repository class, this library provides a set of Gateways, which encapsulate all HTTP activity, and can return data in `stdClass` or array formats. Gateways should be retrieved through your application's service container, either through a `make()` call, or through dependency injection.

For example, to retrieve a list of attachments for a particular shift as an associative array:

```
#!php

$shiftAttachmentsGateway = $container->make(ShiftAttachmentsGateway::class);

$shiftAttachments = $shiftAttachmentsGateway->setResponseType(CommonHttpGateway::RESPONSE_TYPE_ARRAY)
    ->get(4321927);
```

Removing the `setResponseType()` call will cause the Gateway to default to returning an array of `stdClass` objects.

Take a look at `workbench.php` at the root of this library for some more examples.