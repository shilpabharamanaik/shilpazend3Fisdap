<?php namespace Fisdap\Api\Bootstrap;

use Illuminate\Foundation\Bootstrap\ConfigureLogging as FoundationConfigureLogging;
use Illuminate\Log\Writer;
use Illuminate\Contracts\Foundation\Application;


/**
 * Class ConfigureLogging
 *
 * @package Fisdap\Api\Bootstrap
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
class ConfigureLogging extends FoundationConfigureLogging
{
    /**
     * @inheritdoc
     */
    protected function configureSingleHandler(Application $app, Writer $log)
    {
        $log->useFiles(
            $this->getLogPath($app),
            $app->make('config')->get('app.debug') == true ? 'debug' : 'info'
        );
    }


    /**
     * @inheritdoc
     */
    protected function configureDailyHandler(Application $app, Writer $log)
    {
        $log->useDailyFiles(
            $this->getLogPath($app),
            $app->make('config')->get('app.log_max_files', 5),
            $app->make('config')->get('app.debug') == true ? 'debug' : 'info'
        );
    }


    /**
     * @inheritdoc
     */
    protected function configureSyslogHandler(Application $app, Writer $log)
    {
        $log->useSyslog('memapi');
    }


    /**
     * @param Application $app
     *
     * @return string
     */
    private function getLogPath(Application $app)
    {
        return env('MRAPI_LOG_FILE', $app->storagePath().'/logs/mrapi.log');
    }
}