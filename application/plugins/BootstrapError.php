<?php

class Application_Plugin_BootstrapError extends Zend_Controller_Plugin_Abstract
{
	protected $_exception;

	public function __construct(Exception $exception)
	{
		$this->_exception = $exception;
	}

	public function routeStartup($request)
	{
		throw $this->_exception;
	}
}