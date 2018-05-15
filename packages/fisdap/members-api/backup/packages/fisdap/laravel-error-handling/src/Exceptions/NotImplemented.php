<?php namespace Fisdap\ErrorHandling\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class NotImplemented
 *
 * @package Fisdap\ErrorHandling\Exceptions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class NotImplemented extends HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param int        $code     The internal exception code
     */
    public function __construct($message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct(501, $message, $previous, array(), $code);
    }
}
