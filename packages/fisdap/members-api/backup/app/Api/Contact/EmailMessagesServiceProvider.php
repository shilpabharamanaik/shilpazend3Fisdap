<?php namespace Fisdap\Api\Contact;

use Fisdap\Api\Contact\Http\ContactsController;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

/**
 * Class EmailMessageServiceProvider
 * @package Fisdap\Api\Contact
 * @author  Isaac White <iwhite@fisdap.net>
 * @codeCoverageIgnore
 * TODO: Add middleware
 */
final class EmailMessagesServiceProvider extends ServiceProvider
{
    public function boot(Router $router)
    {
        parent::boot($router);
    }

    public function map(Router $router)
    {
        $router->post('contact-us', [
            'as' => 'contact-us',
            'uses' => ContactsController::class . '@contactUs',
        ]);
    }
}
