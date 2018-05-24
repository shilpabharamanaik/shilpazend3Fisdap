<?php
namespace Account\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;
use Account\Validator\StudentValidator;

/**
 * This form is used to collect instructor's email, full name, password and status. The form
 * can work in two scenarios - 'create' and 'update'. In 'create' scenario, instructor
 * enters password, in 'update' scenario he/she doesn't enter password.
 */
class StudentForm extends Form
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
    public function __construct($scenario = 'create', $entityManager = null, $student = null)
    {
        // Define form name
        parent::__construct('student-form');

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->scenario = $scenario;
        $this->entityManager = $entityManager;
        $this->student = $student;

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

		 // Add "HomePhone" field
        $this->add([
            'type'  => 'text',
            'name' => 'homePhone',
            'attributes' => [
                'id' => 'homePhone',
            ],
            'options' => [
                'label' => 'Home',
                'label_attributes' => array('class' => 'grid_3 required')
            ],
        ]);
		 // Add "WorkPhone" field
        $this->add([
            'type'  => 'text',
            'name' => 'workPhone',
            'attributes' => [
                'id' => 'workPhone',
            ],
            'options' => [
                'label' => 'Work',
                'label_attributes' => array('class' => 'grid_3 required')
            ],
        ]);
		 // Add "CellPhone" field
        $this->add([
            'type'  => 'text',
            'name' => 'cellPhone',
            'attributes' => [
                'id' => 'cellPhone',
            ],
            'options' => [
                'label' => 'Cell',
                'label_attributes' => array('class' => 'grid_3 required')
            ],
        ]);
		
		
		 // Add "address" field
        $this->add([
            'type'  => 'text',
            'name' => 'address',
            'attributes' => [
                'id' => 'address',
            ],
            'options' => [
                'label' => 'Street',
                'label_attributes' => array('class' => 'grid_3 required')
            ],
        ]);
		
		// Add "city" field
        $this->add([
            'type'  => 'text',
            'name' => 'city',
            'attributes' => [
                'id' => 'city',
            ],
            'options' => [
                'label' => 'City',
                'label_attributes' => array('class' => 'grid_3 required')
            ],
        ]);
		
		// Add "country" field
        $this->add([
            'type'  => 'text',
            'name' => 'country',
            'attributes' => [
                'id' => 'country',
            ],
            'options' => [
                'label' => 'Country',
                'label_attributes' => array('class' => 'grid_3 required')
            ],
        ]);
		
		// Add "zip" field
        $this->add([
            'type'  => 'text',
            'name' => 'zip',
            'attributes' => [
                'id' => 'zip',
            ],
            'options' => [
                'label' => 'Zip',
                'label_attributes' => array('class' => 'grid_3 required')
            ],
        ]);


	// Add "contact Name" field
        $this->add([
            'type'  => 'text',
            'name' => 'contact_name',
            'attributes' => [
                'id' => 'contact_name',
            ],
            'options' => [
                'label' => 'Name',
                'label_attributes' => array('class' => 'grid_3 required')
            ],
        ]);
		
		// Add "contact Name" field
        $this->add([
            'type'  => 'text',
            'name' => 'contact_phone',
            'attributes' => [
                'id' => 'contact_phone',
            ],
            'options' => [
                'label' => 'Phone',
                'label_attributes' => array('class' => 'grid_3 required')
            ],
        ]);
		// Add "contact Relation" field
        $this->add([
            'type'  => 'text',
            'name' => 'contact_relation',
            'attributes' => [
                'id' => 'contact_relation',
            ],
            'options' => [
                'label' => 'Relation',
                'label_attributes' => array('class' => 'grid_3 required')
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
                        'name' => StudentValidator::class,
                        'options' => [
                            'entityManager' => $this->entityManager,
                            'student' => $this->student
                        ],
                    ],
                ],
            ]);

			// Add input for "homePhone" field
        $inputFilter->add([
                'name'     => 'homePhone',
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
	// Add input for "workPhone" field
        $inputFilter->add([
                'name'     => 'workPhone',
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
			// Add input for "cellPhone" field
        $inputFilter->add([
                'name'     => 'cellPhone',
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
			
				// Add input for "address" field
        $inputFilter->add([
                'name'     => 'address',
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
				// Add input for "city" field
        $inputFilter->add([
                'name'     => 'city',
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
				// Add input for "country" field
        $inputFilter->add([
                'name'     => 'country',
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
				// Add input for "zip" field
        $inputFilter->add([
                'name'     => 'zip',
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
			
			// Add input for "contact_name" field
        $inputFilter->add([
                'name'     => 'contact_name',
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
			// Add input for "contact_phone" field
        $inputFilter->add([
                'name'     => 'contact_phone',
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
			// Add input for "contact_relation" field
        $inputFilter->add([
                'name'     => 'contact_relation',
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
