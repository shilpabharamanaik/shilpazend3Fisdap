<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Calendar Feed
 *
 * @Entity
 * @Table(name="fisdap2_calendar_feeds")
 */
class CalendarFeed extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $name = "Shift Schedule";

    /**
     * @var string
     * @Column(type="string")
     */
    protected $auth_key;

    /**
     * @var \Fisdap\Entity\UserContext
     * @ManyToOne(targetEntity="UserContext")
     * @JoinColumn(name="user_role_id", referencedColumnName="id")
     */
    protected $user_context;

    /**
     * @var array
     * @Column(type="array", nullable=true)
     */
    protected $filters;

    /**
     * @var \DateTime
     * @Column(type="date", nullable=true)
     */
    protected $start_date;

    /**
     * @var \DateTime
     * @Column(type="date", nullable=true)
     */
    protected $end_date;

    /**
     * @param $value string
     * @return \Fisdap\Entity\CalendarFeed
     */
    public function set_name($value)
    {
        if (!empty($value)) {
            $this->name = $value;
        }
        return $this;
    }

    /**
     * @param $value \DateTime
     * @return $this \Fisdap\Entity\CalendarFeed
     */
    public function set_start_date($value)
    {
        $this->start_date = self::string_or_datetime_helper($value);
        return $this;
    }

    /**
     * @param $value \DateTime
     * @return $this \Fisdap\Entity\CalendarFeed
     */
    public function set_end_date($value)
    {
        $this->end_date = self::string_or_datetime_helper($value);
        return $this;
    }

    /**
     * @param $value \Fisdap\Entity\UserContext
     *
     * @return $this \Fisdap\Entity\CalendarFeed
     */
    public function set_user_context($value)
    {
        $this->user_context = self::id_or_entity_helper($value, "UserContext");

        if (!$this->filters) {
            $this->filters = SchedulerFilterSet::getDefaultFilterSet($this->user_context->id);
        }

        return $this;
    }

    /**
     * Generate a semi-random alphanumeric string
     * @return string
     */
    public function generateAuthKey()
    {
        $salt = User::createPasswordSalt();
        $this->auth_key = md5($salt);
        return $this->auth_key;
    }

    /**
     * Get all of the scheduler events tied to this calendar feed
     * @return array["events" => $events, "locations" => $locations
     */
    public function getEvents()
    {
        $startDate = new \DateTime("-1 months");
        $endDate = new \DateTime("+3 months");

        $repo = EntityUtils::getRepository("EventLegacy");

        //Use a different function for getting events for a student
        if ($this->user_context->isStudent()) {
            return $this->getStudentEvents($startDate, $endDate);
        }

        $eventResults = $repo->getStudentEventsByProgram($this->user_context->program->id, $startDate, $endDate, $this->filters);
        $quickAddResults = $repo->getStudentQuickAddShiftsByProgram($this->user_context->program->id, $startDate, $endDate, $this->filters);

        return ['events' => $eventResults, 'quick_add_shifts' => $quickAddResults];
    }

    /**
     * Return the URL to access this calendar feed
     * @return string
     */
    public function getUrl()
    {
        return \Util_HandyServerUtils::getCurrentServerRoot() . "subs/" . $this->auth_key;
    }

    /**
     * @return array
     */
    public function getStudentEvents($startDate, $endDate)
    {
        $repo = EntityUtils::getRepository("EventLegacy");

        $events = $repo->getStudentShifts($this->user_context->id, $startDate, $endDate);
        $locations = array();
        $newEvents = array();
        foreach ($events as $i => $event) {
            $event['instructors'] = $repo->getEventInstructorList($event['event_id']);
            $event['preceptors'] = $repo->getEventPreceptorList($event['event_id']);
            $event['quick_add_shift'] = is_null($event['event_id']);
            $id = $event['quick_add_shift'] ? $event['shift_id'] : $event['event_id'];
            $locations[$event['base_id']] = $event['base_name'];
            $newEvents[$id] = $event;
        }

        return array("events" => $newEvents, "locations" => $locations);
    }
}
