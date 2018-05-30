<?php

use Fisdap\BuildMetadata\BuildMetadata;

/**
 * Class Appmon_BuildController
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Appmon_BuildController extends Zend_Controller_Action
{
    /**
     * /appmon/build route
     */
    public function indexAction()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->view->headTitle('Monitor :: Build');

        /** @noinspection PhpUndefinedMethodInspection */
        $this->_helper->layout()->disableLayout();

        // prevent results from being cached
        $this->_response
            ->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->setHeader('Cache-Control', 'post-check=0, pre-check=0', false)
            ->setHeader('Pragma', 'no-cache');

        $build = new BuildMetadata();
        $build->load();

        $this->view->assign((array) $build);

        if ($this->_request->getParam('format') == 'json' or $this->_request->getHeader('Accept') == 'application/json') {
            $this->_helper->json((array) $build, true);
        } else {
            $this->view->assign((array) $build);
        }
    }
}
