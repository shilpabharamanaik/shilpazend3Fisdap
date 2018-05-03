<?php namespace AscendLearning\Lti\Console\ToolProviders;

use AscendLearning\Lti\Entities\ToolProvider;
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
class DeleteToolProvidersCommand extends Command
{
    protected $signature = "lti:tool-providers:delete {id*}";

    protected $description = "Delete one or more LTI Tool Providers";

    /**
     * @var EntityManagerInterface|EntityManager
     */
    private $entityManager;


    /**
     * CreateToolProviderCommand constructor.
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
        $consumerRepository = $this->entityManager->getRepository(ToolProvider::class);

        $consumerRepository->destroyCollection($this->argument('id'));

        $this->info("Deleted consumers with id(s): " . implode(', ', $this->argument('id')));
    }
}
