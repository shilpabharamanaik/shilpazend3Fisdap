<?php namespace Fisdap\Members\Queue;

use Illuminate\Queue\Capsule\Manager as Queue;
use Illuminate\Queue\Console\ListenCommand;
use Illuminate\Queue\Console\WorkCommand;
use Illuminate\Queue\Listener;
use Illuminate\Queue\Worker;
use Illuminate\Support\ServiceProvider;
use Zend_Registry;
use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Configures/bootstraps job queue
 *
 * @package Fisdap\Members\Queue
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class QueueServiceProvider extends ServiceProvider
{
    /**
     * Initialize job/message queue service
     *
     * We're using the Illuminate/Queue component from Laravel 5.1, though functionally it works similarly to
     * Laravel 4.2 because we're not using the Bus/Job Dispatcher
     *
     * @see http://laravel.com/docs/4.2/queues
     *
     * @return void
     */
    public function register()
    {
        // Setup Queue Capsule Manager
        $queue = new Queue($this->app);

        $queue->addConnection(
            $this->app['config']->get('queue.connections.beanstalkd')
        );

        // Make this Capsule instance available globally via static methods... (optional)
        $queue->setAsGlobal();

        // register queue manager and connection
        $this->app->instance('queue', $queue->getQueueManager());
        $this->app->singleton('queue.connection', function () {
            return $this->app['queue']->connection();
        });
        $this->app->alias('queue.connection', \Illuminate\Contracts\Queue\Queue::class);

        // register FailedJobProvider
        $this->app->singleton(FailedJobProviderInterface::class, LogFailedJobProvider::class);
        $this->app->alias(FailedJobProviderInterface::class, 'queue.failer');

        // register Listener
        $this->app->singleton('queue.listener', function () {
            return new Listener(APPLICATION_PATH . '/../');
        });

        // register Worker
        $this->app->singleton(Worker::class, function () use ($queue) {
            return new Worker(
                $queue->getQueueManager(),
                $this->app->make(FailedJobProviderInterface::class),
                $this->app->make(Dispatcher::class)
            );
        });

        $this->bindCommands();

        // bind queue instance to container and Zend_Registry
        $this->app->instance(Queue::class, $queue);
        $this->app->instance('Illuminate\Queue\Manager', $queue->getQueueManager());
        $this->app->instance('Illuminate\Contracts\Queue\Factory', $queue->getQueueManager());
        $this->app->instance('Illuminate\Contracts\Queue\Monitor', $queue->getQueueManager());
        Zend_Registry::set('queue', $queue);

        $this->bindAliases();
    }


    protected function bindCommands()
    {
        $this->app->singleton(ListenCommand::class, function () {
            return new ListenCommand($this->app->make('queue.listener'));
        });

        $this->app->singleton(WorkCommand::class, function () {
            return new WorkCommand($this->app->make('Illuminate\Queue\Worker'));
        });
    }


    protected function bindAliases()
    {
        $jobHandlers = require APPLICATION_PATH . '/configs/job_handlers.php';
        foreach ($jobHandlers as $abstract => $alias) {
            $this->app->alias($abstract, $alias);
        }
    }
}
