<?php namespace AscendLearning\Lti\Console\Consumers;

use AscendLearning\Lti\Entities\Consumer;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Data\Repository\DoctrineRepository;
use Illuminate\Console\Command;

/**
 * Class DeleteConsumersCommand
 *
 * @package AscendLearning\Lti\Console\Consumers
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class DeleteConsumersCommand extends Command
{
    protected $signature = 'lti:consumers:delete {key*}';

    protected $description = 'Delete one or more LTI Consumers';

    /**
     * @var EntityManagerInterface|EntityManager
     */
    private $entityManager;


    /**
     * DeleteConsumersCommand constructor.
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
        /** @var DoctrineRepository $consumerRepository */
        $consumerRepository = $this->entityManager->getRepository(Consumer::class);

        $consumerRepository->destroyCollection($this->argument('key'));

        $this->info("Deleted consumers with key(s): " . implode(', ', $this->argument('key')));
    }
}
