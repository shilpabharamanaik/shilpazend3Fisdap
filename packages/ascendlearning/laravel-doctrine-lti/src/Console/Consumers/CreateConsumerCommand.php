<?php namespace AscendLearning\Lti\Console\Consumers;

use AscendLearning\Lti\Entities\Consumer;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Console\Command;

/**
 * Class CreateConsumerCommand
 *
 * @package AscendLearning\Lti\Console\Consumers
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class CreateConsumerCommand extends Command
{
    protected $signature = 'lti:consumers:create {key} {name} {secret} {--protected=0} {--enabled=1}';

    protected $description = 'Create an LTI Consumer';

    /**
     * @var EntityManagerInterface|EntityManager
     */
    private $entityManager;


    /**
     * CreateConsumerCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }


    public function handle()
    {
        $consumer = new Consumer(
            $this->argument('key'),
            $this->argument('name'),
            $this->argument('secret'),
            (bool) $this->option('protected'),
            (bool) $this->option('enabled')
        );

        $this->entityManager->persist($consumer);
        $this->entityManager->flush();

        $this->info("Created consumer named '{$this->argument('name')}'");
    }
}
