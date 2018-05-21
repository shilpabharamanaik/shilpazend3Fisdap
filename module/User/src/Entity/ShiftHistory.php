<?php namespace User\Entity;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Shift History.
 *
 * @Entity
 * @Table(name="fisdap2_shift_history")
 * @HasLifecycleCallbacks
 */
class ShiftHistory extends EntityBaseClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="ShiftLegacy", inversedBy="histories")
     * @JoinColumn(name="shift_id", referencedColumnName="Shift_id")
     */
    protected $shift;

    /**
     * @ManyToOne(targetEntity="ShiftHistoryChange")
     */
    protected $change;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @Column(type="datetime")
     */
    protected $timestamp;

    /**
     * @PrePersist
     */
    public function setTimestamp()
    {
        $this->timestamp = new \DateTime("now");
    }

    /**
     * Return a short description of what this shift history record is
     * @return string
     */
    public function getSummary()
    {
        //Check to make sure we have a user, otherwise, blame it on the robot
        if ($this->user->id) {
            $name = $this->user->first_name . " " . $this->user->last_name;
        } else {
            $name = "Fisdap Robot";
        }
        $signoffDTFormat = 'M j, Y \a\t Hi';

        // Change timezone to program timezone
        date_default_timezone_set('America/Chicago');

        /** @var DateTime $signoffDT */
        $signoffDT = $this->timestamp;
        $programSettings = $this->shift->student->program->program_settings;

        if ($programSettings && $programSettings->timezone && $programSettings->timezone->mysql_offset) {
            $signoffDT->setTimezone(DateTime::createFromFormat('O', $programSettings->timezone->mysql_offset)->getTimezone());
            $signoffDTFormat = $signoffDT->format($signoffDTFormat);
        } else {
            $signoffDT->setTimezone(new DateTimeZone('America/Chicago'));
            $signoffDTFormat = $signoffDT->format($signoffDTFormat);
        }

        return $this->change->name . " by " . $name . " - " . $signoffDTFormat;
    }
}
