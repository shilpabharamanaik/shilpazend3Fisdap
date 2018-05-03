<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Exceptions;

use Fisdap\Ascend\Greatplains\Exceptions\InvalidArgumentException;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class InvalidArgumentExceptionTest
 *
 * Test for invalid argument exception
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Exceptions
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class InvalidArgumentExceptionTest extends TestCase
{
    /**
     * Test class has correct contracts
     */
    public function testClassHasCorrectContracts()
    {
        $exception = new InvalidArgumentException('invalid argument');

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
