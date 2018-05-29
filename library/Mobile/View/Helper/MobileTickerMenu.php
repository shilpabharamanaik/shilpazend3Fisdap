<?php

class Mobile_View_Helper_MobileTickerMenu extends Zend_View_Helper_Abstract
{
    protected $_html;
    
    /**
	 * @return string the mobile ticker menu rendered as an html table
	 */
	public function mobileTickerMenu()
	{
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $uri = $request->getModuleName() . "/" . $request->getControllerName() . "/" . $request->getActionName();
        
		$this->_html .= "<div id='menu-container'>";

        if ($uri == "mobile/index/index") {
            $this->_html .= "<div class='menu-button-left menu-button-selected'>Shift List</div>";
            $this->_html .= "<a href='/mobile/index/goals-report/'><div class='menu-button-right'>Goals Report</div></a>";
            $this->_html .= "<div class='menu-ticker' style='display:block;'><img src='/images/mobile_ticker.png'></div>";
        } else if ($uri == "mobile/index/goals-report") {
            $this->_html .= "<a href='/mobile/index/index/'><div class='menu-button-left'>Shift List</div></a>";
            $this->_html .= "<div class='menu-button-right menu-button-selected'>Goals Report</div>";
            $this->_html .= "<div class='menu-ticker menu-ticker-right' style='display:block;'><img src='/images/mobile_ticker.png'></div>";
        } else {
            $this->_html .= "<a href='/mobile/index/index/'><div class='menu-button-left'>Shift List</div></a>";
            $this->_html .= "<a href='/mobile/index/goals-report/'><div class='menu-button-right'>Goals Report</div></a>";
        }
        
        $this->_html .= "</div>";
		
        
        return $this->_html;
    }
}