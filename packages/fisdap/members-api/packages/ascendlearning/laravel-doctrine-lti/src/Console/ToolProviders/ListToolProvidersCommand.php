<?php namespace AscendLearning\Lti\Console\ToolProviders;

use AscendLearning\Lti\Entities\Consumer;
use AscendLearning\Lti\Entities\ToolProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Data\Repository\DoctrineRepository;
use Fisdap\Queries\Specifications\Partial;
use Fisdap\Queries\Specifications\PartialOverrides;
use Happyr\DoctrineSpecification\Spec;
use Illuminate\Console\Command;

/**
 * Class ListToolProvidersCommand
 *
 * @package AscendLearning\Lti\Console\Consumers
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ListToolProvidersCommand extends Command
{
    protected $signature = "lti:tool-providers:list";

    protected $description = "List LTI Tool Providers";

    /**
     * @var EntityManagerInterface|EntityManager
     */
    private $entityManager;


    /**
     * ListToolProvidersCommand constructor.
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
        $headers = ['ID', 'Launch URL', 'Logout URL', 'Logo URL', 'OAuth Consumer Key', 'Secret', 'Resource Link Title',
                    'Resource Link Description', 'Context ID', 'Context Title', 'Custom Parameters'];


        /** @var DoctrineRepository $toolProviderRepository */
        $toolProviderRepository = $this->entityManager->getRepository(ToolProvider::class);

        $toolProviders = $toolProviderRepository->match(Spec::andX(), Spec::asArray());

        $toolProvidersWithCustomParams = array_map(function ($toolProvider) {

            $customParameters = array_map(function ($customParameter) {
                return key($customParameter) . '=' . current($customParameter);
            }, $toolProvider['customParameters']);

            $toolProvider['customParameters'] = implode(PHP_EOL, $customParameters);

            return $toolProvider;

        }, $toolProviders);

        $this->table($headers, $toolProvidersWithCustomParams);
    }
}
