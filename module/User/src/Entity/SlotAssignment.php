<?php namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

//use Fisdap\EntityUtils;


/**
 * Slot Assignment
 *
 * @Entity(repositoryClass="Fisdap\Data\Slot\DoctrineSlotAssignmentRepository")
 * @Table(name="fisdap2_slot_assignments")
 */
class SlotAssignment extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var Slot
     * @ManyToOne(targetEntity="Slot", inversedBy="assignments")
     */
    protected $slot;

    /**
     * @var UserContext
     * @ManyToOne(targetEntity="UserContext", inversedBy="slot_assignments")
     * @JoinColumn(name="user_role_id", referencedColumnName="id")
     */
    protected $user_context;

    /**
     * @var boolean
     * @Column(type="boolean", nullable=true)
     */
    protected $compliant;

    /**
     * @var boolean
     * @Column(type="boolean", nullable=true)
     */
    protected $global_site_compliant;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="ShiftRequest", mappedBy="assignment", cascade={"persist"})
     */
    protected $requests;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Swap", mappedBy="offer", cascade={"persist","remove"})
     */
    protected $swaps;

    /**
     * @OneToOne(targetEntity="ShiftLegacy", mappedBy="slot_assignment", cascade={"persist","remove"})
     */
    protected $shift;

    public function init()
    {
        $this->requests = new ArrayCollection;
        $this->swaps = new ArrayCollection;
    }

    public function getRequestCode($code_type)
    {
        $event = $this->slot->event;
        $program = $this->user_context->program;

        // see if a program-specific code exists for this event
        foreach ($event->shared_preferences as $program_preference) {
            if ($program_preference->program->id == $program->id) {
                $code = $program_preference->{$code_type};
            }
        }

        // otherwise, use the event code
        $code = $event->{$code_type};

        // if this is an assignment that was previously shared, they can only drop
        $share = $event->getEventShareByProgram($program->id);
        if ($share->retired) {
            return $code & 1 ? 1 : 0;
        }

        return $code;
    }

    public function getRequestOptions()
    {
        $requestTypes = RequestType::getAll(true);

        $can = $this->getRequestCode('student_can_switch');
        $permission = $this->getRequestCode('switch_needs_permission');

        $options = array();
        foreach ($requestTypes as $requestType) {
            $bit = $requestType['bit_value'];
            $needs_permission = $permission & $bit ? 1 : 0;
            if ($can & $bit) {
                $options[$requestType['id']] = array('name' => $requestType['name'], 'needs_permission' => $needs_permission);
            }
        }

        return $options;
    }

    public function getRequestRecipients()
    {
        $options = array();
        $program_id = $this->user_context->program->id;
        $students = $this->slot->event->getAvailableUsers($program_id);
        $assigned_users = EntityUtils::getRepository("EventLegacy")->getAssignedUsersByEventOptimized($this->slot->event->id);
        foreach ($students as $id => $student) {
            if (!in_array($id, $assigned_users)) {
                $options[$id] = $student;
            }
        }
        return $options;
    }


    public function hasPendingRequest()
    {
        foreach ($this->requests as $request) {
            if ($request->isPending()) {
                return true;
            }
        }
        foreach ($this->swaps as $swap_offer) {
            if ($swap_offer->request->isPending() && $swap_offer->accepted->name != "declined") {
                return true;
            }
        }

        return false;
    }

    public function switchOwner($new_owner)
    {
        $this->user_context = $new_owner;
        $this->shift->set_student($new_owner->getRoleData()->id);
        $this->save();
    }

    /**
     * Remove this slot assignment and email involved parties to let them know
     *
     * @param boolean $flush         should database changes be flushed
     * @param boolean $send_email    should emails be sent
     * @param boolean $recordHistory should history events be recorded
     * @param boolean $emailRemovee  should the person being removed get an email
     *
     * @return bool
     */
    public function remove($flush = true, $send_email = true, $recordHistory = true, $emailRemovee = true)
    {
        $user = User::getLoggedInUser();
        $event = $this->slot->event;
        $assignment_user_context = $this->user_context;

        // log the action in event history
        if ($recordHistory) {
            $action = EntityUtils::getEntity("EventAction");
            $action->set_type(4);
            $action->initiator = $user->getCurrentUserContext();
            $action->recipient = $assignment_user_context;
            $event->addAction($action);
            $event->save($flush);
        }

        // do the drop!
        $this->slot->assignments->removeElement($this);
        $this->delete($flush);

        // send emails
        if ($send_email) {
            $mail = new \Fisdap_TemplateMailer();
            $mail->addTo($assignment_user_context->user->email)
                ->setSubject("A shift has been removed from your schedule")
                ->setViewParam("event", $event)
                ->setViewParam("initiator", $user)
                ->setViewParam("recipient", $assignment_user_context);

            //Only send email if program setting allows it
            if ($assignment_user_context->program->program_settings->send_scheduler_student_notifications) {
                $mail->sendHtmlTemplate("shift-removed.phtml");
            }

            $mail->clearRecipients()
                ->clearSubject()
                ->setSubject($assignment_user_context->user->getName()." removed from shift");

            $email_list = $event->getInstructorEmails();
            foreach ($email_list as $email) {
                $mail->addTo($email);
            }
            if (count($email_list) > 0) {
                $mail->sendHtmlTemplate("shift-removed-others.phtml");
            }
        }

        return true;
    }
}
