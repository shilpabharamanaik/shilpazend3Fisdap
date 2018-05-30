<?php namespace Fisdap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Fisdap\Data\Slot\SlotAssignmentRepository;
use Fisdap\EntityUtils;

/**
 *
 * @Entity(repositoryClass="Fisdap\Data\Shift\DoctrineShiftRequestRepository")
 * @Table(name="fisdap2_shift_requests")
 */
class ShiftRequest extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(name="id", type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var SlotAssignment
     * @ManyToOne(targetEntity="SlotAssignment", inversedBy="requests")
     */
    protected $assignment;

    /**
     * @var EventLegacy
     * @ManyToOne(targetEntity="EventLegacy", inversedBy="requests")
     * @JoinColumn(name="event_id", referencedColumnName="Event_id")
     */
    protected $event;

    /**
     * @var \DateTime
     * @var RequestType
     * @ManyToOne(targetEntity="RequestType")
     */
    protected $request_type;

    /**
     * @Column(type="datetime")
     */
    protected $sent;

    /**
     * @var UserContext
     * @ManyToOne(targetEntity="UserContext")
     */
    protected $owner;

    /**
     * @var UserContext
     * @ManyToOne(targetEntity="UserContext")
     */
    protected $recipient;

    /**
     * @var RequestState
     * @ManyToOne(targetEntity="RequestState")
     */
    protected $accepted;

    /**
     * @var RequestState
     * @ManyToOne(targetEntity="RequestState")
     */
    protected $approved;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Swap", mappedBy="request", cascade={"persist","remove"})
     */
    protected $swaps;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="SwapTerm", mappedBy="request", cascade={"persist","remove"})
     */
    protected $swap_terms;

    public function init()
    {
        $this->swaps = new ArrayCollection;
        $this->swap_terms = new ArrayCollection;
    }

    public function set_request_type($value)
    {
        $this->request_type = self::id_or_entity_helper($value, 'RequestType');
    }

    public function set_owner($value)
    {
        $this->owner = self::id_or_entity_helper($value, 'UserContext');
    }

    public function set_recipient($value)
    {
        $this->recipient = self::id_or_entity_helper($value, 'UserContext');
    }

    public function set_accepted($value)
    {
        $this->accepted = self::id_or_entity_helper($value, 'RequestState');
    }

    public function set_approved($value)
    {
        $this->approved = self::id_or_entity_helper($value, 'RequestState');
    }

    /**
     * Add association between Request and Swap Terms
     *
     * @param \Fisdap\Entity\SwapTerm $swap_term
     */
    public function addSwapTerm(SwapTerm $swap_term)
    {
        $this->swap_terms->add($swap_term);
        $swap_term->request = $this;
    }

    /**
     * Add association between Request and Swap
     *
     * @param \Fisdap\Entity\Swap $swap
     */
    public function addSwap(Swap $swap)
    {
        $this->swaps->add($swap);
        $swap->request = $this;
    }

    /**
     * Determines whether or not this request is pending
     * this function also expires the request if necessary
     */
    public function isPending()
    {
        // check if the event has happened, and expire the request if necessary
        if ($this->event->start_datetime->format('Y-m-d') <= date('Y-m-d')) {
            if ($this->approved->name == 'unset') {
                $this->set_approved(6);
                $this->save();
            }
        }
        // update swap, too
        $this->getCurrentSwap();

        if (($this->accepted->name == 'unset' && $this->approved->name != 'expired') ||
            ($this->accepted->name == 'accepted' && $this->approved->name == 'unset')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the most recent swap offer (pending or not)
     */
    public function getCurrentSwap()
    {
        $sorted = array();
        foreach ($this->swaps as $swap) {
            $sorted[] = $swap;
        }
        @usort($sorted, array('self', 'sortSwapsByDate'));
        $swap = array_shift($sorted);

        // update the status of the swap
        if ($swap) {
            $swap->isPending();
        }

        return $swap;
    }

    public static function sortSwapsByDate($a, $b)
    {
        if ($a->sent == $b->sent) {
            return ($a->id > $b->id ? -1 : 1);
        }

        return ($a->sent > $b->sent ? -1 : 1);
    }

    /**
     * Determines whether or not the given user role is the next to act
     */
    public function requiresAction($userContextId)
    {
        if (!$this->isPending()) {
            return false;
        }
        $swap = $this->getCurrentSwap();
        if ($userContextId == $this->owner->id) {
            if ($swap && $swap->isPending()) {
                return true;
            }
        }
        if ($userContextId == $this->recipient->id) {
            if ($this->accepted->name == 'unset' && (!$swap || !$swap->isPending())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a string containing the status of the request
     *
     * @param bool $images Should status icons be displayed
     *
     * @return string
     */
    public function getStatus($images = false)
    {
        if ($images) {
            $imageArray = $this->getStatusImages();
            $approved = $imageArray['approved'];
            $denied = $imageArray['denied'];
            $pending = $imageArray['pending'];
        }

        switch ($this->accepted->name) {
            case 'unset':
                if ($this->approved->name == 'expired') {
                    return $denied.'Expired';
                } else {
                    return $pending.'Pending';
                }
                break;
            case 'declined':
                return $denied.'Declined';
                break;
            case 'cancelled':
                return $denied.'Cancelled';
                break;
            case 'accepted':
                switch ($this->approved->name) {
                    case "unset":
                        if ($this->request_type->name == 'drop') {
                            return $pending.'Pending approval';
                        } else {
                            return $pending.'Accepted;<br>Pending approval';
                        }
                        break;
                    case "denied":
                        return $denied.'Denied';
                        break;
                    case "approved":
                        if ($this->request_type->name == 'drop') {
                            return $approved.'Dropped';
                        } else {
                            if (is_null($this->assignment)) {
                                $permissions = $this->assignment->getRequestCode('switch_needs_permission');
                                if ($permissions & $this->request_type->bit_value) {
                                    return $approved.'Approved';
                                } else {
                                    return $approved.'Accepted';
                                }
                            } else {
                                return $approved.'Completed';
                            }
                        }
                        break;
                    case "expired":
                        return $denied.'Expired';
                        break;
                }
                break;
        }
        return 'Unknown';
    }

    /**
     * Get an array of status icons keyed by status
     * @return array
     */
    public function getStatusImages()
    {
        return array(
            "approved" => "<img src='/images/icons/approved.png' class='icon' id='approved-icon'>",
            "denied" => "<img src='/images/icons/denied.png' class='icon' id='denied-icon'>",
            "pending" => "<img src='/images/icons/pending.png' class='icon' id='pending-icon'>",
        );
    }

    /**
     * Returns an array containing the terms of the request
     */
    public function getTermsDescription()
    {
        $description = array();
        foreach ($this->swap_terms as $term) {
            $description[] = $term->getDescription();
        }
        return $description;
    }

    /**
     * Returns the name of the recipient of a swap or cover, scheduler if no recipient
     */
    public function getRecipientName()
    {
        $name = array();
        if ($this->request_type->name == 'drop') {
            return false;
        }

        if (!$this->recipient) {
            $name = "scheduler";
        } else {
            $name = $this->recipient->user->getName();
        }
        return $name;
    }

    /**
     * Returns an array of emails for the relevant instructors
     */
    public function getInstructorEmails()
    {
        $emails = array();

        if ($this->event->type == 'clinical') {
            $type = 'Clinic';
        } else {
            $type = ucfirst($this->event->type);
        }

        foreach ($this->owner->getRoleData()->getInstructors() as $instructor) {
            if ($instructor->hasPermission("Edit ".$type." Schedules")) {
                $emails[] = $instructor->user->email;
            }
        }

        if (count($emails) > 0) {
            return $emails;
        } else {
            return array($this->owner->getRoleData()->program->getProgramContact()->email);
        }
    }

    /**
     * Returns an array of emails for the users relevant to a cancel
     */
    public function getCancelEmails()
    {
        $emails = array();

        // if this request has made it to the instructor(s)
        if ($this->accepted->name == 'accepted') {
            $emails += $this->getInstructorEmails();
        }

        // if there is a recipient involved
        if ($this->recipient) {
            $emails[] = $this->recipient->user->email;
        }

        return $emails;
    }

    /**
     * returns a keyed array of all the action codes
     */
    public function getActionCodes()
    {
        $action_codes = array(
            'swap' => array('approved' => 6, 'denied' => 7, 'complete' => 8),
            'drop' => array('approved' => 9, 'denied' => 10, 'complete' => 11),
            'cover' => array('approved' => 12, 'denied' => 13, 'complete' => 14),
        );

        return $action_codes;
    }

    /**
     * This function returns an array of available assignments formatted for use in a form
     *
     * @return array
     */
    public function getAssignmentOptions()
    {
        $results = array();
        $startdate = new \DateTime('+1 day');
        $enddate = null;
        $owner_program_id = $this->owner->program->id;

        // get the recipient's shifts in the future
        /** @var SlotAssignmentRepository $slotAssignmentRepository */
        $slotAssignmentRepository = EntityUtils::getRepository("SlotAssignment");
        $assignments = $slotAssignmentRepository->getUserContextAssignmentsByDate($this->recipient->id, $startdate, $enddate);

        // make sure each one is available to the owner and not already assigned to them
        $student_group_repo = EntityUtils::getRepository('ClassSectionLegacy');
        $groups = $student_group_repo->getProgramGroups($owner_program_id, null, $this->owner->getRoleData()->id, true, true, $this->owner->user);

        foreach ($assignments as $assignment) {
            // make sure the shift is available for swapping (with or without permission)
            if (!($assignment->getRequestCode("student_can_switch") & 4)) {
                continue;
            }

            // make sure the owner is not already scheduled for this shift
            if ($this->owner->isScheduled($assignment->slot->event->id)) {
                continue;
            }

            // make sure this shift is not already part of a pending request
            if ($assignment->hasPendingRequest()) {
                continue;
            }

            // make sure the shift is available for the owner
            if (!$assignment->slot->event->isAvailableTo($this->owner->certification_level->id, $groups, $owner_program_id, false, $this->owner->user)) {
                continue;
            }

            // we made it this far? Add that sucker to the list!
            $results[$assignment->id] = $assignment->slot->event->getOptionText();
        }

        return $results;
    }

    /**
     * Performs the actual drop, swap or cover by manipulating the assignment(s)/shift(s)
     */
    public function processRequest()
    {
        switch ($this->request_type->name) {
            case 'drop':
                $old_assignment = $this->assignment;
                $this->assignment = null;

                //remove the assignment, but don't record history and send the removee email since we'll do shift request specific emails/history below
                $old_assignment->remove(true, true, false, false);
                break;

            case 'cover':
                //Remove the association to the assignment that will be removed
                $old_assignment = $this->assignment;
                $this->assignment = null;

                //Remove the assignment,
                $old_assignment->remove(true, true, false, false);

                //Then add the recepient to the event
                $this->assignment = $this->event->assign($this->recipient, true, true, false, false);
                $this->save();
                break;

            case 'swap':
                //Get the swap and its corresponding event
                $swap = $this->getCurrentSwap();
                $swapEvent = $swap->offer->slot->event;

                //Remove the associations to existing assignments
                $old_assignment = $this->assignment;
                $this->assignment = null;
                $old_swap_assignment = $swap->offer;
                $swap->offer = null;

                //Remove the assignments from both parties
                $old_assignment->remove(true, true, false, false);
                $old_swap_assignment->remove(true, true, false, false);

                //Now assign both parties to each others' events
                $this->assignment = $this->event->assign($this->recipient, true, true, false, false);
                $swap->offer = $swapEvent->assign($this->owner, true, true, false, false);

                $this->save();
                break;
        }

        // send the mails
        $mail = new \Fisdap_TemplateMailer();
        $mail->addTo($this->owner->user->email)
            ->setSubject(ucfirst($this->request_type->name)." request complete")
            ->setViewParam("request", $this)
            ->sendHtmlTemplate("shift-request-complete-owner.phtml");

        $mail->clearRecipients();

        if ($this->request_type->name == 'cover' || $this->request_type->name == 'swap') {
            $mail->addTo($this->recipient->user->email)
                ->sendHtmlTemplate("shift-request-complete-recipient.phtml");
        }

        $mail->clearRecipients();

        $email_list = $this->getInstructorEmails();
        foreach ($email_list as $email) {
            $mail->addTo($email);
        }
        $mail->sendHtmlTemplate("shift-request-complete-instructor.phtml");


        // log the action in event history
        $action_codes = $this->getActionCodes();
        $action = EntityUtils::getEntity("EventAction");
        $action->set_type($action_codes[$this->request_type->name]['complete']);
        $action->initiator = $this->owner;
        $action->recipient = $this->recipient;
        $this->event->addAction($action);
        $this->event->save();

        if ($this->request_type->name == 'swap') {
            // log the action in event history for the swap, too
            $swap_action = EntityUtils::getEntity("EventAction");
            $swap_action->set_type($action_codes[$this->request_type->name]['complete']);
            $swap_action->initiator = $this->recipient;
            $swap_action->recipient = $this->owner;
            $swap = $this->getCurrentSwap();
            $swap->offer->slot->event->addAction($swap_action);
            $swap->offer->slot->event->save();
        }

        return true;
    }
}
