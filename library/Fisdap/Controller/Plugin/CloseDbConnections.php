<?php


/**
 * Controller plugin to automatically clean up database connections
 */
class Fisdap_Controller_Plugin_CloseDbConnections extends Zend_Controller_Plugin_Abstract
{
	public function dispatchLoopShutdown()
	{
		\Fisdap\EntityUtils::getEntityManager()->getConnection()->close();
		\Zend_Registry::get('db')->closeConnection();
        return;
	}
}