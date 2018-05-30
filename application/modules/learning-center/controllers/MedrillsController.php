<?php

class LearningCenter_MedrillsController extends Fisdap_Controller_Private
{
    public function init()
    {
        parent::init();
        $this->view->vimeoHelper = new \Fisdap_View_Helper_VimeoVideo;
    }

    /**
     * Main index page for a given Medrills product
     *
     * @throws Exception
     */
    public function indexAction()
    {
        $product_id = $this->_getParam('product');

        if (!$this->checkPermissions($this->userContext, $product_id)) {
            // if this is a non-staff user without this product, they can't be here
            $this->displayError("You don't have permission to view this page.");
            return;
        }

        $product = \Fisdap\EntityUtils::getEntity('Product', $product_id);
        $this->view->pageTitle = $product->name;

        $this->view->videos = \Fisdap\EntityUtils::getRepository('MedrillVideo')->getVideosByProduct($product_id);
        $this->view->view_link_base = '/learning-center/medrills/view/product/'.$product_id;
    }

    /**
     * View videos page for a given Medrills product and category
     *
     * @throws Exception
     */
    public function viewAction()
    {
        $product_id = $this->_getParam('product');

        if (!$this->checkPermissions($this->userContext, $product_id)) {
            // if this is a non-staff user without this product, they can't be here
            $this->displayError("You don't have permission to view this page.");
            return;
        }

        $category_id = $this->_getParam('category');
        $product_index_url = "/learning-center/medrills/index/product/".$product_id;

        // if a category hasn't been specified, reroute back to the product index page
        if (!$category_id) {
            $this->redirect($product_index_url);
        }

        // get the videos for this product and category
        $videos = \Fisdap\EntityUtils::getRepository('MedrillVideo')->getVideosByProductAndCategory($product_id, $category_id);

        // if there are no videos for this category, reroute back to the product index page
        if (count($videos) < 1) {
            $this->redirect($product_index_url);
        }

        $product = \Fisdap\EntityUtils::getEntity('Product', $product_id);
        $this->view->pageTitle = $product->name;
        $this->view->pageTitleLinkURL = $product_index_url;
        $this->view->pageTitleLinkText = "<< Back to all videos";

        $category = \Fisdap\EntityUtils::getEntity('MedrillCategory', $category_id);
        $this->view->category = $category->name;
        $this->view->videos = \Fisdap\EntityUtils::getRepository('MedrillVideo')->getVideosByProductAndCategory($product_id, $category_id);

        // figure out which video should be visible on page load
        $start_video = $this->_getParam('video');
        if (!$start_video || $start_video > count($this->view->videos)) {
            $this->view->startVideo = 1;
        } else {
            $this->view->startVideo = $start_video;
        }
    }

    public function checkPermissions($userContext, $product_id = null)
    {
        // if a product hasn't been specified, reroute back to the learning center landing page
        if (!$product_id) {
            $this->redirect('/learning-center');
        }

        return ($userContext->getUser()->isStaff() || $userContext->getPrimarySerialNumber()->hasProductAccess($product_id));
    }
}
