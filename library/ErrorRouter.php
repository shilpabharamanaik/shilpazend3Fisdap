<?php

class ErrorRouter extends Zend_Controller_Router_Rewrite
{
    public function route(Zend_Controller_Request_Abstract $request)
    {
        // If the ErrorHandler plugin has stashed the error in a request param, then
        // it will have already dealt with routing (see Bootstrap::onApplicationShutdown())
        // (Note that this param cannot be spoofed, since any user-supplied params
        // will be strings, not objects)
        if (is_object($request->getParam('error_handler'))) {
            return $request;
        } else {
            return parent::route($request);
        }
    }
}