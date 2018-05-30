<?php

use Fisdap\Entity\User;

/**
 * Class ExceptionLogger
 *
 * Logs exceptions to the filesystem, Bugsnag, and New Relic
 *
 * @author Ben Getsug (bgetsug@fisdap.net)
 */
class ExceptionLogger
{

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $logger;


    public function __construct()
    {
        $this->logger = Zend_Registry::isRegistered('logger') ? Zend_Registry::get('logger') : null;
    }


    /**
     * @param Exception $exception
     * @param string    $errorId
     */
    public function log(Exception $exception, $errorId = null)
    {
        $errorId = $errorId ?: uniqid();

        $errorCode = $exception->getCode();

        // honor PHP error levels, or log exceptions using 'critical' severity
        switch ($errorCode) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
                $severity = 'critical';
                $bugsnagSeverity = 'error';
                break;
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                $severity = 'error';
                $bugsnagSeverity = $severity;
                break;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $severity = 'warning';
                $bugsnagSeverity = 'warning';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $severity = 'notice';
                $bugsnagSeverity = 'info';
                break;
            default:
                $severity = 'critical';
                $bugsnagSeverity = 'error';
                break;
        }

        // debugging
//        $this->logger->debug('Exception message: ' . $exception->getMessage(), $context);
//        $this->logger->debug('Exception code: ' . $exception->getCode(), $context);
//        $this->logger->debug('Exception file: ' . $exception->getFile(), $context);
//        $this->logger->debug('Exception line: ' . $exception->getLine(), $context);
//        $this->logger->debug('Exception severity: ' . $severity, $context);

        // log the exception
        $this->sendToLogger($exception, $severity, $errorId);

        // only send errors of specific severities to Bugsnag
        if (in_array($bugsnagSeverity, array('warning', 'error'))) {
            $this->sendToBugsnag($exception, $errorId, $bugsnagSeverity);
        }

        $this->sendToNewRelic($exception);
    }


    /**
     * @param Exception $exception
     * @param $severity
     * @param $errorId
     */
    protected function sendToLogger(Exception $exception, $severity, $errorId)
    {
        if (! $this->logger instanceof Psr\Log\LoggerInterface) {
            return;
        }

        // gather contextual data
        $context = array(
            //'Server/Request Data'   => $_SERVER,
            //'GET Data'              => $_GET,
            //'POST Data'             => $_POST,
            //'Files'                 => $_FILES,
            //'Cookies'               => $_COOKIE,
            //'Session'               => isset($_SESSION) ? $_SESSION:  array(),
            //'Environment Variables' => $_ENV,
            //'phpErrorLevelOrExceptionCode' => $errorCode,
        );

        if (PHP_SAPI != 'cli') {
            $context['url'] = $_SERVER['REQUEST_URI'];

            $user = User::getLoggedInUser();

            if ($user) {
                $context['username'] = $user->username;
            }
        }


        $context['errorId'] = $errorId;


        // log error, if severity level is allowed
        $EXCEPTION_LOGGER_SILENCE_SEVERITIES = getenv('EXCEPTION_LOGGER_SILENCE_SEVERITIES');

        $silencedSeverites = isset($EXCEPTION_LOGGER_SILENCE_SEVERITIES) ? explode(
            ',',
            $EXCEPTION_LOGGER_SILENCE_SEVERITIES
        ) : array();

        if (!in_array($severity, $silencedSeverites)) {
            $this->logger->$severity($this->makeDetailedMessage($exception), $context);

            /*
             * log each line of the trace on a separate log line with DEBUG severity, like the php.log is normally formatted...
             * ...makes the log easier to read by humans
             */
            $trace = explode('#', $exception->getTraceAsString());
            unset($trace[0]);

            foreach ($trace as $traceLine) {
                $this->logger->debug('#' . trim($traceLine), array('errorId' => $errorId));
            }
        }
    }


    /**
     * @param Exception $exception
     * @param $errorId
     * @param string $bugsnagSeverity
     */
    protected function sendToBugsnag(Exception $exception, $errorId, $bugsnagSeverity)
    {
        $bugsnag = Zend_Registry::isRegistered('bugsnag') ? Zend_Registry::get('bugsnag') : null;

        if ($bugsnag instanceof Bugsnag_Client) {
            $user = User::getLoggedInUser();

            // *** using user.email field as workaround to make errorId searchable ***
            if ($user) {
                $bugsnag->setUser(
                    array(
                        'name' => $user->username,
                        'email' => $errorId,
                        'id' => $user->id
                    )
                );
            } else {
                $bugsnag->setUser(
                    array(
                        'name' => 'UNKNOWN',
                        'email' => $errorId
                    )
                );
            }

            if ($exception instanceof ErrorException) {
                $bugsnag->notifyError($exception->getMessage(), $this->makeDetailedMessage($exception), null, $bugsnagSeverity);
            } else {
                $bugsnag->notifyException($exception, null, $bugsnagSeverity);
            }
        }
    }


    /**
     * @param Exception $exception
     */
    protected function sendToNewRelic(Exception $exception)
    {
        if (extension_loaded('newrelic')) {
            /** @noinspection PhpUndefinedFunctionInspection */
            newrelic_notice_error($this->makeDetailedMessage($exception), $exception);
        }
    }


    /**
     * @param Exception $exception
     *
     * @return string
     */
    protected function makeDetailedMessage(Exception $exception)
    {
        return $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine();
    }
}
