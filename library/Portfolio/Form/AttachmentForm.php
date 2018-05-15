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
 * Description of AttachmentForm
 *
 * @author astevenson
 */
class Portfolio_Form_AttachmentForm extends Fisdap_Form_BaseJQuery
{
    public function init()
    {
        $this->addPrefixPath('Fisdap_Form_Decorator', 'Fisdap/Form/Decorator/', 'decorator');
        
        $this->addElement(new Zend_Form_Element_File('file', array('size' => 40)));
        $this->addElement(new Fisdap_Form_Element_TextareaHipaa('description', array('label' => 'Description:', 'description' => '(optional)', 'rows' => 7, 'cols' => 40)));
        $this->addElement(new Zend_Form_Element_Submit('submit', array('label' => 'Upload', 'id' => 'upload-file-btn')));
        
        $this->setElementDecorators(array(
            'ViewHelper',
            array(array('break' => 'HtmlTag'), array('tag' => 'br', 'openOnly' => true, 'placement' => 'PREPEND')),
            array('LabelDescription', array('escape' => false)),
            array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
        ), array('description'), true);
    }
}
