<?php namespace Fisdap\Api\Client;

use Fisdap\Api\Client\Auth\UserAuthorization;
use Fisdap\Api\Client\HttpClient\HttpClient;
use Illuminate\Support\ServiceProvider;


/**
 * Registers Members REST API HttpClient and Gateways
 *
 * @package Fisdap\Api\Client
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ApiClientServiceProvider extends ServiceProvider
{
    const CLIENT_VERSION = '2.2.1';


    /**
     * Array mapping fully-qualified gateway interface names to concrete class names
     *
     * Must use strings (instead of class constants), to ensure PHP <=5.4 compatibility.
     *
     * @var array
     */
    private $gatewayClassMap = [
        'Fisdap\Api\Client\Users\Gateway\UsersGateway' => 'Fisdap\Api\Client\Users\Gateway\HttpUsersGateway',
        'Fisdap\Api\Client\Shifts\Attachments\Gateway\ShiftAttachmentsGateway'
            => 'Fisdap\Api\Client\Shifts\Attachments\Gateway\HttpShiftAttachmentsGateway',
        'Fisdap\Api\Client\Attachments\Categories\Gateway\AttachmentCategoriesGateway'
            => 'Fisdap\Api\Client\Attachments\Categories\Gateway\HttpAttachmentCategoriesGateway',
        'Fisdap\Api\Client\Students\Gateway\StudentsGateway'
            => 'Fisdap\Api\Client\Students\Gateway\HttpStudentsGateway',
        'Fisdap\Api\Client\Reports\Gateway\ReportsGateway'
        => 'Fisdap\Api\Client\Reports\Gateway\HttpReportsGateway',
        'Fisdap\Api\Client\Scenarios\Gateway\ScenariosGateway'
        => 'Fisdap\Api\Client\Scenarios\Gateway\HttpScenariosGateway',
    ];


    /**
     * @inheritdoc
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/mrapi-client-config.php' => config_path('mrapi-client-config.php')
        ]);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton('Fisdap\Api\Client\HttpClient\HttpClientInterface', function () {

            $config = ApiClientConfig::getInstance();

            /** @var UserAuthorization $userAuthorization */
            $userAuthorization = $this->app->make('Fisdap\Api\Client\Auth\UserAuthorization');

            return new HttpClient([
                'base_uri' => $config->getBaseUrl(),
                'headers'         => [
                    'Authorization'               => "Bearer {$userAuthorization->accessToken}",
                    'fisdap-members-user-role-id' => $userAuthorization->userRoleId,
                    'User-Agent' => 'MRAPI Client - PHP/' . self::CLIENT_VERSION,
                    'Accept' => 'application/json'
                ],
                'timeout'         => $config->getRequestTimeout(),
                'connect_timeout' => $config->getConnectionTimeout()
            ]);
        });

        $this->registerGateways();
    }


    private function registerGateways()
    {
        foreach ($this->gatewayClassMap as $interfaceName => $className) {
            $this->app->singleton($interfaceName, $className);
        }
    }
}