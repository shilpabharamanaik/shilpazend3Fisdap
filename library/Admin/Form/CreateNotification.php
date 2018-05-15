<?php
/**
 * Created by PhpStorm.
 * User: pwolpers
 * Date: 8/25/14
 * Time: 3:59 PM
 */


/**
 * @package Admin
 */
class Admin_Form_CreateNotification extends Fisdap_Form_Base
{
    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        //load CSS and JS files for sliders and chosens
        $this->addJsFile("/js/jquery.sliderCheckbox.js");
        $this->addCssFile("/css/jquery.sliderCheckbox.css");
        $this->addJsFile("/js/jquery.chosen.relative.js");
        $this->addCssFile("/css/jquery.chosen.css");

        //type
        $type = new Fisdap_Form_Element_jQueryUIButtonset('type');
        $type->setOptions(\Fisdap\Entity\NotificationType::getFormOptions())
             ->setButtonWidth('140px')
             ->setUiSize('extra-small')
             ->setUiTheme('cupertino')
             ->setRequired(true)
             ->addErrorMessage('Type is required.')
             ->setDecorators(array("ViewHelper"));

        //title
        $title = new Zend_Form_Element_Text('title');
        $title->setAttribs(array('class' => 'fancy-input',
                                 'size' => '53', 'maxlength' => 80))
              ->setRequired(true)
              ->addErrorMessage('Title is required.');

        //message
        $message = new Zend_Form_Element_Textarea('message');
        $message->setAttribs(array('class' => 'fancy-input'))
                ->setRequired(true)
                ->addErrorMessage('Message is required.');

        //-----Chosen Multiselect Elements-----

        //professions
        $prof_options = \Fisdap\EntityUtils::getRepository('Profession')->getFormOptions();
        $professions = $this->createChosen('professions', null, "All professions...", $prof_options);

        //product access
        $prod_options = \Fisdap\EntityUtils::getRepository('Product')->getFormOptions();
        $product = $this->createChosen('product', "Product access:", "Any product...", $prod_options);

        //certification level
        $cert_options = \Fisdap\EntityUtils::getRepository('CertificationLevel')->getSortedFormOptions();
        $certlevel = $this->createChosen('certlevel', "Certification levels:", "All certification levels...", $cert_options);

        //permissions (for instructors)
        $perm_options = \Fisdap\EntityUtils::getRepository('Permission')->getFormOptionsGroupedByCategory("bit_value", "name");
        $permissions = $this->createChosen('permissions', "Permissions:", "Any permission...", $perm_options);

        //-----Slider Checkbox Elements-----

        //students
        $students = $this->createSlider('students', "Active students");
        $students->setValue(1);

        //instructors
        $instructors = $this->createSlider('instructors', "Instructors");
        $instructors->setValue(1);

        //preceptor training accounts
        $preceptors = $this->createSlider('preceptors', "Preceptor Training Accounts");


        //add elements to the form
        $this->addElement($title)
             ->addElement($type)
             ->addElement($message)
             ->addElement($professions)
             ->addElement($students)
             ->addElement($certlevel)
             ->addElement($product)
             ->addElement($instructors)
             ->addElement($preceptors)
             ->addElement($permissions);

        //set basic zend form decorators
        $this->setElementDecorators(self::$basicElementDecorators, array('title', 'type', 'message', 'message',
                                                                         'professions', 'students', 'certlevel',
                                                                         'product', 'instructors', 'preceptors',
                                                                         'permissions'));

        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => "forms/createNotificationForm.phtml")),
            'Form',

        ));
    }

    /**
     * Custom function to implement a custom zend multiselect form element.
     *
     * @param $element_name
     * @param $label
     * @param $placeholder_text
     * @param $options
     * @param string $multi
     * @return Fisdap_Form_Element_jQueryMultiselectChosen
     */
    private function createChosen($element_name, $label, $placeholder_text, $options, $multi = "multiple")
    {
        $chosen = new Fisdap_Form_Element_jQueryMultiselectChosen($element_name);
        $chosen->setMultiOptions($options)
               ->setLabel($label)
               ->setAttribs(array("data-placeholder" => $placeholder_text,
                                  "multiple" => $multi,
                                  "tabindex" => count($options)));
        return $chosen;
    }

    /**
     * Custom function to implement a custom zend checkbox form element.
     *
     * @param $element_name
     * @param $label
     * @return Fisdap_Form_Element_jQueryCheckboxSlider
     */
    private function createSlider($element_name, $label)
    {
        $slider = new Fisdap_Form_Element_jQueryCheckboxSlider($element_name);
        $slider->setLabel($label);

        return $slider;
    }

    /**
     * Overwriting the custom form validator to ensure that at least one of the student/instructor/preceptor sliders is checked
     *
     * @param array $post
     *
     * @return bool
     * @throws Zend_Form_Exception
     */
    public function isValid($post)
    {
        $valid = parent::isValid($post);

        $formValues = $this->getValues();
        if (!$formValues['students'] && !$formValues['instructors'] && !$formValues['preceptors']) {
            $this->addError('Please choose at least one recipient.');
            $valid = false;
        }

        return $valid;
    }

    /**
     * Process method to post the form entries to fisdap2_notifications
     *
     * @param array $post
     * @return bool
     * @throws Zend_Form_Exception
     */
    public function process(array $post)
    {
        if ($this->isValid($post)) {
            $values = $this->getValues();

            $notification_type = \Fisdap\EntityUtils::getEntity('NotificationType', $values['type']);

            //insert stuff into DB
            $notification = new \Fisdap\Entity\Notification();
            $notification->title = $values['title'];
            $notification->message = $values['message'];
            $notification->date_posted = new \DateTime();
            $notification->notification_type = $notification_type;
            $notification->recipient_params = [
                'professions' => $values['professions'],
                'cert_levels' => $values['certlevel'],
                'students' => $values['students'],
                'instructors' => $values['instructors'],
                'preceptors' => $values['preceptors'],
                'products' => is_array($values['product']) ? array_sum($values['product']) : 0,
                'permissions' => is_array($values['permissions']) ? array_sum($values['permissions']) : 0,
            ];

            $notification->save();
            \Fisdap\EntityUtils::getRepository('Notification')->sendNotification($notification);

            return true;
        }
        return false;
    }
}
