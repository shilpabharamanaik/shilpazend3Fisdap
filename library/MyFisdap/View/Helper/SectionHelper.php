<?php

class MyFisdap_View_Helper_SectionHelper extends Zend_View_Helper_Abstract
{
    public static $hasLoaded = false;

    public function sectionHelper($sectionName, $sectionWidth){
        $this->view->headScript()->appendFile("/js/my-fisdap/WidgetFunctions.js");
        $this->view->headLink()->appendStylesheet("/css/my-fisdap/widgetStyle.css");

        $html = $this->loadStyles();

        // Removing this for now- users will not be able to add in their own widgets in Phase 1.

        /*
        $html .= "<div>";
        $availableWidgets = \Fisdap\EntityUtils::getRepository('MyFisdapWidgetData')->getAvailableWidgetsForSection($sectionWidth);

        $html .= "<select id='add-widget-{$sectionName}'>";
        foreach($availableWidgets as $widget){
            $html .= "<option value='{$widget->id}'>{$widget->display_title}</option>";
        }

        $html .= "
                </select>
                <button id='confirm-add-widget-{$sectionName}'>+</button>
            </div>";
        */

        $html .= "
			<div id='{$sectionName}-section' class='widget-section'></div>
			
			<script>
				$(function(){
					loadWidgets('{$sectionName}', '{$sectionName}-section');
					
					$('#confirm-add-widget-{$sectionName}').click(function(){
						addWidget('{$sectionName}', $('#add-widget-{$sectionName}').val());
					});
				});
			</script>
		";

        return $html;
    }

    private function loadStyles(){

        if(!self::$hasLoaded){
            $publicWidgetCSSPath = realpath(APPLICATION_PATH . '/../public/css/my-fisdap/widget-styles');

            $scriptTag = "<style>";

            if ($handle = opendir($publicWidgetCSSPath)) {
                while (false !== ($entry = readdir($handle))) {
                    $explodedEntry = explode('.', $entry);

                    if($explodedEntry[1] == "css"){
                        $scriptTag .= "\n\n" . file_get_contents($publicWidgetCSSPath . '/' . $entry) . "\n\n";
                    }
                }

                closedir($handle);
            }

            $scriptTag .= "</style>";

            self::$hasLoaded = true;

            return $scriptTag;
        }
    }
}