<?php

class Help_IndexController extends Fisdap_Controller_Base
{

    public function indexAction()
    {
		if ($this->user) {
			if ($this->user->isInstructor()) {
				$this->redirect(Util_HandyServerUtils::get_fisdap_content_url_root() . "/support?r=instructor");
			} else {
				$this->redirect(Util_HandyServerUtils::get_fisdap_content_url_root() . "/support?r=student");
			}
		}
		
		$this->redirect(Util_HandyServerUtils::get_fisdap_content_url_root() . "/support");
    }
}

