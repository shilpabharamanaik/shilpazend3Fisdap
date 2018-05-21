<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Entity class for automated scheduler emails
 *
 * @Entity(repositoryClass="Fisdap\Data\ScheduleEmail\DoctrineScheduleEmailRepository")
 * @Table(name="AutomatedScheduleEmailData")
 */
class ScheduleEmail extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(name="Email_id", type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var string
     * @Column(name="ScheduleTitle", type="string", nullable=true)
     */
    protected $title = "Intern Schedule";

    /**
     * @var ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="Program_id", referencedColumnName="Program_id")
     */
    protected $program;

    /**
     * @var InstructorLegacy
     * @ManyToOne(targetEntity="InstructorLegacy")
     * @JoinColumn(name="Instructor_id", referencedColumnName="Instructor_id")
     */
    protected $instructor;

    /**
     * @var SchedulerFilterSet
     * @OneToOne(targetEntity="SchedulerFilterSet", cascade={"persist","remove"})
     * @JoinColumn(name="filter_id")
     */
    protected $filter;

    /**
     * @var string
     * @Column(name="EmailList", type="string", nullable=false)
     */
    protected $email_list;

    /**
     * @var string
     * @Column(name="Duration", type="string", nullable=false)
     */
    protected $recurring_type;

    /**
     * @var integer
     * @Column(name="OffsetNumber", type="integer", nullable=false)
     */
    protected $offset_number;

    /**
     * @var string
     * @Column(name="OffsetType", type="string", nullable=false)
     */
    protected $offset_type;

    /**
     * @var string
     * @Column(name="EmailSubject", type="string", nullable=true)
     */
    protected $email_subject;

    /**
     * @var string
     * @Column(name="Email_note", type="string", nullable=true)
     */
    protected $email_note;

    /**
     * @var string
     * @Column(name="Orientation", type="string", nullable=true)
     */
    protected $orientation;

    /**
     * @var boolean
     * @Column(name="DisplayLegend", type="boolean", nullable=true)
     */
    protected $legend;

    /**
     * @var boolean
     * @Column(name="Color_switch", type="boolean", nullable=true)
     */
    protected $color;

    /**
     * @var boolean
     * @Column(name="Active", type="boolean", nullable=false)
     */
    protected $active = true;

    /** DEPRECATED FIELDS */
    /**
     * @codeCoverageIgnore
     * @deprecated
     * @var integer
     * @Column(name="AmbServ_id", type="integer", nullable=true)
     */
    protected $site_id;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * $var integer
     * @Column(name="Base_id", type="integer", nullable=true)
     */
    protected $base_id;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @var integer
     * @Column(name="Preceptor_id", type="integer", nullable=true)
     */
    protected $preceptor_id;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @var string
     * @Column(name="ViewType", type="string", nullable=true)
     */
    protected $legacy_view_type;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @var boolean
     * @Column(name="ChosenShifts", type="boolean", nullable=true)
     */
    protected $legacy_chosen_shifts;

    /**
     * @codeCoverageIgnore
     * @var boolean
     * @deprecated
     * @Column(name="AvailableShifts", type="boolean", nullable=true)
     */
    protected $legacy_available_shifts;


    /**
     * Given a filter id, retrieve the entity
     *
     * @param SchedulerFilterSet $filter
     *
     * @return ScheduleEmail or NULL if the given email doesn't exist
     */
    public static function getByFilter($filter)
    {
        $schedule_email = EntityUtils::getRepository("ScheduleEmail")->findOneByFilter($filter);

        if ($schedule_email->id) {
            return $schedule_email;
        }

        return null;
    }


    public function updateDependentIDsFromFilters()
    {
        $filter_set = $this->filter;
        $current_filters = $filter_set->filters;
        $new_filters = $current_filters;
        $made_changes = false;

        $bases_selected_by_user = (isset($current_filters['bases_selected_by_user']))
            ? $current_filters['bases_selected_by_user'] : true;
        $students_selected_by_user = (isset($current_filters['students_selected_by_user']))
            ? $current_filters['students_selected_by_user'] : true;


        // if site does not equal all
        // recalculate bases
        if ($current_filters['sites'] != 'all' && !$bases_selected_by_user) {

            // get all active base IDs for this program/site
            $site_ids = array();

            $base_repo = EntityUtils::getRepository('BaseLegacy');
            $site_repo = EntityUtils::getRepository('SiteLegacy');
            $this_program_id = $this->instructor->user_context->program->id;

            $all_site_type = "";
            $get_site_ids = false;

            if ($current_filters['sites'][0] == "0-Clinical") {
                $all_site_type = "clinical";
            } else {
                if ($current_filters['sites'][0] == "0-Field") {
                    $all_site_type = "field";
                } else {
                    if ($current_filters['sites'][0] == "0-Lab") {
                        $all_site_type = "lab";
                    } else {
                        // use the given site ids
                        $get_site_ids = true;
                        $site_ids = $current_filters['sites'];
                    }
                }
            }


            if ($all_site_type) {
                $raw_sites = $site_repo->getFormOptionsByProgram($this_program_id, $all_site_type, null, null, true);

                // we have at least 1 active one, if we don't let's not bother updating the depending ids
                // (this will cause issues later on and we want these emails to act the same for the user)
                if (count($raw_sites) > 0) {
                    $get_site_ids = true;
                    foreach ($raw_sites as $site_id => $site_name) {
                        $site_ids[] = $site_id;
                    }
                } else {
                    // get inactive sites
                    $raw_sites = $site_repo->getFormOptionsByProgram(
                        $this_program_id,
                        $all_site_type,
                        null,
                        null,
                        false
                    );
                    foreach ($raw_sites as $site_id => $site_name) {
                        $site_ids[] = $site_id;
                    }
                }
            }

            if ($get_site_ids) {
                $new_bases = $base_repo->getBaseIdsByProgramAndSites($site_ids, $this_program_id);

                // we have at least 1 active one, if we don't let's not bother updating the depending ids
                // (this will cause issues later on and we want these emails to act the same for the user)
                if (count($new_bases) == 0) {
                    // include inactive bases
                    $new_bases = $base_repo->getBaseIdsByProgramAndSites($site_ids, $this_program_id, false);
                }

                $new_filters['bases'] = $new_bases;
                $made_changes = true;
            }
        }

        // if certs, groups, gradMonth, gradYear do not equal default
        // recalculate chosen_students
        $update_students = ($current_filters['gradYear'] != 'All years' || $current_filters['gradMonth'] != 'All months'
            || $current_filters['certs'] != ''
            || $current_filters['certs'] != 'all'
            || $current_filters['groups'] != 'all');

        //Recalculate students if we need to update and we're not currently set to all
        if ($update_students && !$students_selected_by_user && $current_filters['chosen_students'] != 'all') {
            // get all active students matching criteria
            $grad_year = ($current_filters['gradYear'] == 'All years') ? null : $current_filters['gradYear'];
            $grad_month = ($current_filters['gradMonth'] == 'All months') ? null : $current_filters['gradMonth'];
            $cert_levels = ($current_filters['certs'] == '' || $current_filters['certs'] == 'all') ? null
                : $current_filters['certs'];
            $groups = ($current_filters['groups'] == 'all') ? null : $current_filters['groups'];

            $students = EntityUtils::getRepository('ProgramLegacy')->getActiveStudentsByProgramOptimized(
                $this->instructor->user_context->program->id,
                $cert_levels,
                $grad_year,
                $grad_month,
                $groups
            );

            foreach ($students as $student) {
                $new_students[] = $student['user_context']['id'];
            }

            $new_filters['chosen_students'] = $new_students;
            $made_changes = true;
        }

        // save the new filter
        if ($made_changes) {
            $this->filter->filters = $new_filters;
            $this->filter->save();
        }
    }

    public function set_site($value)
    {
        $this->site = self::id_or_entity_helper($value, 'SiteLegacy');
    }


    /**
     * determines whether or not this email ought to be sent today
     *
     * @param \DateTime|null $now
     *
     * @return bool
     */
    public function sendToday(\DateTime $now = null)
    {
        $today = $now ?: new \DateTime();

        $send = false;

        $calendar_start = $this->getStartDate($today);

        // monthly emails should start on the first of the month
        if ($this->recurring_type == 'month') {
            if (substr($calendar_start, 8) == "01") {
                $send = true;
            }
        } else {
            if ($this->recurring_type == 'week') {
                // weekly emails should start on a Sunday
                $calendar_start_date = new \DateTime($calendar_start);
                if ($calendar_start_date->format('l') == 'Sunday') {
                    $send = true;
                }
            }
        }

        return $send;
    }


    /**
     * determines the start date for the calendar, assuming it's being sent today
     *
     * @param \DateTime|null $now
     *
     * @return string today's date plus the offset
     */
    public function getStartDate(\DateTime $now = null)
    {
        // copy incoming DateTime object so that modifications are unique to this call
        $today = isset($now) ? clone $now : new \DateTime();
        $offset_number = $this->offset_number;
        $offset_type = $this->offset_type;

        // this is a hack to deal with months being weird;
        // emails will be sent approximately n month(s) in advance
        if ($this->offset_type == 'month') {
            $offset_number = $this->offset_number * 30;
            $offset_type = "day";
        }

        return $today->modify('+' . $offset_number . ' ' . $offset_type)->format('Y-m-d');
    }


    /**
     * determines the end date for the calendar, assuming it's being sent today
     *
     * @param string $start_date
     *
     * @return string
     */
    public function getEndDate($start_date)
    {
        $end_date = new \DateTime($start_date);

        return $end_date->modify('+1 ' . $this->recurring_type)->modify('-1 day')->format('Y-m-d');
    }
}
