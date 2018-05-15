<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\ORM\Mapping\Column;

/**
 * Class StudentPermissions
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait StudentPermissions
{
    /**
     * @Column(name="StudentEnterShift", type="integer")
     */
    protected $can_students_create_field = 1;

    /**
     * @Column(name="StuEnterClinicShifts", type="boolean")
     */
    protected $can_students_create_clinical = 1;

    /**
     * @Column(name="StuViewFullCal", type="boolean")
     */
    protected $student_view_full_calendar = 0;

    /**
     * @Column(name="StuPickField", type="boolean")
     */
    protected $can_students_pick_field = 1;

    /**
     * @Column(name="StuPickClinic", type="boolean")
     */
    protected $can_students_pick_clinical = 1;

    /**
     * @Column(name="StuPickLab", type="boolean")
     */
    protected $can_students_pick_lab = 1;

    /**
     * @Column(name="StuEnterLabShifts", type="integer")
     */
    protected $can_students_create_lab = 0;

    /**
     * @Column(name="StudentsSetAbsentWithPermission", type="integer")
     */
    protected $allow_absent_with_permission = 1;
    
    /**
     * @var bool
     * @Column(name="UseNarrative", type="boolean")
     */
    protected $include_narrative = true;
    
    
    /**
     * The DB stores the booleans backwards
     *
     * @param boolean $value the value that is supposed to be set
     */
    public function set_can_students_create_field($value)
    {
        $this->can_students_create_field = !$value;
    }

    /**
     * Get the inverse of what's stored in the database.
     * @return boolean
     */
    public function get_can_students_create_field()
    {
        return !$this->can_students_create_field;
    }

    /**
     * The DB stores the booleans backwards
     *
     * @param boolean $value the value that is supposed to be set
     */
    public function set_can_students_create_clinical($value)
    {
        $this->can_students_create_clinical = !$value;
    }

    /**
     * Get the inverse of what's stored in the database.
     * @return boolean
     */
    public function get_can_students_create_clinical()
    {
        return !$this->can_students_create_clinical;
    }

    /**
     * The DB stores the booleans backwards
     *
     * @param boolean $value the value that is supposed to be set
     */
    public function set_can_students_create_lab($value)
    {
        $this->can_students_create_lab = !$value;
    }

    /**
     * Get the inverse of what's stored in the database.
     * @return boolean
     */
    public function get_can_students_create_lab()
    {
        return !$this->can_students_create_lab;
    }


    /**
     * @return mixed
     */
    public function getStudentViewFullCalendar()
    {
        return $this->student_view_full_calendar;
    }


    /**
     * @param mixed $student_view_full_calendar
     */
    public function setStudentViewFullCalendar($student_view_full_calendar)
    {
        $this->student_view_full_calendar = $student_view_full_calendar;
    }


    /**
     * @return mixed
     */
    public function getCanStudentsPickField()
    {
        return $this->can_students_pick_field;
    }


    /**
     * @param mixed $can_students_pick_field
     */
    public function setCanStudentsPickField($can_students_pick_field)
    {
        $this->can_students_pick_field = $can_students_pick_field;
    }


    /**
     * @return mixed
     */
    public function getCanStudentsPickClinical()
    {
        return $this->can_students_pick_clinical;
    }


    /**
     * @param mixed $can_students_pick_clinical
     */
    public function setCanStudentsPickClinical($can_students_pick_clinical)
    {
        $this->can_students_pick_clinical = $can_students_pick_clinical;
    }


    /**
     * @return mixed
     */
    public function getCanStudentsPickLab()
    {
        return $this->can_students_pick_lab;
    }


    /**
     * @param mixed $can_students_pick_lab
     */
    public function setCanStudentsPickLab($can_students_pick_lab)
    {
        $this->can_students_pick_lab = $can_students_pick_lab;
    }


    /**
     * @return mixed
     */
    public function getAllowAbsentWithPermission()
    {
        return $this->allow_absent_with_permission;
    }


    /**
     * @param mixed $allow_absent_with_permission
     */
    public function setAllowAbsentWithPermission($allow_absent_with_permission)
    {
        $this->allow_absent_with_permission = $allow_absent_with_permission;
    }


    /**
     * @return boolean
     */
    public function isIncludeNarrative()
    {
        return $this->include_narrative;
    }


    /**
     * @param boolean $include_narrative
     */
    public function setIncludeNarrative($include_narrative)
    {
        $this->include_narrative = $include_narrative;
    }
}
