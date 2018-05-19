<?php
namespace Learningcenter\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Learningcenter\Controller\LearningcenterController;

/**
 * This is the factory for LearningcenterController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class LearningcenterControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        // Instantiate the controller and inject dependencies
        return new LearningcenterController($entityManager);
    }
}
