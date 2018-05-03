<?php namespace Fisdap\Controller\Plugin;

use Fisdap\Api\Client\Auth\UserAuthorization;
use Fisdap\Api\Users\CurrentUser\CurrentUser;
use GuzzleHttp\Client;
use Illuminate\Contracts\Container\Container;
use Psr\Log\LoggerInterface;
use Zend_Auth;
use Zend_Controller_Plugin_Abstract;
use Zend_Controller_Request_Abstract;
use Zend_Registry;
use Zend_Session_Namespace;


/**
 * Ensures that the current user has a valid IDMS token for use with the MRAPI Client
 *
 * @package Fisdap\Controller\Plugin
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class IdmsToken extends Zend_Controller_Plugin_Abstract
{
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

    /**
     * @var CurrentUser
     */
    private $currentUser;


    public function __construct()
    {
        $this->container = Zend_Registry::get('container');
        $this->logger = Zend_Registry::get('logger');
        $this->currentUser = $this->container->make(CurrentUser::class);

        $this->idmsSessionNamespace = new Zend_Session_Namespace('IDMS');
    }


    /**
     * @inheritdoc
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {

            /*
             * If no IDMS token exists in the session, or the token has expired,
             * get a new token and save it in the session
             */
            if ( ! isset($this->idmsSessionNamespace->token)) {
                $this->saveIdmsToken();
            } elseif ($this->idmsSessionNamespace->expiresAt <= time()) {
                $this->logger->debug("IDMS token expired at {$this->idmsSessionNamespace->expiresAt}");
                $this->saveIdmsToken();
            }

            $userContextId = $this->currentUser->context()->getId();

            // bind a UserAuthorization class in the service container for use by the MRAPI Client
            $userAuthorization = new UserAuthorization($this->idmsSessionNamespace->token, $userContextId);

            $this->container->instance('Fisdap\Api\Client\Auth\UserAuthorization', $userAuthorization);

//            $this->logger->debug("User authorized for MRAPI client", (array) $userAuthorization);
        }
    }


    private function saveIdmsToken()
    {
        $this->logger->debug('Getting access token from IDMS...');

        $idmsConfig = $this->container->make('config')->get('idms');

        $idmsClient = new Client;

        $idmsResponse = $idmsClient->post($idmsConfig['base_url'] . '/token',
            [
                'auth' => [
                    $idmsConfig['client_id'],
                    $idmsConfig['client_secret']
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ]
            ]
        );

        $idmsResponse = json_decode($idmsResponse->getBody()->getContents(), true);
        $this->logger->debug('IDMS Token', $idmsResponse);

        $this->idmsSessionNamespace->token = $idmsResponse['access_token'];
        $this->idmsSessionNamespace->expiresAt = time() + $idmsResponse['expires_in'];
    }

    public function clearIdmsToken() {
        $this->idmsSessionNamespace->token = NULL;
    }
}