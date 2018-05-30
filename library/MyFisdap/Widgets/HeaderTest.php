<?php

class MyFisdap_Widgets_HeaderTest extends MyFisdap_Widgets_Base
{
    public function render()
    {
        $html = "Just some text.  Nothing configurable here.";
        return $html;
    }
    
    public function getDefaultData()
    {
        return array();
    }
    
    public function renderHeader()
    {
        return "<span style='background-color: #808080'>Custom Header. <button onclick='alert(\"I belong to widget #{$this->widgetData->id}.\")'>Button.</button></span>";
    }
}
