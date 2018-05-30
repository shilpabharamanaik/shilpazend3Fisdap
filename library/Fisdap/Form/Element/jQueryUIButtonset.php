<?php

/**
 * Class creating a composite jQuery UI Buttonset element
 */
class Fisdap_Form_Element_jQueryUIButtonset extends Zend_Form_Element_Xhtml
{
    /**
     * @var Value of the form element
     */
    protected $_value;
    
    /**
     * @var boolean disabled
     */
    protected $_disabled;

    /**
     * @var string the view helper that will render this composite element
     */
    public $helper = "jQueryUIButtonsetElement";
    
    /**
     * @var array The options available in the buttonset
     */
    public $options;


    public function init()
    {
        $this->setUiTheme("cupertino");
    }


    /**
     * Set the jQuery UI theme for this element
     *
     * @param string $theme
     *
     * @return $this
     */
    public function setUiTheme($theme)
    {
        $this->setAttrib("ui-theme", $theme);
        
        return $this;
    }


    /**
     * Set the jQuery UI size for this element
     *
     * @param string $size
     *
     * @return $this
     */
    public function setUiSize($size)
    {
        $this->setAttrib("ui-size", $size);
        
        return $this;
    }


    /**
     * Set the width for the buttons in this element
     *
     * @param $width
     *
     * @return $this
     * @throws Zend_Form_Exception
     */
    public function setButtonWidth($width)
    {
        $styling = array('label_style' => 'width: '.$width);
        $this->setAttrib("radio", $styling);
        
        return $this;
    }


    /**
     * Set the value of this form element
     *
     * @param mixed $value either the array of value and disabled, or just the value
     * @return Fisdap_Form_Element_jQueryUIButtonset the form element
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            $this->_value = $value['value'];
            $this->_disabled = $value['disabled'];
        } else {
            $this->_value = $value;
            if ($this->_value == -1) {
                $this->_disabled = true;
            } else {
                $this->_disabled = false;
            }
        }
        
        return $this;
    }


    /**
     * returns the value of this object
     *
     * @return int|null
     */
    public function getValue()
    {
        if ($this->_disabled) {
            return -1;
        } else {
            return $this->_value;
        }
    }


    /**
     * @inheritdoc
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        
        return $this;
    }
}
