<?php

namespace Fisdap;

use Whoops\Run;
use Whoops\Exception\ErrorException;
use Exception;

class WhoopsRun extends Run
{

    /**
     * @var \ExceptionLogger
     */
    protected $exceptionLogger;


    public function __construct()
    {
        $this->exceptionLogger = \Zend_Registry::isRegistered('exceptionLogger') ? \Zend_Registry::get('exceptionLogger') : null;
    }


    /**
     * Converts generic PHP errors to \ErrorException
     * instances, before passing them off to be handled.
     *
     * This method MUST be compatible with set_error_handler.
     *
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     *
     * @throws \Whoops\Exception\ErrorException
     * @return bool
     */
    public function handleError($level, $message, $file = null, $line = null)
    {
        if ($level & error_reporting()) {
            $exception = new ErrorException($message, $level, 0, $file, $line);

            foreach ($this->silencedPatterns as $entry) {
                $pathMatches = (bool) preg_match($entry["pattern"], $file);
                $levelMatches = $level & $entry["levels"];
                if ($pathMatches && $levelMatches) {
                    // Ignore the error, abort handling
                    $this->exceptionLogger->log($exception); // Fisdap addition to make sure we always log the error
                    return true;
                }
            }

            if ($this->canThrowExceptions) {
                throw $exception;
            } else {
                $this->handleException($exception);
            }
        }
    }


    public function handleException(Exception $exception)
    {
        // Fisdap addition to make sure we always log the exception
        $this->exceptionLogger->log($exception);

        parent::handleException($exception);
    }


    /**
     * Special case to deal with Fatal errors and the like.
     *
     * Even though this is the same code as in the parent Whoops\Run class, this must exist in order to facilitate
     * fatal error handling.  I believe this is due to the fact that isLevelFatal() and $canThrowExceptions are private,
     * and this subclass does not have access to either of those.  Hence their duplication below as well. ~bgetsug
     */
    public function handleShutdown()
    {
        // If we reached this step, we are in shutdown handler.
        // An exception thrown in a shutdown handler will not be propagated
        // to the exception handler. Pass that information along.
        $this->canThrowExceptions = false;

        $error = error_get_last();
        if ($error && $this->isLevelFatal($error['type'])) {
            // If there was a fatal error,
            // it was not handled in handleError yet.
            $this->handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }


    /**
     * In certain scenarios, like in shutdown handler, we can not throw exceptions
     * @var boolean
     */
    private $canThrowExceptions = true;

    private static function isLevelFatal($level)
    {
        return in_array(
            $level,
            array(
                E_ERROR,
                E_PARSE,
                E_CORE_ERROR,
                E_CORE_WARNING,
                E_COMPILE_ERROR,
                E_COMPILE_WARNING
            )
        );
    }
}
