<?php

/* ***************************************************************************
 *
 *         Copyright (C) 1996-2014.  This is an unpublished work of
 *                          Headwaters Software, Inc.
 *                             ALL RIGHTS RESERVED
 *         This program is a trade secret of Headwaters Software, Inc.
 *         and it is not to be copied, distributed, reproduced, published,
 *         or adapted without prior authorization
 *         of Headwaters Software, Inc.
 *
 * ************************************************************************** */

/**
 * Table that acts as sort of a select element, with checkmarks
 * inspired by GoalSetTable view helper
 * @package Fisdap
 * @author jmortenson
 */
class Fisdap_View_Helper_CheckmarkTableGeneric extends Zend_View_Helper_Abstract
{
    /*
     * $config contains report configuration (Fisdap/Reports)
     * $options configuration for the view helper, including $options['rows'] which define selectable options
     *		$options = array(
     *		    'fieldName' => 'stirng', // name to be used for the form element
		    'summaryLabel' => 'string', // label to be used in report summary for this field
     *		    'rows' => 
			array(
			    'value' => 'value1', // machine representation of the value if this row is selected
			    'content' => array( // array of cells to display in the row, can be 1 or more, text or markup
				'Jones Valley Base 1'
			    ),
			),
			array(
			    'value' => 'value2',
			    'content' => array(
				'Jones Valley Base 2 (<strong>note!</strong>)',  
			    ),
			    'default' => TRUE, // setting default to TRUE makes this the default selection
			),
			array(
			    'value' => 'value3',
			    'content' => array(
				'Jones Valley Base 3',  
			    ),
			),
		    ),
		);
	// please be consistent with number of content columns in each option row!
     */
    public function checkmarkTableGeneric($config = array(), $options = array()) {
	// set a default fieldname if none is set
	if (!isset($options['fieldName'])) {
	    $options['fieldName'] = 'selected-row'; 
	}
	
	// figure out what mode we're in
	$selectMode = $options['multiSelect'] ? "multiple" : "single";
	
	// figure out what the selected row is
	if (isset($config[$options['fieldName']])) {
		// use the config row if there is one
		$selectedRow = $config[$options['fieldName']];
	} else if ($options['selected']) {
		// otherwise, if there's a given default, pick that one
		$selectedRow = $options['selected'];
	} else if (count($options) == 1) {
		// otherwise, if there's only one row, pick that one
		$selectedRow = $options[0]['value'];
	} else {
	    $selectedRow = null; // no row has been selected by the user yet
	}
	$selectedRow = explode(",", $selectedRow);

    // JS / CSS for the widget
	$this->view->headLink()->appendStylesheet('/css/library/Fisdap/View/Helper/checkmark-table-generic.css');
    $this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/checkmark-table-generic.js");
		
	if (isset($config['id'])) {
	    $id = 'id="' . $config['id'] . '"';
	}
	
	// if required, add the searching stuff
	if ($options['searchable']) {
		$html .= '<div class="table-tools">
					<input class="search-box" type="text" autocomplete="off" value="" name="table_search">
				 </div>';
	}
	
	// if required, add the multi-select mode stuff
	if ($options['multiSelect']) {
		$html .= '<div class="table-tools multi-select-tools">
					<span class="num-selected">0</span> of '.count($options['rows']).' selected.
					<a class="select-aller" data-mode="all" href="#">select all</a>
					|
					<a class="select-aller" data-mode="none" href="#">select none</a>
				 </div>';
	}
	
	// build the table!
	$html .= "<div class='fisdap-table-scrolling-container checkmark-table' {$id}>";
	$html .= "<table class='fisdap-table scrollable'>";
	$html .= "<tbody>";
	
	$html .= $this->renderCheckmarkTableRows($options['rows']);
	
	$selectedRow = implode(",", $selectedRow);
	$html .= "</tbody>";
	$html .= "</table>";
	$html .= "<input type='hidden' name='select-mode' value='{$selectMode}'>";
	$html .= "<input type='hidden' class='selected-row' name='{$options['fieldName']}' value='{$selectedRow}'>";
	$html .= "</div>";
	
        return 	$html;
    }
    
    public function checkmarkTableGenericSummary($options = array(), $config = array())
    {
		// set a default fieldname if none is set
		if (!isset($options['fieldName'])) {
		    $options['fieldName'] = 'selected-row'; 
		}
		if (!isset($options['summaryLabel'])) {
		    $options['summaryLabel'] = 'Selected';
		}
		
		// get label for the selected row (assuming first content cell is label)
		$selectedRows = explode(",", $config[$options['fieldName']]);
		$label = array();
		foreach($options['rows'] as $row) {
		    if (in_array($row['value'], $selectedRows)) {
				$label[] = $row['content'][0];
		    }
		}
		
		return array($options['summaryLabel'] => implode(", ", $label));
    }

    public function renderCheckmarkTableRows($rows = array())
    {
        $html = '';

        // add each of the rows to the table
        foreach ($rows as $row) {
            $html .= "<tr data-rowvalue='{$row['value']}'>";
            foreach($row['content'] as $cell) {
                $html .= "<td>" . $cell . '</td>';
            }
            $html .= "</tr>";
        }
        return $html;
    }
}