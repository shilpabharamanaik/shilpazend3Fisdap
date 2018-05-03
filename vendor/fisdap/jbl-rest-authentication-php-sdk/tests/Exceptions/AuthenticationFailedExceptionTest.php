<?php namespace Fisdap\JBL\Authentication\Phpunit\Exceptions;

use Fisdap\JBL\Authentication\Exceptions\AuthenticationFailedException;
use Fisdap\JBL\Authentication\Phpunit\TestCase;
use Fisdap\JBL\Authentication\Exceptions\RequestException;
use \Mockery as mockery;

/**
 * Class AuthenticationFailedExceptionTest
 *
 * Authentication failed exception tests
 *
 * @package Fisdap\JBL\Authentication\Phpunit\Exceptions
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class AuthenticationFailedExceptionTest extends TestCase
{
    /**
     * Test class extends request exception
     */
    public function testClassExtendsRequestException()
    {
        $exception = new AuthenticationFailedException('403 error message');

        $this->assertInstanceOf(RequestException::class, $exception);
    }

    /**
     * Test authentication http status code is a 403
     */
    public function testAuthenticationHttpStatusCodeIs403()
    {
        $exception = new AuthenticationFailedException('403 error message');

        $this->assertEquals(403, $exception->getHttpStatus());
        $this->assertEquals(403, AuthenticationFailedException::HTTP_STATUS_CODE);
    }
}
