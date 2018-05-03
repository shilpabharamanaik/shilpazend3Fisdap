<?php namespace Fisdap\JBL\Authentication\Phpunit\Exceptions;

use Fisdap\JBL\Authentication\Exceptions\ServerException;
use Fisdap\JBL\Authentication\Phpunit\TestCase;
use Fisdap\JBL\Authentication\Exceptions\RequestException;
use \Mockery as mockery;

/**
 * Class ServerExceptionTest
 *
 * @package Fisdap\JBL\Authentication\Phpunit\Exceptions
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class ServerExceptionTest extends TestCase
{
    /**
     * Test class extends request exception
     */
    public function testClassExtendsRequestException()
    {
        $exception = new ServerException('500 error message');

        $this->assertInstanceOf(RequestException::class, $exception);
    }

    /**
     * Test authentication http status code is a 500
     */
    public function testAuthenticationHttpStatusCodeIs403()
    {
        $exception = new ServerException('500 error message');

        $this->assertEquals(500, $exception->getHttpStatus());
        $this->assertEquals(500, ServerException::HTTP_STATUS_CODE);
    }
}
