<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;

/**
 * Entity Class for Scheduler Filter Sets
 *
 * @Entity
 * @Table(name="fisdap2_scheduler_filter_sets")
 */
class SchedulerFilterSet extends EntityBaseClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @var UserContext
     * @ManyToOne(targetEntity="UserContext")
     * @JoinColumn(name="user_role_id", referencedColumnName="id")
     */
    protected $user_context;

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $active = true;

    /**
     * @var SchedulerViewType
     * @ManyToOne(targetEntity="SchedulerViewType")
     */
    protected $view_type;

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $preset = false;

    /**
     * @var string
     * @Column(type="array", nullable=true)
     */
    protected $filters;

    /**
     * @var boolean
     * @Column(type="boolean", nullable=true)
     */
    protected $show_student_names;

    /**
     * @var boolean
     * @Column(type="boolean", nullable=true)
     */
    protected $show_instructor_names;

    /**
     * @var boolean
     * @Column(type="boolean", nullable=true)
     */
    protected $show_preceptor_names;

    /**
     * @var boolean
     * @Column(type="boolean", nullable=true)
     */
    protected $show_weebles;

    /**
     * @var boolean
     * @Column(type="boolean", nullable=true)
     */
    protected $show_totals;

    /**
     * @var boolean
     * @Column(type="boolean", nullable=true)
     */
    protected $show_site_names;

    /**
     * @var boolean
     * @Column(type="boolean", nullable=true)
     */
    protected $show_base_names;


    public function setViewTypeByName($name)
    {
        $this->view_type = EntityUtils::getRepository("SchedulerViewType")->findOneByName($name);
        return $this;
    }

    public function set_user_context($value)
    {
        $this->user_context = self::id_or_entity_helper($value, "UserContext");
        return $this;
    }

    public function set_view_type($value)
    {
        $this->view_type = self::id_or_entity_helper($value, "SchedulerViewType");
        return $this;
    }

    public function getDisplayOptionsArray()
    {
        return array(
            "student_names" => array("display" => "Student names", "value" => ($this->show_student_names !== false) ? true : false),
            "instructor_names" => array("display" => "Instructor names", "value" => ($this->show_instructor_names !== false) ? true : false),
            "preceptor_names" => array("display" => "Preceptor names", "value" => ($this->show_preceptor_names !== false) ? true : false),
            "site_names" => array("display" => "Site names", "value" => ($this->show_site_names !== false) ? true : false),
            "base_names" => array("display" => "Base names", "value" => ($this->show_base_names !== false) ? true : false),
            "weebles" => array("display" => "Student icons", "value" => ($this->show_weebles !== false) ? true : false),
            "totals" => array("display" => "Shift totals", "value" => ($this->show_totals !== false) ? true : false),
        );
    }

    public function getDisplayOptionDescription()
    {
        $description = "";
        $options = $this->getDisplayOptionsArray();


        // first start with 'names'
        $names = "";
        if ($options['student_names']['value'] && $options['instructor_names']['value'] && $options['preceptor_names']['value']) {
            $names = "All names turned on";
        } elseif (!$options['student_names']['value'] && !$options['instructor_names']['value'] && !$options['preceptor_names']['value']) {
            $names = "All names turned off";
        } else {
            $names .= "Student names turned " . $this->getOnOffText($options['student_names']['value']) . "; ";
            $names .= "Instructor names turned " . $this->getOnOffText($options['instructor_names']['value']) . "; ";
            $names .= "Preceptor names turned " . $this->getOnOffText($options['preceptor_names']['value']);
        }

        // now deal with location
        $locations = "";
        if ($options['site_names']['value'] && $options['base_names']['value']) {
            $locations = "All location info turned on";
        } elseif (!$options['site_names']['value'] && !$options['base_names']['value']) {
            $locations = "All location info turned off";
        } else {
            $locations .= "Site names turned " . $this->getOnOffText($options['site_names']['value']) . "; ";
            $locations .= "Base names turned " . $this->getOnOffText($options['base_names']['value']);
        }

        // now deal with weebles
        $weebles = "Student icons turned " . $this->getOnOffText($options['weebles']['value']);

        // now deal with shift totals
        $totals = "Shift totals turned " . $this->getOnOffText($options['totals']['value']);

        $description = $names . "; " . $locations . "; " . $weebles . "; " . $totals . ".";

        return $description;
    }

    public function getOnOffText($on)
    {
        return ($on) ? "on" : "off";
    }

    // returns all the relevant info about the filter
    public function getInfoArray()
    {
        $filters = $this->filters;

        // get the program this filter is used for
        if ($this->user_context) {
            $program = $this->user_context->user->program;
        } else {
            $schedule_email = ScheduleEmail::getByFilter($this);
            $program = $schedule_email->program;
        }
        $program_id = $program->id;

        return SchedulerFilterSet::getInfoFromFilters($filters, $program_id);
    }

    /**
     * Get a nested array representing the filters in plain English rather than entity ids
     *
     * @param array $filters
     * @param $program_id
     * @return array
     */
    public static function getInfoFromFilters(array $filters, $program_id)
    {
        $info = array();

        // parse sites
        if ($filters['sites'] == 'all') {
            $sites = EntityUtils::getRepository("SiteLegacy")->getFormOptionsByProgram($program_id, null, null, null, true);
            $site_category = "All active sites";
            $type = null;
        } elseif (!is_numeric($filters['sites'][0])) {
            $type = lcfirst(substr($filters['sites'][0], 2));
            $sites[ucfirst($type)] = EntityUtils::getRepository("SiteLegacy")->getFormOptionsByProgram($program_id, $type, null, null, true);
            $site_category = "All active $type sites";
        } else {
            $sites = EntityUtils::getRepository("SiteLegacy")->getFormOptionsByIds($filters['sites']);
            $numSelectedSites = \Util_Array::countNested($sites);
            $site_category = $numSelectedSites . ' '. \Util_String::pluralize('site', $numSelectedSites);
            $type = null;
        }
        $info['site_category'] = $site_category;
        $info['sites'] = $sites;

        // parse bases
        if ($filters['bases'] == 'all') {
            // bases are ONLY ever 'all' if sites are 'all', too, so grab ALL active sites
            $bases = EntityUtils::getRepository("BaseLegacy")->getFormOptionsByProgram($program_id, true, $type, null);
            $base_category = 'All active bases';
        } else {
            if ($filters['bases']) {
                $bases = EntityUtils::getRepository("BaseLegacy")->getFormOptionsByIds($filters['bases']);
                $numPossibleBases = EntityUtils::getRepository("BaseLegacy")->getBaseCount($filters['sites'], $program_id);
            }

            $numSelectedBases = \Util_Array::countNested($bases);
            if ($numSelectedBases == $numPossibleBases) {
                $base_category = "All bases from selected sites";
            } else {
                $base_category = $numSelectedBases . ' '. \Util_String::pluralize('base', $numSelectedBases);
            }
        }

        $info['base_category'] = $base_category;
        $info['bases'] = $bases;

        // parse preceptors
        if ($filters['preceptors'] == 'all') {
            foreach ($sites as $types) {
                foreach ($types as $site_id => $site_name) {
                    $preceptors[$site_name] = EntityUtils::getRepository("PreceptorLegacy")->getPreceptorFormOptions($program_id, $site_id);
                }
            }
            $preceptor_category = 'Active preceptors from selected sites';
        } else {
            $preceptors = EntityUtils::getRepository("PreceptorLegacy")->getFormOptionsByIds($filters['preceptors']);
            $numSelectedPreceptors = \Util_Array::countNested($preceptors);
            $preceptor_category = $numSelectedPreceptors . ' '. \Util_String::pluralize('preceptor', $numSelectedPreceptors);
        }
        $info['preceptor_category'] = $preceptor_category;
        $info['preceptors'] = $preceptors;

        // parse availability
        if ($filters['show_avail'] == 1) {
            $info['available'] = true;

            // cert levels
            if ($filters['avail_certs'] == 'all') {
                $all_available_certs = true;
                $available_certs = "All certification levels";
            } else {
                $all_available_certs = false;
                $available_certs = SchedulerFilterSet::getLevelInfo($filters['avail_certs']);
            }
            $info['all_available_certs'] = $all_available_certs;
            $info['available_certs'] = $available_certs;

            // student groups
            if ($filters['avail_groups'] == 'all') {
                $available_groups_category = 'All student groups';
                $available_groups = EntityUtils::getRepository('ClassSectionLegacy')->getFormOptions($program_id, true);
            } else {
                $available_groups = SchedulerFilterSet::getGroupInfo($filters['avail_groups']);
                $available_groups_category = count($available_groups) . ' '. \Util_String::pluralize('student group', count($available_groups));
            }
            $info['available_groups_category'] = $available_groups_category;
            $info['available_groups'] = $available_groups;

            $info['hide_invisible'] = $filters['avail_open_window'];
        } else {
            $info['available'] = false;
        }

        // parse chosen
        if ($filters['show_chosen'] == 1) {
            $info['chosen'] = true;

            // cert levels
            if ($filters['certs'] == 'all' || !$filters['certs']) {
                $all_chosen_certs = true;
                $chosen_certs = "All certification levels";
            } else {
                $all_chosen_certs = false;
                $chosen_certs = SchedulerFilterSet::getLevelInfo($filters['certs']);
            }
            $info['all_chosen_certs'] = $all_chosen_certs;
            $info['chosen_certs'] = $chosen_certs;

            // student groups
            if ($filters['groups'] == 'all') {
                $chosen_groups_category = 'All student groups';
                $chosen_groups = EntityUtils::getRepository('ClassSectionLegacy')->getFormOptions($program_id, true);
            } else {
                $chosen_groups = SchedulerFilterSet::getGroupInfo($filters['groups']);
                $chosen_groups_category = count($chosen_groups) . ' '. \Util_String::pluralize('student group', count($chosen_groups));
            }
            $info['chosen_groups_category'] = $chosen_groups_category;
            $info['chosen_groups'] = $chosen_groups;

            // grad date
            $info['chosen_grad_date'] = SchedulerFilterSet::getGradDate($filters['gradYear'], $filters['gradMonth']);

            // chosen students
            if ($filters['chosen_students'] == 'all' || is_null($filters['chosen_students'])) {
                $all_chosen_students = true;
                $chosen_students = 'All students';
            } else {
                $all_chosen_students = false;
                $chosen_students = array();
                $names = EntityUtils::getRepository('User')->getNamesByContextIds($filters['chosen_students']);
                foreach ($names as $name) {
                    $chosen_students[] = $name['first_name']." ".$name['last_name'];
                }
            }
            $info['all_chosen_students'] = $all_chosen_students;
            $info['chosen_students'] = $chosen_students;
        } else {
            $info['chosen'] = false;
        }

        return $info;
    }

    public static function getGroupInfo($groups)
    {
        $info = array();
        if (is_array($groups)) {
            foreach ($groups as $group_id) {
                $group = EntityUtils::getEntity('ClassSectionLegacy', $group_id);
                $info[$group_id] = $group->name;
            }
        }
        return $info;
    }

    public static function getLevelInfo($levels)
    {
        $info = array();
        foreach ($levels as $level_id) {
            $level = EntityUtils::getEntity('CertificationLevel', $level_id);
            $info[$level_id] = $level->description."s";
        }
        return $info;
    }

    public static function getGradDate($gradYear, $gradMonth)
    {
        if ($gradYear == 'All years' && $gradMonth == 'All months') {
            return "All graduation dates";
        }
        require_once('FisdapDate.inc');
        // If $gradMonth == 0 here, FisdapDate throws an assertion failed error because there's no 0th month that it knows about.
        if ($gradMonth != 'All months' && $gradMonth > 0) {
            $month = \FisdapDate::get_long_month_name($gradMonth);
            if ($gradYear != 'All years') {
                return "Graduating in " . $month . " " . $gradYear;
            } else {
                return "Graduating in " . $month;
            }
        } else {
            return "Graduating in " . $gradYear;
        }
    }

    public static function getDefaultFilterSet($userContextId = null)
    {
        $user = User::getLoggedInUser();

        if (isset($userContextId)) {
            return array("sites" => "all",
                         "bases" => "all",
                         "preceptors" => "all",
                         "show_avail" => 0,
                         "avail_certs" => "all",
                         "avail_groups" => "all",
                         "avail_open_window" => true,
                         "show_chosen" => 1,
                         "chosen_students" => array($userContextId));
        }

        if ($user->getCurrentRoleName() == 'instructor') {
            $filters = array("sites" => "all",
                             "bases" => "all",
                             "preceptors" => "all",
                             "show_avail" => 1,
                             "avail_certs" => "all",
                             "avail_groups" => "all",
                             "avail_open_window" => false,
                             "show_chosen" => 1,
                             "chosen_students" => "all");
        } else {
            $student_group_repo = EntityUtils::getRepository('ClassSectionLegacy');
            $userGroups = $student_group_repo->getProgramGroups($user->getProgramId(), null, $user->getCurrentRoleData()->id, true, true);
            $filters = array("sites" => "all",
                             "bases" => "all",
                             "preceptors" => "all",
                             "show_avail" => 1,
                             "avail_certs" => array($user->getCurrentUserContext()->certification_level->id),
                             "avail_groups" => $userGroups,
                             "avail_open_window" => true,
                             "show_chosen" => 1,
                             "chosen_students" => array($user->getCurrentUserContext()->id));
        }

        return $filters;
    }

    /**
     * Get all user role ids from a given program given a set of filters
     *
     * PLEASE NOTE: This function should only be used when there are students to be filtered and will throw
     *
     * @param       $program_id
     * @param array $filters
     *
     * @throws \Exception when given a filter set that is not being filtered by student
     * @return array
     */
    public static function getStudentUserContextIdsFromFilters($program_id, array $filters)
    {
        $userContextIds = [];

        //Don't use this function if there are no students to filter
        if ($filters['chosen_students'] == 'all') {
            throw new \Exception("There are no students to filter to.");
        }

        //This case is easy, if they've explicitly chosen a set of students, just filter to them
        if ($filters['students_selected_by_user'] == 1) {
            $userContextIds = $filters['chosen_students'];
        } else {
            //This is the hard part, we need to get students based on cert level, group and grad date
            $grad_year = ($filters['gradYear'] == 'All years') ? null : $filters['gradYear'];
            $grad_month = ($filters['gradMonth'] == 'All months') ? null : $filters['gradMonth'];
            $cert_levels = ($filters['certs'] == '' || $filters['certs'] == 'all') ? null : $filters['certs'];
            $groups = ($filters['groups'] == 'all') ? null : $filters['groups'];

            $students = EntityUtils::getRepository('ProgramLegacy')->getActiveStudentsByProgramOptimized($program_id, $cert_levels, $grad_year, $grad_month, $groups);

            foreach ($students as $student) {
                $userContextIds[] = $student['user_context']['id'];
            }
        }

        return $userContextIds;
    }

    /**
     * Get an array of site ids given a program and array of filters
     *
     * @param       $program_id
     * @param array $filters
     *
     * @return array
     */
    public static function getSiteIdsFromFilters($program_id, array $filters)
    {
        //I'm using this method since it already creates a nice set of site_ids given the "All Field/Clinical/Lab Sites" complication
        $filterInfo = self::getInfoFromFilters($filters, $program_id);

        //Assemble site ids array
        $site_ids = [];
        foreach ($filterInfo['sites'] as $type => $sites) {
            $site_ids = array_merge($site_ids, array_keys($sites));
        }

        return $site_ids;
    }
}
