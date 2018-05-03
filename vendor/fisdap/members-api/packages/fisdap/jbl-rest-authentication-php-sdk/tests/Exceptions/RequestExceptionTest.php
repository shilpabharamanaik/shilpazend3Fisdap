<?php namespace Fisdap\JBL\Authentication\Phpunit\Exceptions;

use Fisdap\JBL\Authentication\Exceptions\RequestException;
use \Mockery as mockery;
use Fisdap\JBL\Authentication\Phpunit\TestCase;
use Exception;

/**
 * Class RequestExceptionTest
 *
 * @package Fisdap\JBL\Authentication\Phpunit\Exceptions
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class RequestExceptionTest extends TestCase
{
    /**
     * Test request exception extends exception
     */
    public function testRequestExceptionExtendsException()
    {
        $this->assertInstanceOf(Exception::class, new RequestException('error happened'));
    }
}
