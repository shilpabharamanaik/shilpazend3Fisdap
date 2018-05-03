<?php namespace AscendLearning\Lti\Console\Consumers;

use AscendLearning\Lti\Entities\Consumer;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Data\Repository\DoctrineRepository;
use Fisdap\Queries\Specifications\Partial;
use Fisdap\Queries\Specifications\PartialOverrides;
use Happyr\DoctrineSpecification\Spec;
use Illuminate\Console\Command;

/**
 * Class ListConsumersCommand
 *
 * @package AscendLearning\Lti\Console\Consumers
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ListConsumersCommand extends Command
{
    protected $signature = 'lti:consumers:list';

    protected $description = 'List LTI Consumers';

    /**
     * @var EntityManagerInterface|EntityManager
     */
    private $entityManager;


    /**
     * ListConsumersCommand constructor.
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
        $headers = ['Key', 'Name', 'Secret', 'LTI Version', 'Consumer Name', 'Consumer Version', 'Consumer GUID',
                    'Protected', 'Enabled'];

        $spec = Spec::andX(
            new PartialOverrides(
                [
                    new Partial(
                        ['consumer_key', 'name', 'secret', 'lti_version', 'consumer_name', 'consumer_version',
                            'consumer_guid', 'protected', 'enabled']
                    )
                ]
            )
        );

        /** @var DoctrineRepository $consumerRepository */
        $consumerRepository = $this->entityManager->getRepository(Consumer::class);

        $consumers = $consumerRepository->match($spec, Spec::asArray());

        $this->table($headers, $consumers);
    }
}
