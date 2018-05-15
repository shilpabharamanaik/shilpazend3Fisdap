<?php

/**
 * Class Fisdap_Controller_Plugin_NewRelicTransaction
 *
 * A controller plugin to set the NewRelic Transaction name
 */
class Fisdap_Controller_Plugin_NewRelicTransaction extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        if (extension_loaded('newrelic')) {
            /** @noinspection PhpUndefinedFunctionInspection */
            newrelic_name_transaction($this->getRequest()->getModuleName() . '/' . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName());
        }
    }
}
