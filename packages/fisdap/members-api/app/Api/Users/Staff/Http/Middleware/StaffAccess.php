<?php namespace Fisdap\Api\Users\Staff\Http\Middleware;

use Closure;
use Fisdap\Entity\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Foundation\Application;
use Psr\Log\LoggerInterface;


/**
 * Provides 'before' filter to allow Fisdap employees to perform any request
 *
 * @package Fisdap\Api\Users\Staff\Http\Middleware
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class StaffAccess
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var AuthManager
     */
    private $auth;

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @param Application     $app
     * @param AuthManager     $auth
     * @param LoggerInterface $logger
     */
    public function __construct(Application $app, AuthManager $auth, LoggerInterface $logger)
    {
        $this->app = $app;
        $this->auth = $auth;
        $this->logger = $logger;
    }


    public function handle($request, Closure $next)
    {
        /** @var User $user */
        $user = $this->auth->guard()->user();

        if ($user !== null) {
            if ($user->isStaff()) {
                $this->logger->debug("User '{$user->getId()}' is staff, middleware will be disabled");
                $this->app->instance('middleware.disable', true);
            }
        }

        return $next($request);
    }
}