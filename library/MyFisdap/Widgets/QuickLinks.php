<?php

class MyFisdap_Widgets_QuickLinks extends MyFisdap_Widgets_Base implements MyFisdap_Widgets_iConfigurable
{
    public function render()
    {
        $links = $this->getIncludedLinks();

        $html = "";

        if (count($links) == 0) {
            $html .= "No links added.  Click the settings icon to add some!";
        } else {
            $html = "<div class='quick-links-widget-background'>";

            foreach ($links as $sectionName => $sectionLinks) {
                $html .= "<h2 class='quick-link-section-header'>" . $sectionName . "</h2>";

                foreach ($sectionLinks as $linkTitle => $linkData) {
                    $html .= "<div class='quick-link-a'><a href='{$linkData['url']}'>{$linkTitle}</a></div>";
                }
            }

            $html .= "</div>";

            // Useful for debugging...
            //$html .= "<pre>" . print_r(array_keys($this->data), true) . "</pre>";
            //$html .= "<pre>" . print_r($this->data, true) . "</pre>";
        }

        return $html;
    }

    private function getLinks()
    {
        $links = array();

        $user = $this->getWidgetUser();
        $currentContext = $user->getCurrentUserContext();

        $sn = $currentContext->getPrimarySerialNumber();

        $isInstructor = $currentContext->isInstructor();
 
        //$links['Fisdap']['Learning Center'] = array('key' => 'fisdap_learning_center', 'url' => "https://members.fisdap.net/learning-center");
        $links['Fisdap']['Navigate 2'] = array('key' => 'Navigate_2', 'url' => "http://www2.jblearning.com/my-account");

        if (!$isInstructor && ($sn->hasStudyTools())) {
            $links['Fisdap']['Study Tools'] = array('key' => 'Fisdap_Study_Tools', 'url' => "https://members.fisdap.net/learning-center");
        }

        if (!$isInstructor && ($sn->hasProductAccess("secure_testing"))) {
            //$links['Fisdap']['Take a Test'] = array('key' => 'Fisdap_Take_a_test', 'url' => \Fisdap\MoodleUtils::getUrl("secure_testing"));
            $links['Fisdap']['Take a Test'] = array('key' => 'Fisdap_Take_a_test', 'url' => "https://members.fisdap.net/learning-center");
        }

        if ($isInstructor || $sn->hasSkillsTracker() || ($sn->hasScheduler() && $user->getCurrentProgram()->scheduler_beta)) {
            $portfolioTitle = "MyPortfolio";

            if ($isInstructor) {
                $portfolioTitle = "Portfolios";
            }

            $links['Fisdap'][$portfolioTitle] = array('key' => 'Fisdap_MyPortfolio', 'url' => "/portfolio");
        }

        $links['Fisdap']['Open Airways'] = array('key' => 'Fisdap_Open_Airways', 'url' => "http://www." . Util_HandyServerUtils::get_server() . ".net/whats_new/open_airways");

        if ($isInstructor || $sn->hasScheduler()) {
            $links['Fisdap']['Schedule'] = array('key' => 'Fisdap_Schedule', 'url' => "/scheduler");
        }

        $links['Fisdap']['PCRF Research Podcasts'] = array('key' => 'Fisdap_PCRF_Research', 'url' => "http://www." . Util_HandyServerUtils::get_server() . ".net/podcasts/pcrf");

        if ($isInstructor) {
            $links['Fisdap']['Manage Account'] = array('key' => 'Fisdap_Manage_Account', 'url' => "/account/edit/instructor");
        } else {
            $links['Fisdap']['Manage Account'] = array('key' => 'Fisdap_Manage_Account', 'url' => "/account/edit/student");
        }

        if ($isInstructor || $sn->hasSkillsTracker()) {
            $links['Fisdap']['Document Patient Care'] = array('key' => 'Fisdap_Document_Patient_Care', 'url' => "/skills-tracker/shifts");
        }

        $links['Fisdap']['Fisdap Help'] = array('key' => 'Fisdap_Help', 'url' => "/help");
        $links['Fisdap']['How to Pass a Fisdap Exam'] = array('key' => 'Fisdap_How_To_Pass_Fisdap_Exam', 'url' => "https://testing-instructions.s3.amazonaws.com/how_to_succeed.pdf");
        $links['Fisdap']['Fisdap Tutorials'] = array('key' => 'Fisdap_Tutorials', 'url' => "http://www." . Util_HandyServerUtils::get_server() . ".net/support/getting_started/fisdap_tutorials");

        $links['Fisdap']['Release Log'] = array('key' => 'Fisdap_Release_Log', 'url' => "http://www." . Util_HandyServerUtils::get_server() . ".net/whats_new/release_history");

        if ($isInstructor) {
            $links['Fisdap']['Preceptor Training'] = array('key' => 'Fisdap_Preceptor_Training', 'url' => \Fisdap\MoodleUtils::getUrl("preceptor_training"));
        }

        if ($isInstructor) {
            $links['Fisdap']['Webinars'] = array('key' => 'Fisdap_Webinars', 'url' => "http://www.fisdap.net/whats_new/webinars");
        }

        $links['Social']['Facebook'] = array('key' => 'Social_Facebook', 'url' => "http://facebook.com");
        $links['Social']['Twitter'] = array('key' => 'Social_Twitter', 'url' => "http://twitter.com");
        $links['Social']['Google+'] = array('key' => 'Social_GooglePlus', 'url' => "http://plus.google.com");

        // Sort the links alphabetically before sending them off...
        foreach ($links as $sectionName => $linkSection) {
            ksort($links[$sectionName]);
        }

        ksort($links);

        return $links;
    }

    private function getIncludedLinks()
    {
        $links = $this->getLinks();

        $cleanLinks = array();

        foreach ($links as $sectionName => $sectionLinks) {
            foreach ($sectionLinks as $linkTitle => $linkData) {
                if (isset($this->data[$linkData['key']])) {
                    $cleanLinks[$sectionName][$linkTitle] = $linkData;
                }
            }
        }

        return $cleanLinks;
    }

    public function getDefaultData()
    {
        return array(
            'Navigate_2' => 1,
            'Fisdap_Study_Tools' => 1,
            'Fisdap_Take_a_test' => 1,
            'Fisdap_MyPortfolio' => 1,
            'Fisdap_Open_Airways' => 1,
            'Fisdap_Schedule' => 1,
            'Fisdap_Document_Patient_Care' => 1,
            'Fisdap_Help' => 1,
            'Fisdap_Release_Log' => 1,
            'Fisdap_Preceptor_Training' => 1,
            'Fisdap_Webinars' => 1
        );
    }

    public function getConfigurationFormId()
    {
        return $this->getNamespacedName('configure-quick-links-form');
    }

    public function getConfigurationForm()
    {
        $links = $this->getLinks();

        $form = "
			<form id='{$this->getConfigurationFormId()}'>
				<div class='quick-links-config-div'>
					<input type='hidden' name='wid' value='{$this->widgetData->id}' />
		";

        foreach ($links as $sectionName => $sectionLinks) {
            $form .= "<h2 class='quick-link-section-header'>" . $sectionName . "</h2>";

            foreach ($sectionLinks as $linkTitle => $linkData) {
                $checkedText = "";

                if (isset($this->data[$linkData['key']])) {
                    $checkedText = "checked='CHECKED'";
                }

                $form .= "<div class='quick-link-a'><input type='checkbox' value='1' name='" . $linkData['key'] . "' {$checkedText}/>{$linkTitle}</div>";
            }
        }

        $form .= "
			</div>
			</form>
		";

        return $form;
    }
}
