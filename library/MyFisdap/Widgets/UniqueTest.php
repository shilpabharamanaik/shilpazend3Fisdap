<?php

class MyFisdap_Widgets_UniqueTest extends MyFisdap_Widgets_Base
{
    public function render()
    {
        $html = "I am unique.";
        return $html;
    }
    
    public function getDefaultData()
    {
        return array();
    }
}
