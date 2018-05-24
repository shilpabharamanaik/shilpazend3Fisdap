<?php namespace Fisdap\Api\Auth\Http\Middleware;

use Closure;
use Doctrine\ORM\EntityManager;
use Fisdap\Api\ServiceAccounts\Entities\ServiceAccount;
use Fisdap\Logging\ClassLogging;
use Illuminate\Auth\AuthManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use OAuth2\HttpFoundationBridge\Request as BridgeRequest;
use OAuth2\HttpFoundationBridge\Response as BridgeResponse;
use OAuth2\Server as OAuth2Server;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;


/**
 * Effectively an OAuth2 Resource Server, this middleware performs OAuth2 token
 * verification and stores the User entity in Laravel's Authentication service
 *
 * @package Fisdap\Api\Auth
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class OAuth2ResourceServer
{
    use ClassLogging;


    const WWW_AUTHENTICATE_REALM = 'Fisdap Members API';

    /**
     * @var Application
     */
    private $app;
    
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var OAuth2Server
     */
    private $oauth2Server;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var AuthManager
     */
    private $auth;

    /**
     * @var EntityManager
     */
    private $entityManager;


    /**
     * @param Application   $app
     * @param Request       $request
     * @param Router        $router
     * @param OAuth2Server  $oauth2Server
     * @param Config        $config
     * @param AuthManager   $auth
     * @param EntityManager $entityManager
     */
    public function __construct(
        Application $app,
        Request $request,
        Router $router,
        OAuth2Server $oauth2Server,
        Config $config,
        AuthManager $auth,
        EntityManager $entityManager
    ) {
        $this->app = $app;
        $this->request = $request;
        $this->router = $router;
        $this->oauth2Server = $oauth2Server;
        $this->config = $config;
        $this->auth = $auth;
        $this->entityManager = $entityManager;
    }


    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->guard()->check()) {
            return $next($request);
        }
        
        // skip authentication for specific request URIs
        foreach ($this->config->get('oauth2.requestUrisToSkip', []) as $requestUriToSkip) {
            if (preg_match("%$requestUriToSkip%", $this->request->getRequestUri())) {
                return $next($request);
            }
        }

        // skip authentication for the OPTIONS method (for CORS)
        if ($request->getMethod() == 'OPTIONS') {
            return $next($request);
        }

        // setup OAuth2 Resource Server
        $bridgeRequest = BridgeRequest::createFromRequest($this->request);

        // fix bug in duplication of access_token when using query string
        if ($bridgeRequest->query->has('access_token') && $bridgeRequest->request->has('access_token')) {
            $bridgeRequest->request->set('access_token', null);
        }

        $bridgeResponse = new BridgeResponse();

        $this->authorizeToken($bridgeRequest, $bridgeResponse);

        $tokenData = $this->oauth2Server->getAccessTokenData($bridgeRequest);

        if ($tokenData['scope'] === 'service') {
            $this->handleServiceAccounts($tokenData);
        } else {
            $this->handleUsers($tokenData);
        }

        return $next($request);
    }


    /**
     * @param BridgeRequest  $bridgeRequest
     * @param BridgeResponse $bridgeResponse
     */
    private function authorizeToken(BridgeRequest $bridgeRequest, BridgeResponse $bridgeResponse)
    {
        // Respond with an error if token validation fails
        if ( ! $this->oauth2Server->verifyResourceRequest($bridgeRequest, $bridgeResponse)) {

            // replace OAuth2 server generic "Service" realm with something proper
            $wwwAuthHeader = str_replace(
                'realm="Service"',
                'realm="' . self::WWW_AUTHENTICATE_REALM . '"',
                $bridgeResponse->headers->get('WWW-Authenticate')
            );

            $responseContent = json_decode($bridgeResponse->getContent(), true);

            if (isset($responseContent['error_description'])) {
                $message = $responseContent['error_description'];
            } else {
                $message = 'Invalid access token';
            }

            throw new UnauthorizedHttpException($wwwAuthHeader, $message, null, $bridgeResponse->getStatusCode());
        }
    }


    /**
     * @param array $tokenData
     */
    private function handleServiceAccounts(array $tokenData)
    {
        $serviceAccount = null;
        $route = null;

        try {
            /** @var ServiceAccount $serviceAccount */
            $serviceAccount = $this->entityManager->find(ServiceAccount::class, $tokenData['client_id']);
            $route = $this->router->getRoutes()->match($this->request);
        } catch (\Exception $e) {
            $this->classLogger->critical($e->getMessage());

            $this->unauthorized("An unknown error occurred when trying to find the service account record");
        }

        if ($serviceAccount === null) {
            $this->unauthorized("No service account exists for client_id {$tokenData['client_id']}");
        }

        if ($serviceAccount->hasPermission($route->getName()) === false) {
            $this->unauthorized(
                "{$serviceAccount->getOauth2ClientId()} does not have permission to access the {$route->getName()} route"
            );
        }

        $this->app->instance('middleware.disable', true);
    }


    /**
     * Get user from token data and login
     *
     * @param array $tokenData
     */
    private function handleUsers(array $tokenData)
    {
        // todo - rename this header to user-context-id
        $tokenData['userContextId'] = (int) $this->request->header('fisdap-members-user-role-id');

        if ( ! $this->auth->guard()->validate($tokenData)) {
            $this->unauthorized('No user-identifying information was found in an access token or request headers');
        }

        $this->classLogDebug('User authenticated with OAuth2 token', $tokenData);
    }


    /**
     * @param $message
     */
    private function unauthorized($message)
    {
        throw new UnauthorizedHttpException('Bearer realm="' . self::WWW_AUTHENTICATE_REALM . '"', $message);
    }
}