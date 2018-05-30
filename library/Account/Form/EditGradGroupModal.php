<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * Form for processing an activation code
 */

/**
 * @package    Account
 */
class Account_Form_EditGradGroupModal extends Fisdap_Form_Base
{
    
    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/editGradGroupModal.phtml")),
        array('Form'),
    );
    
    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();
        
        $this->addCssFile("/css/library/Account/Form/edit-grad-group.css");
        $this->addJsFile("/js/library/Account/Form/edit-grad-group.js");
        $this->addJsFile("/js/library/Account/Form/edit-groups-select.js");
        
        // add years/group select boxes
        $groups = new Fisdap_Form_Element_Groups('edit_groups');
        $this->addElement($groups);
                
        $grad = new Fisdap_Form_Element_GraduationDate('edit_grad');
        $grad->setYearRange(date("Y"), date("Y") + 5);
        $this->addElement($grad);
        
        $this->setDecorators(self::$_formDecorators);
    }

    public function process($data, $codes)
    {
        // step through the codes we have and update them
        foreach ($codes as $sn) {
            $code = \Fisdap\EntityUtils::getEntity('SerialNumberLegacy')->getBySerialNumber($sn);
            // we want to update the grad date
            if ($data['gradMonth'] != "do-not-change") {
                if ($data['gradMonth'] == 0 || $data['gradYear'] == 0) {
                    $code->graduation_date = null;
                } else {
                    $code->graduation_date = new \DateTime($data['gradYear'] . "-" . $data['gradMonth'] . "-01");
                }
            }
            
            // if we want to update the group
            if ($data['sectionId'] != "do-not-change") {
                if ($data['sectionId'] == 0) {
                    $code->group = null;
                } else {
                    $code->group = \Fisdap\EntityUtils::getEntity("ClassSectionLegacy", $data['sectionId']);
                }
            }
            
            $code->save();
        }
        
        return true;
    }
}
