<?php

class Styleguide_IndexController extends Fisdap_Controller_Staff
{
    public function init()
    {
        parent::init();
        $this->view->styleguide = new \Scan\Kss\Parser('../public/css/styleguide');
        $this->view->headLink()->appendStylesheet("/css/styleguide/styleguide.css");
    }

    public function indexAction()
    {
        $this->view->pageTitle = "Styleguide";
    }

    public function headersAction()
    {
        $this->view->pageTitle = "Styleguide: Headers";
        $this->view->section = 1;
    }
}
