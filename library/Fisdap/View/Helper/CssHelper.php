<?php

class Zend_View_Helper_CssHelper extends Zend_View_Helper_Abstract 
{ 
    function cssHelper() { 
        $request = Zend_Controller_Front::getInstance()->getRequest();
        
        if ($request->getModuleName() == 'default') {
            $file_uri = 'css/' . $request->getControllerName() . '/' . $request->getActionName() . '.css';             
        } else {
            $file_uri = 'css/' . $request->getModuleName() . '/' . $request->getControllerName() . '/' . $request->getActionName() . '.css';
        }
        if (file_exists($file_uri)) { 
            $this->view->headLink()->appendStylesheet('/' . $file_uri); 
        }
    } 
}