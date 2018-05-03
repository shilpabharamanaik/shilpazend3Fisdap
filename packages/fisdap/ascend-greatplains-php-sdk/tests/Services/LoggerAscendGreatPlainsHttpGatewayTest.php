<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Services;

use Fisdap\Ascend\Greatplains\Contracts\Services\ApiClient;
use Fisdap\Ascend\Greatplains\Services\LoggerAscendGreatPlainsHttpGateway;
use Psr\Log\LoggerInterface;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class LoggerAscendGreatPlainsHttpGatewayTest
 *
 * Tests for the logger ascend great plains http gateway
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Services
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class LoggerAscendGreatPlainsHttpGatewayTest extends TestCase
{
    public function testLoggerHttpGatewayHasCorrectContracts()
    {
        $logger = mockery::mock(LoggerInterface::class);
        $client = mockery::mock(ApiClient::class);

        $api = new LoggerAscendGreatPlainsHttpGateway($logger, $client);

        $this->assertInstanceOf(ApiClient::class, $api);
    }

    /**
     * Test logger http gateway can get and post data
     */
    public function testLoggerHttpGatewayCanGetAndPostData()
    {
        $logger = mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->andReturnSelf();

        $client = mockery::mock(ApiClient::class);
        $client->shouldReceive('get')->andReturn(['Data' => []]);
        $client->shouldReceive('post')->andReturn(['Data' => []]);

        $api = new LoggerAscendGreatPlainsHttpGateway($logger, $client);

        $this->assertArrayHasKey('Data', $api->get('/test/endpoint'));
        $this->assertArrayHasKey('Data', $api->post('/test/endpoint'));
    }

    /**
     * Test logger http gateway can patch data
     */
    public function testLoggerHttpGatewayCanPatchData()
    {
        $logger = mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->andReturnSelf();

        $client = mockery::mock(ApiClient::class);
        $client->shouldReceive('patch')->andReturn(['Data' => []]);

        $api = new LoggerAscendGreatPlainsHttpGateway($logger, $client);

        $this->assertArrayHasKey('Data', $api->patch('/test/endpoint'));
    }
}
