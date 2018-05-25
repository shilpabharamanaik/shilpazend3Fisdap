<?php


/**
 * Controller plugin to automatically clean up database connections
 */
use Zend\Mvc\Controller\Plugin;

class Fisdap_Controller_Plugin_CloseDbConnections extends AbstractPlugin
{
	public function dispatchLoopShutdown()
	{
		\Fisdap\EntityUtils::getEntityManager()->getConnection()->close();
		\Zend_Registry::get('db')->closeConnection();
        return;
	}
}