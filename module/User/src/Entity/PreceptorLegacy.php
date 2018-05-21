<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Legacy Entity class for Preceptors.
 *
 * @Entity(repositoryClass="Fisdap\Data\Preceptor\DoctrinePreceptorLegacyRepository")
 * @Table(name="PreceptorData")
 */
class PreceptorLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="Preceptor_id", type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(name="FirstName", type="string")
     */
    protected $first_name;

    /**
     * @Column(name="LastName", type="string")
     */
    protected $last_name;

    /**
     * @Column(name="Title", type="string", nullable=true)
     */
    protected $title;

    /**
     * @ManyToOne(targetEntity="SiteLegacy")
     * @JoinColumn(name="AmbServ_id", referencedColumnName="AmbServ_id")
     */
    protected $site;

    /**
     * @Column(name="DateSelected", type="string", nullable=true)
     * @codeCoverageIgnore
     * @deprecated
     */
    protected $date_selected;

    /**
     * @Column(name="MainBase", type="string", nullable=true)
     */
    protected $main_base;

    /**
     * @Column(name="HPhone", type="string", nullable=true)
     */
    protected $home_phone;

    /**
     * @Column(name="WPhone", type="string", nullable=true)
     */
    protected $work_phone;

    /**
     * @Column(name="Pager", type="string", nullable=true)
     */
    protected $pager;

    /**
     * @Column(name="EmailAddress", type="string", nullable=true)
     */
    protected $email;

    /**
     * @Column(name="Status", type="string", type="string", nullable=true)
     */
    protected $status;

    /**
     * @Column(name="AdvisorRNum", type="string", nullable=true)
     * @codeCoverageIgnore
     * @deprecated
     */
    protected $advisor_rnum;

    /**
     * @ManyToOne(targetEntity="StudentLegacy")
     * @JoinColumn(name="Student_id", referencedColumnName="Student_id")
     */
    protected $student;

    /**
     * @Column(name="Active", type="boolean")
     */
    protected $active = true;

    /**
     * @Column(name="PDAPreceptor_id", type="integer", nullable=true)
     * @deprecated
     */
    protected $pda_preceptor_id = 0;

    /**
     * @OneToMany(targetEntity="ProgramPreceptorLegacy", mappedBy="preceptor")
     * @JoinColumn(name="Preceptor_id", referencedColumnName="Preceptor_id")
     */
    protected $program_preceptor_associations;

    public function init()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $value
     * @codeCoverageIgnore
     * @deprecated - use setStudent($value)
     */
    public function set_student($value)
    {
        $this->student = self::id_or_entity_helper($value, "StudentLegacy");
    }

    /**
     * @param $student
     */
    public function setStudent(StudentLegacy $student)
    {
        $this->student = $student;
    }

    public function getStudent()
    {
        return $this->student;
    }

    /**
     * @param $value
     * @codeCoverageIgnore
     * @deprecated - use setSite($value)
     */
    public function set_site($value)
    {
        $this->site = self::id_or_entity_helper($value, "SiteLegacy");
    }

    /**
     * @param $site
     */
    public function setSite(SiteLegacy $site)
    {
        $this->site = $site;
    }

    public function getSite()
    {
        return $this->site;
    }

    public function setFirstName($name)
    {
        $this->first_name = $name;
    }

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function setLastName($name)
    {
        $this->last_name = $name;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function setTitle($title)
    {
        $this->title = $title ? $title : null;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setDateSelected($date)
    {
        $this->date_selected = $date ? $date : null;
    }

    public function getDateSelected()
    {
        return $this->date_selected;
    }

    public function setMainBase($base)
    {
        $this->main_base = $base ? $base : null;
    }

    public function getMainBase()
    {
        return $this->main_base;
    }

    public function setHomePhone($phone)
    {
        $this->home_phone = self::formatNumber($phone);
    }

    public function getHomePhone()
    {
        return $this->home_phone;
    }

    public function setWorkPhone($phone)
    {
        $this->work_phone = self::formatNumber($phone);
    }

    public function getWorkPhone()
    {
        return $this->work_phone;
    }

    public function setPager($phone)
    {
        $this->pager = self::formatNumber($phone);
    }

    public function getPager()
    {
        return $this->pager;
    }

    public function setEmail($email)
    {
        $this->email = $email ? $email : null;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setStatus($status)
    {
        $this->status = $status ? $status : null;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setActive($active = true)
    {
        $this->active = is_bool($active) ? $active : true;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function getAssociationByProgram($program_id)
    {
        if ($this->program_preceptor_associations) {
            foreach ($this->program_preceptor_associations as $assoc) {
                if ($assoc->program->id == $program_id) {
                    return $assoc;
                }
            }
        }

        return false;
    }

    public static function getPreceptorFormOptions($programId, $siteId, $active = true)
    {
        $values = array();

        $preceptors = EntityUtils::getRepository('PreceptorLegacy')->getPreceptors($programId, $siteId, $active);
        foreach ($preceptors as $preceptor) {
            $values[$preceptor->id] = $preceptor->first_name . " " . $preceptor->last_name;
        }

        return $values;
    }

    public static function getPatientsByPreceptor($preceptorId)
    {
        $students = EntityUtils::getRepository('PreceptorLegacy')->getPreceptorPatients($preceptorId);

        return $students;
    }

    /**
     * returns a select box with the sorted list of preceptors
     *
     * @param Run $run
     * @return array - the select with a sorted list of preceptors
     */
    public static function getSortedPreceptorSelect($run)
    {
        $selectOptions = array("-1" => "Choose a preceptor...");
        foreach (self::getPreceptorFormOptions(
            $run->student->program->id,
            $run->shift->site->id
        ) as $preceptorId => $preceptorName) {
            $selectOptions[$preceptorId] = $preceptorName;
        }
        return $selectOptions;
    }

    public static function setEventPreceptors($new, $old)
    {
        EntityUtils::getRepository('PreceptorLegacy')->mergeEventPreceptors($new, $old);
    }

    /**
     * @param $number
     *
     * Phone number provided by preceptor modal
     * @return string formatted phone number
     */
    public static function formatNumber($number)
    {
        if ($number === null) {
            return null;
        }

        $format = false;
        $original_number = $number;
        $number = preg_replace('[\D]', '', $number);

        if ($number) {
            if (is_numeric($number)) {
                if (strlen($number) >= 10) {
                    $format = true;
                }
            }
        }

        if ($format) {
            $formatted_number = "(";
            $formatted_number .= substr($number, 0, 3);
            $formatted_number .= ") ";
            $formatted_number .= substr($number, 3, 3);
            $formatted_number .= "-";
            $formatted_number .= substr($number, 6, 4);

            if (strlen($number) > 10) {
                $formatted_number .= " x";
                $formatted_number .= substr($number, 10, strlen($number));
            }
        } else {
            $formatted_number = $original_number;
        }

        return $formatted_number;
    }
}
