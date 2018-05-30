<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\NoResultException;
use Fisdap\EntityUtils;

/**
 * Class defining entities that have start date/time, optional end date/time, optional duration
 *
 * @Entity
 * @Table(name="fisdap2_events",uniqueConstraints={@UniqueConstraint(name="event_idx", columns={"event_id", "instance_id"})})
 * @HasLifecycleCallbacks
 */
class Event extends EntityBaseClass
{
    
    /**
     * Event ID is the unique ID of an event, which may have a repeat rule associated with it.
     * One event may have multiple instances, allowing for group deletion/modification
     * @Id
     * @Column(type="integer")
     */
    protected $event_id;
    
    /**
     * The ID of this particular instance of the event.
     * @Column(type="integer")
     */
    protected $instance_id;
    
    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $start;
    
    /**
     * @var \DateTime
     * @Column(type="datetime", nullable=true)
     */
    protected $end;
    
    /**
     * @ManyToOne(targetEntity="Timezone")
     * @JoinColumn(name="timezone_id", referencedColumnName="id")
     */
    protected $timezone;

    /**
     * Duration in hours
     * @Column(type="integer", nullable=true)
     */
    protected $duration;
    
    
    /*
     * Lifecycle callbacks
     */

    /**
     * @PrePersist
     */
    public function created()
    {
        // we're using two auto-generated IDs (event_id and instance_id) so we need to manually increment
        if (isset($this->event_id)) {
            // We have an event ID, so this is an existing event.
            // get last instance ID for this event so we can increment
            $qb = EntityUtils::getEntityManager()->createQueryBuilder();
            $qb->select(array(
                'e.instance_id'
            ))
            ->from('\Fisdap\Entity\Event', 'e')
            ->where('e.event_id = ?1')
            ->orderBy('e.instance_id', 'DESC')
            ->setMaxResults(1)
            ->setParameter(1, $this->event_id);
            $lastInstanceId = $qb->getQuery()->getSingleScalarResult();
        
            $this->instance_id = $lastInstanceId + 1;
        } else {
            // we don't have an event ID, so this is a new event
            // Get last event_id so we can increment
            $qb = EntityUtils::getEntityManager()->createQueryBuilder();
            $qb->select(array(
                'e.event_id'
            ))
            ->from('\Fisdap\Entity\Event', 'e')
            ->orderBy('e.event_id', 'DESC')
            ->setMaxResults(1);
            try {
                $lastEventId = $qb->getQuery()->getSingleScalarResult();
                $this->event_id = $lastEventId + 1;
            } catch (NoResultException $e) {
                // If there are no events, doctrine throws an exception
        $this->event_id = 1; // the first event!
            }
        
            $this->event_id = $lastEventId + 1;
            $this->instance_id = 1; // start a new event at first instance
        }
        
        // make sure a timezone is set
        if (!isset($this->timezone)) {
            // get timezone of current user's program, or default to central time
            $loggedInUser =  User::getLoggedInUser();
            if ($loggedInUser) {
                $this->timezone = $loggedInUser->getCurrentRoleData()->program->program_settings->timezone;
            } else {
                $this->timezone = 2; // central time
            }
        }
        
        // set duration
        if (!isset($this->duration) && isset($this->start) && isset($this->end)) {
            $this->duration = $this::calculateDuration($this->start, $this->end);
        }
    }
    
    /**
     * @PreUpdate
     */
    public function updated()
    {
        // set duration
        if (!isset($this->duration) && isset($this->start) && isset($this->end)) {
            $this->duration = $this::calculateDuration($this->start, $this->end);
        }
    }
    
    
    /*
     * Setters
     */
    public function set_start(\DateTime $start)
    {
        $this->start = $start;
    }
    
    public function set_end(\DateTime $end)
    {
        $this->end = $end;
    }
    
    public function set_timezone($timezone)
    {
        $this->timezone = self::id_or_entity_helper($timezone, 'Timezone');
    }
    
    /*
     * Methods
     */
    
    /*
     * Return string/number representation of difference between two DateTime objects
     * @param DateTime $start Starting time of the event
     * @param DateTime $end The end of the event
     * @param string $format Options include y, m, d, h, i, s, from  http://www.php.net/manual/en/dateinterval.format.php
     *
     * @return mixed The string or integer representation of the difference between the two times
     */
    public static function calculateDuration($start, $end, $format = 'h')
    {
        if (!($start instanceof \DateTime) || !($end instanceof \DateTime)) {
            return false;
        } else {
            $interval = $start->diff($end);
            return $interval->format('%' . $format);
        }
    }

    /*
     * Return a string representation of the event
     * @param string $timeFormat How should hours, minutes, seconds and am/pm be displayed, must match http://www.php.net/manual/en/function.date.php
     * @param string $dateFormat How should date, month, year be displayed, must match http://www.php.net/manual/en/function.date.php
     * @param boolean $showEnd Should we show an end date, if available?
     * @param boolean $showDuration Should we show the duration of the event, if an end datetime is specified?
     *
     * @return string The string representation of the event
     */
    public function format($timeFormat = 'g:ia', $dateFormat = 'm/d/y', $showEnd = true, $showDuration = false, $ignoreTimezone = false)
    {
        $output = '';
    
        // clone the objects because we don't want to modify the Event object properties themselves, which would affect potential future calls to format()
        $start = clone $this->start;
        $end = clone $this->end;
    
        // if we have a timezone, and not told to ignore, convert to current user's timezone
        if (!$ignoreTimezone && $this->timezone) {
            // convert times to appropriate timezone for current user
            $loggedInUser =  User::getLoggedInUser();
            if ($loggedInUser) {
                $targetTimezone = $loggedInUser->getCurrentRoleData()->program->program_settings->timezone;
                $start = Timezone::getLocalDateTime($start, $this->timezone, $targetTimezone);
                $end = Timezone::getLocalDateTime($end, $this->timezone, $targetTimezone);
            }
        }
    
        if ($end && $showEnd) {
            // start and end dates are available
            if ($start->format('j') == $end->format('j')) {
                // event starts and ends on same date
                if ($timeFormat) {
                    $output .= $start->format($timeFormat);
                    $output .= ' to ' . $end->format($timeFormat);
                }
        
                if ($dateFormat) {
                    $output .= ' ' . $start->format($dateFormat);
                }
            } else {
                // event ends on a different date
                if ($timeFormat) {
                    $output .= $start->format($timeFormat);
                }
                if ($dateFormat) {
                    $output .= ' ' . $start->format($dateFormat);
                }
                if ($output) {
                    $output .= ' to ';
                }
                if ($timeFormat) {
                    $output .=  $end->format($timeFormat);
                }
                if ($dateFormat) {
                    $output .= ' ' . $end->format($dateFormat);
                }
            }
        } else {
            // just display start date
            if ($timeFormat) {
                $output .= $start->format($timeFormat);
            }
            if ($dateFormat) {
                $output .= ' ' . $start->format($dateFormat);
            }
        }
    
        // Optionally output duration
        if ($showDuration) {
            $duration = $start->diff($end);
            $output .= ' ' . $duration->format('%h') . ' hours';
        }

        unset($start, $end); //garbage collection of unnecessary objects
        return $output;
    }
}
