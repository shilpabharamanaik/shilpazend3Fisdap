<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * Class Requirements
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait Requirements
{
    /**
     * @OneToMany(targetEntity="RequirementNotification", mappedBy="program")
     * @JoinColumn(name="Program_id", referencedColumnName="program_id")
     */
    protected $requirement_notifications;


    /**
     * Should a new requirement notification be sent for this requirement?
     * @param integer $requirementId
     * @return boolean
     */
    public function sendNewRequirementNotification($requirementId)
    {
        foreach ($this->requirement_notifications as $notification) {
            if ($notification->requirement->id == $requirementId) {
                return $notification->send_assignment_notification;
            }
        }

        return false;
    }
}
