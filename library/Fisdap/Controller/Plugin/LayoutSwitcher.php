<?php
/**
 * Controller plugin to detect the users browser and store that info in the
 * Zend_Registry
 *
 * @todo Look into possibly adding some code to switch out layouts for mobile
 * browsers.
 */

/**
 * @package    Fisdap
 * @subpackage Controllers
 */
class Fisdap_Controller_Plugin_LayoutSwitcher extends Zend_Controller_Plugin_Abstract
{

	public function dispatchLoopStartup(
        Zend_Controller_Request_Abstract $request)
    {
        if ($request->getModuleName() == 'mobile') {
            Zend_Layout::getMvcInstance()->setLayout('mobile');
			//$this->_response->setRedirect('https://www.fisdap.net/mobile/mobile_shift_list.php');
			//return;
        }
    }
}