<?php namespace Fisdap\JBL\Authentication\Phpunit;

use Fisdap\JBL\Authentication\LoggerCurlHttpClient;
use Fisdap\JBL\Authentication\Contracts\HttpClient;
use \Mockery as mockery;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class LoggerCurlHttpClientTest
 *
 * @package Fisdap\JBL\Authentication\Phpunit
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class LoggerCurlHttpClientTest extends TestCase
{

    /**
     * Test class has correct contracts
     */
    public function testClassHasCorrectContracts()
    {
        $logger = mockery::mock(LoggerInterface::class);
        $origHttpClient = mockery::mock(HttpClient::class);

        $loggerClient = new LoggerCurlHttpClient($logger, $origHttpClient);

        $this->assertInstanceOf(HttpClient::class, $loggerClient);
    }

    /**
     * Test can make an http post request
     */
    public function testClassCanMakeHttpPostRequest()
    {
        $logger = mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->andReturnSelf();

        $origHttpClient = mockery::mock(HttpClient::class);
        $origHttpClient->shouldReceive('post')->andReturn(new stdClass());

        $loggerClient = new LoggerCurlHttpClient($logger, $origHttpClient);

        $this->assertInstanceOf(stdClass::class, $loggerClient->post('route/123'));
    }

    /**
     * Test can make an http GET request
     */
    public function testCanMakeAnHttpGetRequest()
    {
        $logger = mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->andReturnSelf();

        $origHttpClient = mockery::mock(HttpClient::class);
        $origHttpClient->shouldReceive('get')->andReturn(new stdClass());

        $loggerClient = new LoggerCurlHttpClient($logger, $origHttpClient);

        $this->assertInstanceOf(stdClass::class, $loggerClient->get('route/123'));
    }

    /**
     * Test can set headers
     */
    public function testCanSetHeaders()
    {
        $logger = mockery::mock(LoggerInterface::class);
        $origHttpClient = mockery::mock(HttpClient::class);

        $loggerClient = new LoggerCurlHttpClient($logger, $origHttpClient);

        $this->assertInstanceOf(HttpClient::class, $loggerClient);
    }
}
