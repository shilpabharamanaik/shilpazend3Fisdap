<?php
namespace Account\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Account\Controller\AccountController;

/**
 * This is the factory for AccountController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class AccountControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        // Instantiate the controller and inject dependencies
        return new AccountController($entityManager);
    }
}
