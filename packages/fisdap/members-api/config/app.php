<?php

use AscendLearning\Lti\LtiServiceProvider;
use Aws\Laravel\AwsServiceProvider;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Bugsnag\BugsnagLaravel\BugsnagLaravelServiceProvider;
use Fideloper\Proxy\TrustedProxyServiceProvider;
use Fisdap\AliceFixtureGenerator\FixtureGeneratorServiceProvider;
use Fisdap\Api\Attachments\ApiAttachmentEventsServiceProvider;
use Fisdap\Api\Attachments\ApiAttachmentsServiceProvider;
use Fisdap\Api\Auth\OAuth2ServiceProvider;
use Fisdap\Api\Cache\Console\CacheConsoleServiceProvider;
use Fisdap\Api\Cache\CouchbaseCacheServiceProvider;
use Fisdap\Api\Cache\DoctrineCacheServiceProvider;
use Fisdap\Api\Commerce\CommerceServiceProvider;
use Fisdap\Api\Contact\EmailMessagesServiceProvider;
use Fisdap\Api\Ethnicities\EthnicitiesServiceProvider;
use Fisdap\Api\Gender\GenderServiceProvider;
use Fisdap\Api\Http\Batching\BatchRequestServiceProvider;
use Fisdap\Api\Jobs\JobsServiceProvider;
use Fisdap\Api\Shifts\Patients\PatientsServiceProvider;
use Fisdap\Api\Shifts\Patients\Procedures\ProceduresServiceProvider;
use Fisdap\Api\Shifts\Patients\Skills\SkillsServiceProvider;
use Fisdap\Api\Shifts\Patients\Traumas\TraumasServiceProvider;
use Fisdap\Api\Products\ProductRoutesServiceProvider;
use Fisdap\Api\Products\ProductsServiceProvider;
use Fisdap\Api\Products\SerialNumbers\Events\SerialNumberEventsServiceProvider;
use Fisdap\Api\Products\SerialNumbers\SerialNumbersRoutesServiceProvider;
use Fisdap\Api\Professions\ProfessionsServiceProvider;
use Fisdap\Api\Programs\Events\ProgramEventsServiceProvider;
use Fisdap\Api\Programs\ProgramsServiceProvider;
use Fisdap\Api\ServiceAccounts\ServiceAccountsServiceProvider;
use Fisdap\Api\Shifts\Attachments\ShiftAttachmentCategoriesServiceProvider;
use Fisdap\Api\Shifts\Attachments\ShiftAttachmentsServiceProvider;
use Fisdap\Api\Shifts\ShiftsServiceProvider;
use Fisdap\Api\Shifts\Signatures\SignatureServiceProvider;
use Fisdap\Api\Support\ZendRegistryServiceProvider;
use Fisdap\Api\Timezones\TimezonesServiceProvider;
use Fisdap\Api\Users\CurrentUser\CurrentUserServiceProvider;
use Fisdap\Api\Users\UserContexts\Events\UserContextEventsServiceProvider;
use Fisdap\Api\Users\UserContexts\UserContextsServiceProvider;
use Fisdap\Api\Users\UsersServiceProvider;
use Fisdap\Api\VerificationTypes\VerificationTypesServiceProvider;
use Fisdap\AppHealthChecks\AppHealthChecksServiceProvider;
use Fisdap\Attachments\AttachmentsServiceProvider;
use Fisdap\Attachments\Categories\AttachmentCategoriesServiceProvider;
use Fisdap\Attachments\Core\AttachmentsCoreServiceProvider;
use Fisdap\Attachments\Core\ConfigProvider\AttachmentsConfigServiceProvider;
use Fisdap\BuildMetadata\BuildMetadataServiceProvider;
use Fisdap\Data\Repository\EntityRepositoryServiceProvider;
use Fisdap\ErrorHandling\ErrorHandlerServiceProvider;
use Fisdap\Logging\Commands\LoggingCommandBusServiceProvider;
use Fisdap\Logging\Events\EventLoggingServiceProvider;
use Fisdap\Logging\LoggingServiceProvider;
use Fisdap\OAuth\Storage\CouchbaseOAuthStorageServiceProvider;
use Intouch\LaravelNewrelic\NewrelicServiceProvider;
use Jlapp\Swaggervel\SwaggervelServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Settings: "single", "daily", "syslog", "errorlog"
    |
    */

    'log' => env('APP_LOG', 'single'),

    'log_level' => env('APP_LOG_LEVEL', 'debug'),

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        //Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /******************************
         ******************************
         * ORDER IS VERY IMPORTANT!!! *
         ******************************
         ******************************/

        /*
         * Doctrine 
         */
        LaravelDoctrine\ORM\DoctrineServiceProvider::class,
        ZendRegistryServiceProvider::class,
        EntityRepositoryServiceProvider::class,

        /*
         * Cache / Couchbase
         */
        CouchbaseCacheServiceProvider::class,
        CacheConsoleServiceProvider::class,
        DoctrineCacheServiceProvider::class,
        
        /*
         * Application Service Providers...
         */
	 
		// App\Providers\AppServiceProvider::class,
        // App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        // App\Providers\EventServiceProvider::class,
        // App\Providers\RouteServiceProvider::class,
		OAuth2ServiceProvider::class,
        Fisdap\Api\Providers\AppServiceProvider::class,
        Fisdap\Api\Providers\AuthServiceProvider::class,
        Fisdap\Api\Providers\EventServiceProvider::class,
        Fisdap\Api\Providers\RouteServiceProvider::class,
        JobsServiceProvider::class,
        

        /*
         * Third-party
         */
        AwsServiceProvider::class,
        IdeHelperServiceProvider::class,
        Bugsnag\BugsnagLaravel\BugsnagServiceProvider::class,
        Barryvdh\Cors\ServiceProvider::class,
        //GleanServiceProvider::class,
        NewrelicServiceProvider::class,
        TrustedProxyServiceProvider::class,
        SwaggervelServiceProvider::class,

        /*
         * Fisdap Support/Utilities
         */
        AppHealthChecksServiceProvider::class,

        AttachmentsConfigServiceProvider::class,
        AttachmentsCoreServiceProvider::class,
        AttachmentsServiceProvider::class,
        AttachmentCategoriesServiceProvider::class,

        BuildMetadataServiceProvider::class,
        CouchbaseOAuthStorageServiceProvider::class,
        ErrorHandlerServiceProvider::class,
        FixtureGeneratorServiceProvider::class,
        LoggingCommandBusServiceProvider::class,
        LoggingServiceProvider::class,
        EventLoggingServiceProvider::class,
        LtiServiceProvider::class,

        /*
         * Fisdap "Members" Domain
         */
        ApiAttachmentsServiceProvider::class,
        ApiAttachmentEventsServiceProvider::class,
        
        BatchRequestServiceProvider::class,
        CommerceServiceProvider::class,
        CurrentUserServiceProvider::class,
        GenderServiceProvider::class,
        EmailMessagesServiceProvider::class,
        EthnicitiesServiceProvider::class,
        ProductsServiceProvider::class,
        SerialNumberEventsServiceProvider::class,
        SerialNumbersRoutesServiceProvider::class,
        PatientsServiceProvider::class,
        ProceduresServiceProvider::class,
        ProductRoutesServiceProvider::class,
        ProfessionsServiceProvider::class,
        ProgramsServiceProvider::class,
        ProgramEventsServiceProvider::class,
        ServiceAccountsServiceProvider::class,
        ShiftsServiceProvider::class,
        ShiftAttachmentsServiceProvider::class,
        ShiftAttachmentCategoriesServiceProvider::class,
        SignatureServiceProvider::class,
        SkillsServiceProvider::class,
        TimezonesServiceProvider::class,
        TraumasServiceProvider::class,
        UsersServiceProvider::class,
        UserContextsServiceProvider::class,
        UserContextEventsServiceProvider::class,
        VerificationTypesServiceProvider::class,
        Propaganistas\LaravelPhone\PhoneServiceProvider::class,
		Sentry\SentryLaravel\SentryLaravelServiceProvider::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,

        'EntityManager' => LaravelDoctrine\ORM\Facades\EntityManager::class,
        'Registry'      => LaravelDoctrine\ORM\Facades\Registry::class,
        'Doctrine'      => LaravelDoctrine\ORM\Facades\Doctrine::class,
 
		'Bugsnag' => 	Bugsnag\BugsnagLaravel\Facades\Bugsnag::class,
        'Newrelic'    => \Intouch\LaravelNewrelic\Facades\Newrelic::class,
        'AWS'         => \Aws\Laravel\AwsFacade::class,
		'Sentry' => Sentry\SentryLaravel\SentryFacade::class,
    ],

];
