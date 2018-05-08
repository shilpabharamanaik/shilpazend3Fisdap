<?php
namespace Zend\View\Helper;
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
 * This is a view helper for the main site footer.  Effectively renders itself,
 * and includes the required CSS files.
 */
class Zend_View_Helper_MainFooter extends Zend_View_Helper_Abstract
{
	/**
	 * Default entry point for this class.  Returns the HTML to be used for the
	 * primary site footer panel.  This panel shouldn't really vary too much
	 * overall between pages, so shouldn't need any arguments/config settings.
	 *
	 * @return string HTML for the footer.
	 */
	public function mainFooter()
	{
		$html = "
			
			<div id='footer' class='footer'>
				<div id='footer_text' class='footer_text'>
					651-690-9241
					<span class='footer_separator'>|</span>
					
					<a href='mailto:info@fisdap.net'>info@fisdap.net</a>
				</div>
			</div>
		";
		
		return $html;
	}
}