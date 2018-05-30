<?php

/* * **************************************************************************
 *
 *         Copyright (C) 1996-2011.  This is an unpublished work of
 *                          Headwaters Software, Inc.
 *                             ALL RIGHTS RESERVED
 *         This program is a trade secret of Headwaters Software, Inc.
 *         and it is not to be copied, distributed, reproduced, published,
 *         or adapted without prior authorization
 *         of Headwaters Software, Inc.
 *
 * ************************************************************************** */

/**
 * This is a view helper to display an instructor picker widget
 *
 * @author astevenson
 */
class Fisdap_View_Helper_InstructorPicker extends Zend_View_Helper_Abstract
{
    
    /**
     * This helper is used to generate a chunk of HTML and JS that will allow someone
     * to pick an instructor from the current program.
     *
     * @param integer $instructorId ID of the instructor to show up pre-selected (I guess)...
     * @param string $clickLink String containing the URL to link to when a user
     *		clicks on a name from the main search box dropdown options
     * @param string $listBaseLink String containing the URL to use for the
     *		results that return when searching using the "Go" button.  If null,
     *		defaults to $clickLink.
     * @param string $instructorDropdownLink String containing the URL to redirect
     *		to when a user selects a instructor from the advanced search instructor
     *		dropdown.  If null, defaults to $clickLink.
     * @return type String containing the HTML for the instructor selector.
     */
    public function instructorPicker($instructorId, $clickLink, $listBaseLink=null)
    {
        if ($listBaseLink == null) {
            $listBaseLink = $clickLink;
        }

        $this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/instructorPicker.js");
        $this->view->headLink()->appendStylesheet("/css/library/Fisdap/View/Helper/instructorPicker.css");
        
        $user = \Fisdap\Entity\User::getLoggedInUser();
        
        $instructors = \Fisdap\EntityUtils::getRepository('User')->getAllInstructorsByProgram($user->getProgramId());

        $instructorOptions = array('Select one...');
        foreach ($instructors as $instructor) {
            $instructorOptions[$instructor['id']] = $instructor['first_name'] . " " . $instructor['last_name'];
        }

        $defaultMessage = 'Type an instructor\'s name, username, or email, etc.';
        
        $this->view->headScript()->appendFile("/js/jquery.fieldtag.js");
        
        $html = <<<HTML
			<div id="find-instructor-container" class="grid_12">
			{$this->view->autoComplete('instructorSearch', '', array('select' => new Zend_Json_Expr('function( event, ui ) { location.href="' . $clickLink . '" + ui.item.id; }'), 'source' => '/ajax/usersearch/role/instructor/'), array('size' => 80, 'title' => $defaultMessage))}
			<script type="text/javascript">
				var listBaseURL = '{$listBaseLink}';

				$('#instructorSearch').fieldtag();

				$('#instructorSearch').autocomplete({
					// This is a complete and utter hack.  Using it to restyle the popup
					// that appears when you're searching for people.
					open: function(event, ui){
						// Add a border to the autocomplete stuff...
						$('.ui-autocomplete').css('border', '1px solid black');

						$('.ui-autocomplete').find('a').each(function(index, el){
							$(el).css('font-size', '10pt');
						});
					}
				});
			</script>
			{$this->view->formButton('go-btn', 'Go', array('class' => 'small'))}


			<div style='margin-top: 5px; padding-top: 5px;'><a href="#" id="advanced-search-link" class='small-link'>Advanced Search<img id="arrow" style="width:1em;" src="/images/arrow_left.png"></a></div>
			<div style='clear: both'></div>
			<div id='advanced-search-container'>
				<form id='advanced-search-form'>
					<div class="filter-prompt">
						Instructor:<br>
						{$this->view->formSelect('instructor-list', $instructorId, array(), $instructorOptions)}
					</div>
				</form>
			</div>
		</div>
		<div id="instructor-results-container" class="grid_12" style="display: none">
			<h2 id="instructor-list-title" class="section-header">Search Results</h2>
			Did you mean... ?
			<br />
			<div id="instructor-results" class="grid_12">
			</div>
		</div>
HTML;
                        
        return $html;
    }
}
