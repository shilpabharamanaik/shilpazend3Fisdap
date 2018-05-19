<?php
namespace Reports\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Reports\Controller\ReportsController;

/**
 * This is the factory for ReportsController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class ReportsControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        // Instantiate the controller and inject dependencies
        return new ReportsController($entityManager);
    }
}
