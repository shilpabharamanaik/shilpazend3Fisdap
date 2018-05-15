<?php 

class Zend_View_Helper_JavascriptHelper extends Zend_View_Helper_Abstract
{
    public function javascriptHelper($path=null)
    {
        if ($path == null) {
            $request = Zend_Controller_Front::getInstance()->getRequest();

            if ($request->getModuleName() == 'default') {
                $file_uri = 'js/' . $request->getControllerName() . '/' . $request->getActionName() . '.js';
            } else {
                $file_uri = 'js/' . $request->getModuleName() . '/' . $request->getControllerName() . '/' . $request->getActionName() . '.js';
            }
        } else {
            $file_uri = 'js/' . $path;
        }
        
        if (file_exists($file_uri)) {
            $this->view->headScript()->appendFile('/' . $file_uri);
        }
    }
}
