<?php

class Fisdap_View_Helper_ShowBikeEvents extends Zend_View_Helper_Abstract 
{

    protected $_html;
    
    public function showBikeEvents()
    {
		
		$this->view->headScript()->appendFile("/js/bike-ride/delete.js");
		
        //Grab all existing bike ride events
        
        $events = \Fisdap\EntityUtils::getRepository("BikeRideEvent")->findAll();
        
        $bikeEventsPartials = array();
        foreach($events as $item)
        {
            $bikeEventsPartials[] = array('item' => $item);
        }
        
        $this->_html .= "<table class='fisdap-table'>";
        $this->_html .= "<thead><tr><th>Id</th><th>Region</th><th>Origin</th><th>Destination</th><th>Available Roles</th>
                        <th>Start Date</th><th>Ending Date</th><th>Notes</th>
                        <th>Edit</th><th>Delete</th><th>etc.</th></tr></thead>";
        $this->_html .= "<tbody>";
        if (count($bikeEventsPartials) > 0) {
	        $this->_html .= $this->view->partialLoop("bikeRideEventRow.phtml", $bikeEventsPartials);			
		} else {
			$this->_html .= "<tr><td colspan='7'>Use links to create, format, and delete bike ride events.</th></tr>";
		}
		
        $this->_html .= "</tbody>";
        $this->_html .= "</table>";
        $this->_html .= "<p><a href='/bike-ride/create/'>Create New Event</a></p>";
        return $this->_html;
    }



}