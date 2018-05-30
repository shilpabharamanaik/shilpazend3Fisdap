<?php
/**
 * Returns a Script tag containing all the per-widget CSS files
 * by checking a pre-defined directory
 *
 * @return string HTML SCRIPT tag containing CSS code
 */
class MyFisdap_View_Helper_WidgetStylesHelper extends Zend_View_Helper_Abstract
{
    public function WidgetStylesHelper()
    {
        $publicWidgetCSSPath = realpath(APPLICATION_PATH . '/../public/css/my-fisdap/widget-styles');
            
        $scriptTag = "<style>";
    
        if ($handle = opendir($publicWidgetCSSPath)) {
            while (false !== ($entry = readdir($handle))) {
                $explodedEntry = explode('.', $entry);
            
                if ($explodedEntry[1] == "css") {
                    $scriptTag .= "\n\n" . file_get_contents($publicWidgetCSSPath . '/' . $entry) . "\n\n";
                }
            }
        
            closedir($handle);
        }
    
        $scriptTag .= "</style>";
    
        return $scriptTag;
    }
}
