<?php
namespace Account\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;
use Account\Validator\NewInstructorValidator;

/**
 * This form is used to collect instructor's email, full name, password and status. The form
 * can work in two scenarios - 'create' and 'update'. In 'create' scenario, instructor
 * enters password, in 'update' scenario he/she doesn't enter password.
 */
class NewInstructorForm extends Form
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
        parent::__construct('newinstructor-form');

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
        
        $this->add(array(
        'type' => 'Zend\Form\Element\MultiCheckbox',
        'name' => 'Rules',
        'options' => array(
            'label' => '',
            'value_options' => array(
                '0' => 'Program Director',
                '1' => 'Medical Director',
                '2' => 'Clinical Coordinator',
                '3' => 'Instructor',
            ),
        )
        ));
        
        $this->add(array(
        'type' => 'Zend\Form\Element\MultiCheckbox',
        'name' => 'programPermissions',
        'options' => array(
            'label' => '',
            'value_options' => array(
                '0' => 'Order Accounts',
                '1' => 'Edit Student Accounts',
                '2' => 'Admin Exams',
                '3' => 'Edit Instructor Accounts',
                '4' => 'Edit Program Settings',
            ),
        )
        ));
        
        $this->add(array(
        'type' => 'Zend\Form\Element\MultiCheckbox',
        'name' => 'skillsTrackerPermissions',
        'options' => array(
            'label' => '',
            'value_options' => array(
                '0' => 'View All Data',
                '1' => 'Edit Clinical Data',
                '2' => 'Enter Evals',
                '3' => 'Edit Portfolio',
                '4' => 'Edit Field Data',
                '5' => 'Edit Lab Data',
                '6' => 'Edit Evals',
            ),
        )
        ));
        
        $this->add(array(
        'type' => 'Zend\Form\Element\MultiCheckbox',
        'name' => 'schedulePermissions',
        'options' => array(
            'label' => '',
            'value_options' => array(
                '0' => 'View Schedules',
                '1' => 'Edit Clinic Schedules',
                '2' => 'Edit Compliance Status',
                '3' => 'Edit Field Schedules',
                '4' => 'Edit Lab Schedules',
            ),
        )
        ));
    
        $this->add(array(
        'type' => 'Zend\Form\Element\MultiCheckbox',
        'name' => 'reportsPermissions',
        'options' => array(
            'label' => '',
            'value_options' => array(
                '0' => 'View Reports',
            ),
        )
        ));
        $this->add(array(
        'type' => 'Zend\Form\Element\MultiCheckbox',
        'name' => 'emailNewAccount',
        'options' => array(
            'label' => '',
            'value_options' => array(
                '0' => 'Send email to instructor with his/her login information.',
            ),
        )
        ));
        
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
        $this->add(array(
        'type' => 'Zend\Form\Element\MultiCheckbox',
        'name' => 'automatedEmails',
        'options' => array(
            'label' => '',
            'value_options' => array(
                '0' => 'Student Events',
            ),
        )
        ));
        $this->add(array(
        'type' => 'Zend\Form\Element\MultiCheckbox',
        'name' => 'labEmails',
        'options' => array(
            'label' => '',
            'value_options' => array(
                '0' => ' Lab Shifts',
            ),
        )
        ));

        $this->add(array(
        'type' => 'Zend\Form\Element\MultiCheckbox',
        'name' => 'clinicalEmails',
        'options' => array(
            'label' => '',
            'value_options' => array(
                '0' => ' Clinical Shifts',
            ),
        )
        ));
        $this->add(array(
        'type' => 'Zend\Form\Element\MultiCheckbox',
        'name' => 'fieldEmails',
        'options' => array(
            'label' => '',
            'value_options' => array(
                '0' => 'Field Shifts',
            ),
        )
        ));
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
                        'name' => NewInstructorValidator::class,
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
