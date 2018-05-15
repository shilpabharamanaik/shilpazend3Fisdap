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
 * Base class for Fisdap Forms to inherit from
 */

/**
 * @package    Fisdap
 */
class Fisdap_Form_Base extends Zend_Form
{
    /**
     * @var Zend_View
     */
    protected $_view;

    /**
     * @var boolean
     */
    public $isInstructor;

    /**
     * @var array decorators for checkbox elements
     */
    public static $checkboxDecorators = array(
        'ViewHelper',
        array('Label', array('placement' => 'APPEND')),
    );

    /**
     * @var array of decorators for a checkbox that can handle HTML in the label
     */
    public static $checkboxHTMLDecorators = array(
        'ViewHelper',
        array('LabelDescription', array('escape' => false, 'placement' => 'APPEND')),
    );

    /**
     * @var array decorators for multi checkbox elements
     */
    public static $multiCheckboxDecorators = array(
        'ViewHelper',
        array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'multi-checkboxes')),
        array('Label', array('tag' => 'div', 'escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-prompt')),
    );


    /**
     * @var array decorators for buttons
     */
    public static $buttonDecorators = array(
        'ViewHelper',
    );

    /**
     * @var array decorators for elements that are always hidden; stripped down html
     */
    public static $hiddenElementDecorators = array(
        'ViewHelper',
    );

    /**
     * @var array decorators for displaying custom elements
     */
    public static $strippedDecorators = array(
        'ViewHelper',
        array('LabelDescription', array('escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-prompt')),
    );

    /**
     * @var array decorators for individual elements
     */
    public static $elementDecorators = array(
        'ErrorHighlight',
        'ViewHelper',
        array(array('break' => 'HtmlTag'), array('tag' => 'br', 'openOnly' => true, 'placement' => 'PREPEND')),
        array('LabelDescription', array('escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-prompt')),
    );

    /**
     * @var array decorators for individual elements
     */
    public static $floatingElementDecorators = array(
        'ErrorHighlight',
        'ViewHelper',
        array(array('break' => 'HtmlTag'), array('tag' => 'br', 'openOnly' => true, 'placement' => 'PREPEND')),
        array('LabelDescription', array('escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-prompt', 'style' => 'float: left;')),
    );

    /**
     * @var array decorators
     */
    public static $gridElementDecorators = array(
        'ErrorHighlight',
        'ViewHelper',
        array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_9')),
        array('LabelDescription', array('tag' => 'div', 'class' => 'grid_3', 'escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-prompt')),
    );

    /**
     * @var array decorators
     */
    public static $textElementDecorators = array(
        'ErrorHighlight',
        'ViewHelper',
        array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_8 text-input')),
        array('LabelDescription', array('tag' => 'div', 'class' => 'grid_4', 'escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-prompt')),
    );

    /**
     * @var array decorators
     */
    public static $longLabelGridElementDecorators = array(
        'ErrorHighlight',
        'ViewHelper',
        array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_5')),
        array('Label', array('tag' => 'div', 'class' => 'grid_7', 'escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-prompt')),
    );

    /*
     * @var array decorators for the most basic form element
     */
    public static $basicElementDecorators = array(
        'ErrorHighlight',
        'ViewHelper',
        array('Label', array('escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-prompt')),
    );

    /*
     * @var array decorators for a basic form element that is hidden on load
     */
    public static $basicHiddenElementDecorators = array(
        'ErrorHighlight',
        'ViewHelper',
        array('Label', array('escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-prompt hidden')),
    );

    // PLEASE, FOR THE LOVE OF CHRIST, USE THE FOLLOWING DECORATORS!!!
    /**
     * These are the decorators we should use FOR ALL FORMS: labels right-aligned in a column to the left of the input
     */
    public function getStandardFormDecorators($viewscript)
    {
        return array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => $viewscript)),
            array('Form', array('class' => 'standard-form'))
        );
    }

    /**
     * These are the decorators we should use FOR ALL FORM INPUTS: labels right-aligned in a column to the left of the input
     * @var array decorators
     */
    public static $standardFormInputDecorators = array(
        'ErrorHighlight',
        'ViewHelper',
        array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'input')),
        array('LabelDescription', array('class' => 'label', 'escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-prompt')),
    );

    const REQUIRED_SYMBOL = "*";

    const NSC_DIAMOND = "<img class='nsc-diamond' src='/images/nsc_diamond.png'>";

    public function init()
    {
        $this->addElementPrefixPath('Fisdap_Form_Decorator', 'Fisdap/Form/Decorator/', 'decorator');
        parent::init();
    }

    public function __construct($options = null)
    {
        if (!$this->_view) {
            $this->_view = $this->getView();
        }

        $this->addJsFile('/js/library/Fisdap/Form/global-form.js');

        $user = \Fisdap\Entity\User::getLoggedInUser();

        if ($user instanceof \Fisdap\Entity\User) {
            $this->isInstructor = (\Fisdap\Entity\User::getLoggedInUser()->getCurrentRoleName() == 'instructor');
        } else {
            $this->isInstructor = false;
        }

        parent::__construct($options);
    }

    public function addJsOnLoad($code)
    {
        $code = new Zend_Json_Expr($code);
        $this->_view->jQuery()->addOnLoad($code);
    }

    public function addJsFile($path)
    {
        $this->_view->headScript()->appendFile($path);
    }

    public function addCssFile($path)
    {
        $this->_view->headLink()->appendStylesheet($path);
    }

    /**
     * Alternative to using Zend's default error display logic.
     *
     * To use this function, just call $this->element->displayErrors
     * inside of the form's viewscript.
     *
     * @return string
     */
    public function displayErrors()
    {
        $html = "<div class='form-errors alert'><ul>";

        $messages = $this->getMessages();

        foreach ($messages as $element => $msg) {
            $html .= "<li>" . array_pop($msg) . "</li>";
        }

        $html .= "</ul></div>";

        if (count($messages) > 0) {
            return $html;
        }
    }

    /**
     * Adds a group of checkbox elements to the form and checks them based on an array of defaults
     *
     * @param $options a keyed array of the options for this group of checkboxes
     * @param $groupname a name for the group, to be used in naming the checkbox elements
     * @param $defaultChecked an array of ids for which elements should be checked by default
     * @return mixed
     */
    public function addCheckboxGroup($options, $groupname, $defaultChecked)
    {
        $checkboxArray = [];

        foreach ($options as $id => $name) {
            $checkboxName = $groupname . '_' . $id;
            $checkboxElement = new Zend_Form_Element_Checkbox($checkboxName);

            $checkboxElement->setDecorators(array('ViewHelper'));

            $this->addElement($checkboxElement);

            // Set the default value here.
            if (is_array($defaultChecked)) {
                $isChecked = in_array($id, $defaultChecked);
            } else {
                $isChecked = false;
            }

            $this->setDefaults(array($checkboxName => $isChecked));

            $checkboxArray[] = $checkboxElement;
        }

        return $checkboxArray;
    }

    /**
     * Loops through a given group of check boxes and returns an array of the ids of the checked boxes
     *
     * @param $data the form post results
     * @param $options a keyed array of the options for this group of checkboxes
     * @param $groupname a name for the group, to be used in naming the checkbox elements
     * @return mixed
     */
    public function getCheckboxGroupResults($data, $options, $groupname)
    {
        foreach ($options as $id => $name) {
            $checkboxName = $groupname . '_' . $id;

            if ($data[$checkboxName]) {
                $resultsArray[] = $id;
            }
        }

        return $resultsArray;
    }
}
