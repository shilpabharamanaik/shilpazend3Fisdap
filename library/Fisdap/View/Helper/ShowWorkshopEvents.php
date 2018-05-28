<?php

class Fisdap_View_Helper_ShowWorkshopEvents extends Zend_View_Helper_Abstract 
{

    protected $_html;
    
    public function showWorkshopEvents()
    {
		
	$this->view->headScript()->appendFile("/js/events/delete.js");
		
        //Grab all existing workshops
        $workshops = \Fisdap\EntityUtils::getRepository("Workshop")->findAll();
        
        $workshopEventsPartials = array();
        foreach($workshops as $item)
        {
            $workshopEventsPartials[] = array('item' => $item);
        }
        
        $this->_html .= "<table class='fisdap-table'>";
        $this->_html .= "<thead><tr><th>Id</th><th>Location</th><th>Date</th><th>Deadline</th><th>Cost</th>
                        <th>Duration</th><th>Email Subject</th><th>Email Body</th>
                        <th>Edit</th><th>Delete</th></tr></thead>";
        $this->_html .= "<tbody>";
        if (count($workshopEventsPartials) > 0) {
	        $this->_html .= $this->view->partialLoop("workshopEventRow.phtml", $workshopEventsPartials);			
		} else {
			$this->_html .= "<tr><td colspan='6'>Use links to create, format, and delete workshop events.</th></tr>";
		}
		
        $this->_html .= "</tbody>";
        $this->_html .= "</table>";
        $this->_html .= "<p><a href='/events/update/'>Create New Workshop</a></p>";
        return $this->_html;
    }



}