<?php
use Fisdap\Entity\EthnioScreener;
use Fisdap\EntityUtils;

/**
 * Class Admin_Form_ManageEthnioScreeners
 * @author Sam Tape <stape@fisdap.net>
 */
class Admin_Form_ManageEthnioScreeners extends Fisdap_Form_Base
{
    /**
     * @var EthnioScreener[]
     */
    public $screeners;

    /**
     * Grab all of the EthnioScreener entities and add them to $this
     *
     * @param null $options
     */
    public function __construct($options = null)
    {
        $this->screeners = \Fisdap\EntityUtils::getRepository("EthnioScreener")->findAll();

        parent::__construct($options);
    }

    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        //load CSS and JS files for sliders
        $this->addJsFile("/js/jquery.sliderCheckbox.js");
        $this->addCssFile("/css/jquery.sliderCheckbox.css");

        //Loop over the screeners adding a text field and checkbox for each one
        foreach($this->screeners as $screener) {
            $slider = new Fisdap_Form_Element_jQueryCheckboxSlider("screenerActive_" . $screener->id);
            $slider->setChecked($screener->active)
                    ->setDecorators(array('ViewHelper'));
            $this->addElement($slider);

            $screenerId = new Zend_Form_Element_Text("screenerId_" . $screener->id);
            $screenerId->setValue($screener->screener_id)
                ->setDecorators(array('ViewHelper'))
                ->setRequired(true)
                ->addErrorMessage('You must enter a Screener #')
                ->setLabel($screener->name);
            $this->addElement($screenerId);
        }

        $saveBtn = new Fisdap_Form_Element_SaveButton('saveBtn');
        $saveBtn->setLabel('Submit');
        $this->addElement($saveBtn);

        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => "forms/manageEthnioScreenersForm.phtml")),
            'Form',
        ));
    }

    /**
     * Process method to save the EthnioScreener entities
     *
     * @param array $post
     * @return bool
     * @throws Zend_Form_Exception
     */
    public function process(array $post)
    {
        if ($this->isValid($post)) {
            $values = $this->getValues();

            foreach($this->screeners as $screener) {
                $screener->active = $values['screenerActive_' . $screener->id];
                $screener->screener_id = $values['screenerId_' . $screener->id];
            }

            EntityUtils::getEntityManager()->flush();

            return true;
        }
        return false;
    }

}