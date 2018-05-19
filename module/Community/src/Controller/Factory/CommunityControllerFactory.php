<?php
namespace Community\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Community\Controller\CommunityController;

/**
 * This is the factory for CommunityController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class CommunityControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

      // Instantiate the controller and inject dependencies
        return new CommunityController($entityManager);
    }
}
