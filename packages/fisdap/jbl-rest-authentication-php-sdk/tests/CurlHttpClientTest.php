<?php namespace Fisdap\JBL\Authentication\Phpunit;

use Fisdap\JBL\Authentication\CurlHttpClient;
use \Mockery as mockery;
use Fisdap\JBL\Authentication\Contracts\HttpClient;

/**
 * Class CurlHttpClientTest
 *
 * Tests for the curl http client wrapper object
 *
 * @package Fisdap\JBL\Authentication\Phpunit
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class CurlHttpClientTest extends TestCase
{
    /**
     * Test http client has correct contracts to make HTTP requests
     */
    public function testHttpClientHasCorrectContracts()
    {
        $this->assertInstanceOf(HttpClient::class, new CurlHttpClient());
    }
}
