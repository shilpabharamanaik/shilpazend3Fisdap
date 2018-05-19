<?php
namespace Skillstracker\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Skillstracker\Controller\SkillstrackerController;

/**
 * This is the factory for SkillstrackerController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class SkillstrackerControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        // Instantiate the controller and inject dependencies
        return new SkillstrackerController($entityManager);
    }
}
