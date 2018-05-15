<?php

class MyFisdap_Widgets_InlineMarketing extends MyFisdap_Widgets_Base
{
    public function render()
    {
        $campaignBillboard = $this->getCampaignData();

        $action = $campaignBillboard->campaign->action;
        $message = $campaignBillboard->message;

        $html = "
			<div style='text-align: center; width: 100%; overflow: hidden'>
				<a $action>$message</a>
			</div>
		";

        return $html;
    }

    public function renderHeader()
    {
        return "";
    }

    /**
     * Override to remove the header
     *
     * @return String containing the HTML for the widget container.
     */
    public function renderContainer()
    {
        $widgetContents = $this->render();

        $header = $this->renderHeader();

        $html = <<<EOF
			<div id='widget_{$this->widgetData->id}_container' class='widget-container widget-container-blank'>
				<div id='widget_{$this->widgetData->id}_render' class='widget-render'>
					{$widgetContents}
				</div>
			</div>
EOF;

        return $html;
    }

    private function getCampaignData()
    {
        $userContext = $this->getWidgetUser()->getCurrentUserContext();

        $billboard = $this->getBillboard();

        foreach ($billboard->campaign_billboards as $cb) {
            if ($cb->campaign->isUserContextAuthorized($userContext) && $cb->campaign->isDateApproved()) {
                $campaignThings[] = $cb;
            }
        }

        $randomRecord = $campaignThings[rand(0, count($campaignThings) - 1)];

        return $randomRecord;
    }

    private function getBillboard()
    {
        $userContext = $this->getWidgetUser()->getCurrentUserContext();

        if ($userContext->isInstructor()) {
            $uniqueName = 'dashboard_instructor';
        } else {
            $uniqueName = 'dashboard_student';
        }

        $repos = \Fisdap\EntityUtils::getRepository('MarketingBillboardLegacy');

        $billboards = $repos->findBy(array('unique_name' => $uniqueName));

        if (count($billboards) != 1) {
            return false;
        } else {
            return $billboards[0];
        }
    }

    public function getDefaultData()
    {
        return array();
    }
}
