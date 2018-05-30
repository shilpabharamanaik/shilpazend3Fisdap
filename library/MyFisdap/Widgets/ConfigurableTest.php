<?php

class MyFisdap_Widgets_ConfigurableTest extends MyFisdap_Widgets_Base implements MyFisdap_Widgets_iConfigurable
{
    public function render()
    {
        $html = <<<EOF
			<div id='{$this->getNamespacedName('main-div')}' style="width: 200px; height:200px; background-color: {$this->data['color']}">
				Crazy Div.
			</div>
EOF;
        return $html;
    }
    
    public function getDefaultData()
    {
        return array('color' => '#0000FF');
    }
    
    public function getConfigurationFormId()
    {
        return $this->getNamespacedName('configure-test-form');
    }
    
    public function getConfigurationForm()
    {
        $form = "
			<form id='{$this->getConfigurationFormId()}'>
				<input type='hidden' name='wid' value='{$this->widgetData->id}' />
				<input type='text' name='color' value='{$this->data['color']}' />
			</form>
		";
        
        return $form;
    }
}
