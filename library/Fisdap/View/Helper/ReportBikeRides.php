<?php

class Fisdap_View_Helper_ReportBikeRides extends Zend_View_Helper_Abstract 
{
    
    protected $_html;
    
    public function reportBikeRides($passcode = null)
    {
        
        $this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/reportBikeRides.js");
        
        //Grab all existing bike rides
		$em = \Fisdap\EntityUtils::getEntityManager();
		
		if ($passcode) {
	        //$rides = \Fisdap\EntityUtils::getRepository("BikeRideEvent")->findBy(array("passcode" => $passcode));
			$rides = $em->createQuery("SELECT r FROM \Fisdap\Entity\BikeRiderData r JOIN r.event e WHERE e.passcode = '$passcode'")->getResult();
		} else {
	        $rides = \Fisdap\EntityUtils::getRepository("BikeRiderData")->findAll();
		}
        
        $bikeRidersPartials = array();
        foreach($rides as $item)
        {
            $bikeRidersPartials[] = array('item' => $item);
        }
        
        $this->_html .= "<table class='fisdap-table'>";
        $this->_html .= "<thead><tr><th>First Name</th><th>Last Name</th>
                        <th>Email</th><th>Event</th><th>Paid</th><th>More Info</th></tr>
                        </thead>";
        $this->_html .= "<tbody>";
        if (count($bikeRidersPartials) > 0) {
	        $this->_html .= $this->view->partialLoop("bikeRiderRow.phtml", $bikeRidersPartials);			
		} else {
			$this->_html .= "<tr><td colspan='6'>There are no riders to display yet.</th></tr>";
		}
        
        $this->_html .= "</tbody>";
        $this->_html .= "</table>";
        return $this->_html;
    }
    
}