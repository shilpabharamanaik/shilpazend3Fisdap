<?php namespace AscendLearning\Lti\Console\ToolProviders;

use AscendLearning\Lti\Entities\ToolProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Console\Command;

/**
 * Class CreateToolProviderCommand
 *
 * @package AscendLearning\Lti\Console\Consumers
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class CreateToolProviderCommand extends Command
{
    protected $signature = 'lti:tool-providers:create
                            {launchUrl} {secret} {oauthConsumerKey?}
                            {--logoutUrl=} {--logoUrl=} {--resourceLinkTitle=} {--resourceLinkDescription=}
                            {--contextId=} {--contextTitle=} 
                            {--customParameter=* : A comma-separated key-value pair}';

    protected $description = 'Create an LTI Tool Provider';

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
        $toolProvider = new ToolProvider($this->argument('launchUrl'), $this->argument('secret'));
        $toolProvider->setOauthConsumerKey($this->argument('oauthConsumerKey'));
        $toolProvider->setLogoutUrl($this->option('logoutUrl'));
        $toolProvider->setLogoUrl($this->option('logoUrl'));
        $toolProvider->setResourceLinkTitle($this->option('resourceLinkTitle'));
        $toolProvider->setResourceLinkDescription($this->option('resourceLinkDescription'));
        $toolProvider->setContextId($this->option('contextId'));
        $toolProvider->setContextTitle($this->option('contextTitle'));

        if (is_array($this->option('customParameter'))) {
            $toolProvider->setCustomParameters(array_map(function ($customParameter) {
                list($key, $value) = explode(',', $customParameter);
                return [$key => $value];
            }, $this->option('customParameter')));
        }

        $this->entityManager->persist($toolProvider);
        $this->entityManager->flush();

        $this->info("Created tool provider with launch URL: '{$this->argument('launchUrl')}'");
    }
}
