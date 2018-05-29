<?php namespace Fisdap\Members\Logging;

use Illuminate\Support\ServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;


/**
 * Class MailLogServiceProvider
 *
 * @package Fisdap\Members\Logging
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class MailLogServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        if (APPLICATION_ENV == 'development') {
            $DEVELOPMENT_LOGGING_LEVEL = constant(getenv('DEVELOPMENT_LOGGING_LEVEL'));
            $loggingLevel = $DEVELOPMENT_LOGGING_LEVEL ?: Logger::DEBUG;
        } else {
            $loggingLevel = Logger::INFO;
        }

        $logger = new Logger(APPLICATION_ENV . '-mail');
        $logger->pushHandler(new StreamHandler(
            IN_HOME_DIR === true ? APPLICATION_PATH . '/../data/mail.log' : '/var/log/fisdap/mail.log',
            $loggingLevel,
            true,
            APPLICATION_ENV == 'development' ? 0666 : null
        ));

        if (APPLICATION_ENV == 'development') {
            $logger->pushProcessor(new MemoryUsageProcessor);
            $logger->pushProcessor(new MemoryPeakUsageProcessor);
        }

        $mailLogger = new MailLogger($logger);

        $this->app->instance(MailLogger::class, $mailLogger);
    }
}