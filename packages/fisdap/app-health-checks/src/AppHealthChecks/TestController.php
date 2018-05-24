<?php namespace Fisdap\AppHealthChecks;

use Illuminate\Routing\Controller;
use Log;
use Request;


/**
 * Logs request information for testing/debugging purposes
 *
 * @package Fisdap\AppHealthChecks
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class TestController extends Controller
{
    public function test()
    {
        Log::info(
            'SERVER: ' . print_r($_SERVER, true)
            . PHP_EOL . 'ENV: ' . print_r($_ENV, true)
            . PHP_EOL . 'REQUEST: ' . print_r($_REQUEST, true)
            . PHP_EOL . 'COOKIE: ' . print_r($_COOKIE, true)
            . PHP_EOL . 'trusted proxies: ' . print_r(Request::getTrustedProxies(), true)
            . PHP_EOL . 'client IPs: ' . print_r(Request::getClientIps(), true)
            . PHP_EOL . 'is secure?: ' . print_r(Request::isSecure(), true)
        );
    }
}