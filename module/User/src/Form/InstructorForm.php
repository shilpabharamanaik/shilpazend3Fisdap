<?php
namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;
use User\Validator\InstructorValidator;

/**
 * This form is used to collect instructor's email, full name, password and status. The form
 * can work in two scenarios - 'create' and 'update'. In 'create' scenario, instructor
 * enters password, in 'update' scenario he/she doesn't enter password.
 */
class InstructorForm extends Form
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
    private $instructor = null;

    /**
     * Constructor.
     */
    public function __construct($scenario = 'create', $entityManager = null, $instructor = null)
    {
        // Define form name
        parent::__construct('instructor-form');

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->scenario = $scenario;
        $this->entityManager = $entityManager;
        $this->instructor = $instructor;

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
            'type'  => 'text',
            'name' => 'firstName',
            'attributes' => [
                'id' => 'firstName',
            ],
            'options' => [
                'label' => 'First Name',
                'label_attributes' => array('class' => 'grid_3 required')
            ],
        ]);

        // Add "lastName" field
        $this->add([
            'type'  => 'text',
            'name' => 'lastName',
            'attributes' => [
                'id' => 'lastName',
            ],
            'options' => [
                'label' => 'Last Name',
                'label_attributes' => array('class' => 'grid_3 required')
            ],
        ]);

        // Add "email" field
        $this->add([
            'type'  => 'text',
            'name' => 'email',
            'options' => [
                'label' => 'E-mail',
                'label_attributes' => array('class' => 'grid_3 required')
            ],
        ]);


        // Add "full_name" field
        $this->add([
            'type'  => 'text',
            'name' => 'full_name',
            'options' => [
                'label' => 'Full Name',
            ],
        ]);



        // Add "full_name" field
        $this->add([
            'type'  => 'text',
            'name' => 'full_name',
            'options' => [
                'label' => 'Full Name',
            ],
        ]);

        if ($this->scenario == 'create') {

            // Add "password" field
            $this->add([
                'type'  => 'password',
                'name' => 'password',
                'options' => [
                    'label' => 'Password',
                ],
            ]);

            // Add "confirm_password" field
            $this->add([
                'type'  => 'password',
                'name' => 'confirm_password',
                'options' => [
                    'label' => 'Confirm password',
                ],
            ]);
        }

        // Add "status" field
        $this->add([
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Status',
                'value_options' => [
                    1 => 'Active',
                    2 => 'Retired',
                ]
            ],
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

        // Add input for "firstName" field
        $inputFilter->add([
                'name'     => 'firstName',
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

        // Add input for "lastName" field
        $inputFilter->add([
                'name'     => 'lastName',
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

        // Add input for "email" field
        $inputFilter->add([
                'name'     => 'email',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 128
                        ],
                    ],
                    [
                        'name' => 'EmailAddress',
                        'options' => [
                            'allow' => \Zend\Validator\Hostname::ALLOW_DNS,
                            'useMxCheck'    => false,
                        ],
                    ],
                    [
                        'name' => InstructorValidator::class,
                        'options' => [
                            'entityManager' => $this->entityManager,
                            'instructor' => $this->instructor
                        ],
                    ],
                ],
            ]);



        // Add input for "full_name" field
        $inputFilter->add([
                'name'     => 'full_name',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 512
                        ],
                    ],
                ],
            ]);

        if ($this->scenario == 'create') {

            // Add input for "password" field
            $inputFilter->add([
                    'name'     => 'password',
                    'required' => true,
                    'filters'  => [
                    ],
                    'validators' => [
                        [
                            'name'    => 'StringLength',
                            'options' => [
                                'min' => 6,
                                'max' => 64
                            ],
                        ],
                    ],
                ]);

            // Add input for "confirm_password" field
            $inputFilter->add([
                    'name'     => 'confirm_password',
                    'required' => true,
                    'filters'  => [
                    ],
                    'validators' => [
                        [
                            'name'    => 'Identical',
                            'options' => [
                                'token' => 'password',
                            ],
                        ],
                    ],
                ]);
        }

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
