<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Services;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Fisdap\Ascend\Greatplains\Contracts\Services\ApiClient;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use Fisdap\Ascend\Greatplains\Services\AscendGreatPlainsHttpGateway;
use \Mockery as mockery;

/**
 * Class AscendGreatPlainsHttpGatewayTest
 *
 * Tests for ascend great plains http gateway
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Services
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class AscendGreatPlainsHttpGatewayTest extends TestCase
{
    /**
     * Test http gateway has correct contracts
     */
    public function testHttpGatewayHasCorrectContracts()
    {
        $httpGateway = new AscendGreatPlainsHttpGateway('baseuri', 'key', 'appId');

        $this->assertInstanceOf(ApiClient::class, $httpGateway);
    }

    /**
     * Test http gateway can make patch request
     */
    public function testGatewayCanMakePatchRequest()
    {
        $httpGateway = new AscendGreatPlainsHttpGateway('baseuri', 'key', 'appId');

        $streamInterface = mockery::mock(StreamInterface::class);
        $streamInterface->shouldReceive('getContents')->andReturn(json_encode(['Data' => []]));

        $responseInterface = mockery::mock(ResponseInterface::class);
        $responseInterface->shouldReceive('getBody')->andReturn($streamInterface);

        $guzzle = mockery::mock(Client::class);
        $guzzle->shouldReceive('patch')->andReturn($responseInterface);

        $httpGateway->setClient($guzzle);

        $this->arrayHasKey('Data', $httpGateway->patch('/endpoint/', $data = ['stuff' => 'here']));
    }

    /**
     * Test http gateway can make post request
     */
    public function testGatewayCanMakePostRequest()
    {
        $httpGateway = new AscendGreatPlainsHttpGateway('baseuri', 'key', 'appId');

        $streamInterface = mockery::mock(StreamInterface::class);
        $streamInterface->shouldReceive('getContents')->andReturn(json_encode(['Data' => []]));

        $responseInterface = mockery::mock(ResponseInterface::class);
        $responseInterface->shouldReceive('getBody')->andReturn($streamInterface);

        $guzzle = mockery::mock(Client::class);
        $guzzle->shouldReceive('post')->andReturn($responseInterface);

        $httpGateway->setClient($guzzle);

        $this->arrayHasKey('Data', $httpGateway->post('/endpoint/', $data = ['stuff' => 'here']));
    }

    /**
     * Test http gateway can make get request
     */
    public function testGatewayCanMakeGetRequest()
    {
        $httpGateway = new AscendGreatPlainsHttpGateway('baseuri', 'key', 'appId');

        $streamInterface = mockery::mock(StreamInterface::class);
        $streamInterface->shouldReceive('getContents')->andReturn(json_encode(['Data' => []]));

        $responseInterface = mockery::mock(ResponseInterface::class);
        $responseInterface->shouldReceive('getBody')->andReturn($streamInterface);

        $guzzle = mockery::mock(Client::class);
        $guzzle->shouldReceive('get')->andReturn($responseInterface);

        $httpGateway->setClient($guzzle);

        $this->arrayHasKey('Data', $httpGateway->get('/endpoint/'));
    }
}
