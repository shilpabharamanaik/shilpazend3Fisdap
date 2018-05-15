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
 * Description of StudentFilter
 *
 * @author astevenson
 */
class Reports_Form_Element_AdvancedSettings extends Zend_Form_Element_Xhtml
{
    /**
     * @var string the view helper that will render this composite element
     */
    public $helper = "advancedSettingsElement";
    
    public static $patientTypes = array(
        'live humans' => 1,
        'live animals' => 3,
        'manikin sims' => 5,
        'dead humans' => 2,
        'dead animals' => 4,
        'other sims' => 6,
    );
    
    public function init()
    {
        $this->getView()->headScript()->appendFile("/js/library/Reports/Form/Element/advanced-settings.js");
    }
    
    public function getValue()
    {
        // returns patient types as array containing db-ids of selected patient types
        if (is_array($this->_value) && !isset($this->_value['patient-types'])) {
            $value = $this->_value;
            $value['patient-types'] = array();
            if (is_array($value['patient-type'])) {
                foreach ($value['patient-type'] as $type) {
                    $value['patient-types'][] = self::$patientTypes[$type];
                }
            }
            $this->setValue($value);
        }
        return parent::getValue();
    }
}
