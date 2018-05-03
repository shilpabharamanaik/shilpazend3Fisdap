<?php namespace Fisdap\JBL\Authentication\Phpunit;

use Fisdap\JBL\Authentication\Contracts\EmailPasswordAuthenticator;
use Fisdap\JBL\Authentication\Contracts\UserByIdAuthenticator;
use Fisdap\JBL\Authentication\Contracts\UserByProductIdAuthenticator;
use Fisdap\JBL\Authentication\JblRestApiUserAuthentication;
use Fisdap\JBL\Authentication\Contracts\HttpClient;
use \Mockery as mockery;
use stdClass;

/**
 * Class JblRestApiUserAuthenticationTest
 *
 * Tests for JBL Rest api user authentication test
 *
 * @package Fisdap\JBL\Authentication\Phpunit
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class JblRestApiUserAuthenticationTest extends TestCase
{
    /**
     * Test class has correct contracts
     */
    public function testClassHasCorrectContracts()
    {
        $httpClient = mockery::mock(HttpClient::class);
        $authenticator = new JblRestApiUserAuthentication('http://url/', $httpClient);

        $this->assertInstanceOf(UserByProductIdAuthenticator::class, $authenticator);
        $this->assertInstanceOf(UserByIdAuthenticator::class, $authenticator);
        $this->assertInstanceOf(EmailPasswordAuthenticator::class, $authenticator);
    }

    /**
     * Test can authenticate with email and password
     */
    public function testCanAuthenticateWithEmailPassword()
    {
        // Check documentation for full list of variables
        $data = new stdClass();
        $data->Error = false;
        $data->Ok = true;
        $data->Errors = null;
        $data->Data = new \stdClass();
        $data->Data->Id = 0;
        $data->Data->FirstName = "TestTwo";
        $data->Data->LastName = "Student2";
        $data->Data->EmailAddress = "testStudent2@jbl.com";

        $httpClient = mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('setHeaders')->andReturnSelf();
        $httpClient->shouldReceive('post')->andReturn($data);

        $authenticator = new JblRestApiUserAuthentication('http://url/', $httpClient);

        $this->assertInstanceOf(stdClass::class, $authenticator->authenticateWithEmailPassword('email', 'password'));
    }

    /**
     * Test can authenticate user by id
     */
    public function testCanAuthenticateUserById()
    {
        // Check documentation for full list of variables
        $data = new stdClass();
        $data->Error = false;
        $data->Ok = true;
        $data->Errors = null;
        $data->Data = new \stdClass();
        $data->Data->UserId = "7c06bc99-8b24-4514-905b-b2cdb0ddc408";
        $data->Data->Email = "testStudent2@jbl.com";

        $httpClient = mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('setHeaders')->andReturnSelf();
        $httpClient->shouldReceive('get')->andReturn($data);

        $authenticator = new JblRestApiUserAuthentication('http://url/', $httpClient);

        $this->assertInstanceOf(stdClass::class, $authenticator->authenticateUserById('1223abcd'));
    }

    /**
     * Test can authenticate user by product id
     */
    public function testCanAuthenticateUserByProductId()
    {
        // Check documentation for full list of variables
        $data = new stdClass();
        $data->Error = false;
        $data->Ok = true;
        $data->Errors = null;
        $data->Data = new \stdClass();
        $data->Data->UserId = "7c06bc99-8b24-4514-905b-b2cdb0ddc408";
        $data->Data->Email = "testStudent2@jbl.com";

        $httpClient = mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('setHeaders')->andReturnSelf();
        $httpClient->shouldReceive('get')->andReturn($data);

        $authenticator = new JblRestApiUserAuthentication('http://url/', $httpClient);

        $this->assertInstanceOf(stdClass::class, $authenticator->authenticateUserByProductId('E69AFA4C-3894-4A1D-9810-A490CA7FC53E'));
    }
}
