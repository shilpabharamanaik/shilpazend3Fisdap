<?php namespace Fisdap\Api\Cache\Console;

use Illuminate\Cache\TaggableStore;
use Illuminate\Console\Command;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
use Symfony\Component\Console\Input\InputArgument;


/**
 * Class CacheForgetCommand
 *
 * @package Fisdap\Api\Cache\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class CacheForgetCommand extends Command
{
    protected $name = 'cache:forget';

    protected $description = 'Delete a cache key';

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Store|TaggableStore
     */
    private $store;


    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        parent::__construct();

        $this->repository = $repository;

        $this->store = $repository->getStore();
    }


    public function fire()
    {
        if ( ! empty($this->argument('tags'))) {

            if ($this->store->tags($this->argument('tags'))->has($this->argument('key'))) {
                $this->store->tags($this->argument('tags'))->forget($this->argument('key'));
                $this->info("Deleted {$this->argument('key')} from tags: " . implode(', ', $this->argument('tags')));
            } else {
                $this->error("No key named '{$this->argument('key')}' found in tags: " . implode(', ', $this->argument('tags')));
            }

        } else {

            if ($this->repository->has($this->argument('key'))) {
                $this->store->forget($this->argument('key'));
                $this->info("Deleted {$this->argument('key')} from cache");
            } else {
                $this->error("No key named '{$this->argument('key')}' found in cache");
            }
        }
    }


    /**
     * @inheritdoc
     */
    protected function getArguments()
    {
        return [
            ['key', InputArgument::REQUIRED, 'The cache key'],
            ['tags', InputArgument::IS_ARRAY, 'Cache tags']
        ];
    }
}
