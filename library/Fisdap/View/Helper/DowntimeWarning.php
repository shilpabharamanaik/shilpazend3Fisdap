<?php

/****************************************************************************
*
*         Copyright (C) 1996-2011.  This is an unpublished work of
*                          Headwaters Software, Inc.
*                             ALL RIGHTS RESERVED
*         This program is a trade secret of Headwaters Software, Inc.
*         and it is not to be copied, distributed, reproduced, published,
*         or adapted without prior authorization
*         of Headwaters Software, Inc.
*
****************************************************************************/

/**
 * This is a view helper for the intrusive impending-downtime warning
 */
class Fisdap_View_Helper_DowntimeWarning extends Zend_View_Helper_Abstract
{
    /**
     * Default entry point for this class.
     *
     * @return string HTML of the warning
     */
    public function downtimeWarning()
    {
        // check if we have a downtime/maintenance warning to display
        $repo = \Fisdap\EntityUtils::getRepository('Maintenance');
        $maintenance = $repo->getCurrentMaintenance();
        
        if ($maintenance instanceof \Fisdap\Entity\Maintenance) {
        
            // get the minutes-until-downtime count
            $nowDate = new \DateTime('now');
            $interval = $nowDate->diff($maintenance->downtime_starts);
            $this->_html = "";
            
            $this->_html .= '<div style="" id="downtime-warning">
									  <div><strong>Update</strong>: The Fisdap website will be unavailable starting in
									  ' . (($interval->format('%h')) ? $interval->format('%h hour(s) and %i minute(s)') : $interval->format('%i minutes')) . ' for
									  planned system maintenance. ' . (($maintenance->downtime_ends) ? 'It may be down until ' . $maintenance->downtime_ends->format('g:ia T M j') . '. ' : '')
                                      . 'Please save any changes and log out before this time.' . (($maintenance->notes) ? ' ' . $maintenance->notes : '') . '
									  </div>
	
									  <div class="contact-support">
									   Questions? Call 621.690.9241 (our business hours are 8am to 5:30pm CDT) or email
									   <a href="mailto:support@fisdap.net">support@fisdap.net</a>.
									 </div>
			</div>';
            
            return $this->_html;
        } else {
            return '';
        }
    }
}
