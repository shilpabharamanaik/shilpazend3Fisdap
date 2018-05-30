<?php


/**
 * Class Appmon_IndexController
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Appmon_IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->redirect('/appmon/status');
    }
}
