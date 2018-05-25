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
 * Base class for Fisdap jQuery Forms to inherit from
 */

/**
 * @package    Fisdap
 */
class Fisdap_Form_BaseJQuery extends ZendX_JQuery_Form
{
    /**
     * @var Zend_View
     */
    protected $_view;
 
    public static $gridElementDecorators = array(
        'ErrorHighlight',
        'ViewHelper',
        array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_9')),
        array('Label', array('tag' => 'div', 'class' => 'grid_3', 'escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
        array(array('break' => 'HtmlTag'), array('tag'=>'div', 'class'=>'clear', 'placement' => 'APPEND')),
    );
    
    /**
	 * @var array decorators for jQuery form elements
	 */
	public static $strippedFormJQueryElements = array(
        'ErrorHighlight',
        array('UiWidgetElement', array('tag' => '')), // it necessary to include for jquery elements
	);
    
    const NSC_DIAMOND = "<img class='nsc-diamond' src='/images/nsc_diamond.png'>";
   
    public function init()
    {
        
        parent::init();
    }
    
    public function __construct($options = null)
    {
        if (!$this->_view) {
            $this->_view = $this->getView();
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
     * Serialize as string
     *
     * Proxies to {@link render()}.
     *
     * Modifying to work around Whoops library re-throwing an exception from
     * the trigger_error() that would normally be here ~bgetsug
     *
     * @see http://framework.zend.com/issues/browse/ZF-2528
     * @return string
     */
    public function __toString()
    {
        try {
            $return = $this->render();
            return $return;
        } catch (Exception $e) {
            // instead of trigger_error(), just log the exception
            $exceptionLogger = Zend_Registry::isRegistered('exceptionLogger') ? Zend_Registry::get('exceptionLogger') : null;

            if ($exceptionLogger !== null) {
                $exceptionLogger->log($e);
            }

            return '';
        }
    }
}
