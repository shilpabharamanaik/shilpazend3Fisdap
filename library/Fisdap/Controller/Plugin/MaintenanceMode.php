<?php namespace Fisdap\Controller\Plugin;

use Zend_Controller_Plugin_Abstract;
use Zend_Controller_Request_Abstract;

/**
 * Disables processing of requests when in Maintenance Mode
 *
 * @package Fisdap\Controller\Plugin
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class MaintenanceMode extends Zend_Controller_Plugin_Abstract
{
    /**
     * @inheritdoc
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        if (file_exists(storage_path() . '/down')) {
            http_response_code(503);
            die('Down for maintenance');
        }
    }
}
