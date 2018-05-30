<?php namespace Fisdap\Api\Cache\Console;

use Illuminate\Cache\TaggableStore;
use Illuminate\Console\Command;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class CacheFlushCommand
 *
 * @package Fisdap\Api\Cache\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class CacheFlushCommand extends Command
{
    protected $name = 'cache:flush';

    protected $description = 'Flush cache tags';

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
        if (empty($this->argument('tags'))) {
            throw new \InvalidArgumentException("At least one cache tag must be specified");
        }

        $this->store->tags($this->argument('tags'))->flush();
        $this->info("Flushed cache tags: " . implode(', ', $this->argument('tags')));
    }


    /**
     * @inheritdoc
     */
    protected function getArguments()
    {
        return [
            ['tags', InputArgument::IS_ARRAY, 'Cache tags']
        ];
    }
}
