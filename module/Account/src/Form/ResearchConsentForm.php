<?php
namespace Account\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;
use Account\Validator\ResearchConsentValidator;

/**
 * This form is used to collect instructor's email, full name, password and status. The form
 * can work in two scenarios - 'create' and 'update'. In 'create' scenario, instructor
 * enters password, in 'update' scenario he/she doesn't enter password.
 */
class ResearchConsentForm extends Form
{
    /**
     * Scenario ('create' or 'update').
     * @var string
     */
    private $scenario;

    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager = null;

    /**
     * Current instructor.
     * @var Account\Entity\Instructor
     */
    private $student = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Define form name
        parent::__construct('researchconsent-form');

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements()
    {
        
        // Add "firstName" field
        $this->add([
        'type' => 'Zend\Form\Element\Radio',
        'name' => 'dataUse',
        'options' => array(
            'value_options' => array(
                '0' => 'I do not consent to having my anonymous data used for research purposes.',
                '1' => 'I consent to having my anonymous data used for research purposes.',
            ),
        ),
    ]);

        $this->add([
        'type' => 'Zend\Form\Element\Radio',
        'name' => 'dataRelease',
        'options' => array(
            'value_options' => array(
                '0' => 'I do not consent to having my anonymous data released to other person(s) or college(s) for research purposes only.',
                '1' => 'I consent to having my anonymous data released to other person(s) or college(s) for research purposes only.',
            ),
        ),
    ]);
        

        // Add the Submit button
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Create'
            ],
        ]);
    }

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter()
    {
        // Create main input filter
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        // Add input for "dataUse" field
        $inputFilter->add([
                'name'     => 'dataUse',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 50
                        ],
                    ],
                ],
            ]);

        // Add input for "dataRelease" field
        $inputFilter->add([
                'name'     => 'dataRelease',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 50
                        ],
                    ],
                ],
            ]);

        // Add input for "status" field
        $inputFilter->add([
                'name'     => 'status',
                'required' => true,
                'filters'  => [
                    ['name' => 'ToInt'],
                ],
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]);
    }
}
