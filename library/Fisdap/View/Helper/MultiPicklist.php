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
 * A super generalized pick list that just lets us assign whatevers.
 * Previous authors: astevenson
 * @package Scheduler
 * @author jmortenson
 */
class Fisdap_View_Helper_MultiPicklist extends Zend_View_Helper_Abstract
{
    protected $_html;

    public $view;
    
    /**
     * Config is an array that overrides default settings so you can load session/user settings
     * $config = array(
     *  'student' => a single student id, int or string, for use in single mode only
     *  'multistudent_picklist_selected' => a comma-separated list of student ids, for use in multiple mode only
     *  'anonymous' => whether or not the anonymous box should be checked, 1 or NULL/0, for use in multiple mose only
     * );
     */
    public $config;

    public $options;
    
    /**
     * Primary view helper function to construct the HTML. Takes an $options array:
     * $options = array(
     *   type => "String", // String representing the type of item we're assigning (ex: 'students', 'instructors')
     *   assignableItems => array($itemId => $itemName, ...),
     *   assignedItems => array($itemId, $itemId, ...)
     * );
     * @var $options array
     * @return string HTML
     */
    public function multiPicklist($config = null, $options = array(), $view = null)
    {
        $this->config = $config;
        $this->options = $options;
        
        $this->view->headScript()->prependFile("/js/library/Fisdap/View/Helper/multiPicker.js");
        $this->view->headLink()->appendStylesheet("/css/library/Fisdap/View/Helper/multiPicker.css");
        
        $renderInfo = array(
            "assignable" => $options['assignableItems'],
            "assigned" => array(),
            "type" => $options['type']
        );

        //Only add assigned data if there's data to be added
        if ($config['multi_picklist_selected']) {
            $renderInfo['assigned'] = explode(',', $config['multi_picklist_selected']);
        }
        
        $this->_html .= $this->view->partial('multi-picklist.phtml', 'default', $renderInfo);
        
        return $this->_html;
    }
    
    public function MultiPicklistSummary($options = array(), $config = array())
    {
        $section = ucwords($options['type']);
        
        //If hybrid mode is on and we received a single student, handle it
        $items = explode(",", $config['multi_picklist_selected']);
        if (count($items) > 0) {
            $info = count($items);
        }
        
        return array($section => $info);
    }
    
    public function multiPicklistValidate($options, $config)
    {
        $errors = array();
        
        $selection = explode(',', $config['multi_picklist_selected']);
        
        // make sure a student is chosen
        if (count($selection) < 1 || $selection[0] == "") {
            $errors[$input_id][] = "Please select one or more " . $options['type'] . ".";
        }
        
        return $errors;
    }
}
