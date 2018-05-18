<?php namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Requirement Attachment
 *
 * @Entity
 * @Table(name="fisdap2_requirement_attachments")
 */
class RequirementAttachment extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var \Fisdap\Entity\Requirement
     * @ManyToOne(targetEntity="Requirement")
     */
    protected $requirement;

    /**
     * @var \Fisdap\Entity\UserContext
     * @ManyToOne(targetEntity="UserContext", inversedBy="requirement_attachments")
     * @JoinColumn(name="user_role_id", referencedColumnName="id")
     */
    protected $user_context;

    /**
     * @var \DateTime
     * @Column(type="date", nullable=true)
     */
    protected $due_date;

    /**
     * @var \DateTime
     * @Column(type="date", nullable=true)
     */
    protected $expiration_date;

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $completed = 0;

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $archived = 0;

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $expired = 0;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ManyToMany(targetEntity="PortfolioUploads")
     * @JoinTable(name="fisdap2_req_attachment_uploads",
     *      joinColumns={@JoinColumn(name="requirement_attachment_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="upload_id", referencedColumnName="id")}
     *      )
     */
    protected $uploads;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @OneToMany(targetEntity="RequirementHistory", mappedBy="requirement_attachment", cascade={"persist", "remove"})
     */
    protected $histories;


    public function __construct()
    {
        $this->uploads = new ArrayCollection;
        $this->histories = new ArrayCollection;
        $this->due_date = new \DateTime();
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    public function set_due_date($value, $userContextId = null)
    {
        $value = self::string_or_datetime_helper($value);

        //If there was a value set for completion, and it's changing, record history
        if (!is_null($this->due_date) && $value != $this->due_date && $this->id) {
            $this->recordHistory(7, "due date", $userContextId);
        }

        $this->due_date = $value;
        return $this;
    }

    public function set_expiration_date($value, $userContextId = null)
    {
        $value = self::string_or_datetime_helper($value);

        //If there was a value set for completion, and it's changing, record history
        if (!is_null($this->expiration_date) && $value != $this->expiration_date && $this->id) {
            $this->recordHistory(7, "expiration date", $userContextId);
        }

        $this->expiration_date = $value;
        return $this;
    }

    public function set_completed($value, $userContextId = null)
    {
        //If there was a value set for completion, and it's changing, record history
        if (!is_null($this->completed) && $value != $this->completed) {
            $this->recordHistory($value ? 1 : 2, null, $userContextId);
        }

        $this->completed = $value;

        return $this;
    }

    public function set_user_context($value)
    {
        // If there was no value for user role, record the history of this requirement being attached
        $this->user_context = self::id_or_entity_helper($value, "UserContext");
        ;

        return $this;
    }

    /**
     * Determine if a requirement attachment is past its expiration date
     * @return boolean
     */
    public function isExpired()
    {
        //if there's no expiration date, then it can't be expired
        if (!$this->expiration_date) {
            return false;
        }

        return (date_create() >= $this->expiration_date) || $this->expired;
    }

    /**
     * Determine if a requirement is past its due date, and can be computed for compliance
     * @return boolean
     */
    public function isDue()
    {
        return new \DateTime() >= $this->due_date;
    }

    /**
     * Determine if this particular attachment is compliant
     * @return boolean
     */
    public function isCompliant()
    {
        //If archived, the attachment shouldn't count against your compliance
        if ($this->archived) {
            return true;
        }

        //There are two cases for compliance:
        // 1). The requirement is completed and not expired.
        // 2). The requirement is not due yet.
        return ($this->completed && !$this->isExpired()) || !$this->isDue();
    }

    public function archive($createNewAttachment = true, $userContextId = null)
    {
        $this->archived = true;

        $this->recordHistory(3, null, $userContextId);

        if ($createNewAttachment) {
            return $this->user_context->assignRequirement($this->requirement, null, 0, null, "renewed");
        }

        return $this;
    }

    public function getExpirationDate($format)
    {
        if ($this->expiration_date) {
            return $this->expiration_date->format($format);
        }

        return null;
    }


    public function getStatus()
    {
        // archived
        if ($this->archived) {
            return "archived";
        }

        // missing and not past due
        if (!$this->completed && !$this->isDue()) {
            return "in progress";
        }

        // compliant; we already weeded out the technical versions of compliance above
        if ($this->isCompliant()) {
            return "compliant";
        } else {
            return "non-compliant";
        }
    }

    public function getSite($association_program_id)
    {
        // This is not real. You can't really determine site from attachment.
        return $this->requirement->getAssocationByProgram($association_program_id)->site;
    }


    /**
     * @param      $change
     * @param null $notes
     * @param null $userContextId
     * @todo refactor
     * @codeCoverageIgnore
     * @deprecated
     */
    public function recordHistory($change, $notes = null, $userContextId = null)
    {
        $history = EntityUtils::getEntity("RequirementHistory");
        $history->requirement = $this->requirement;
        $history->requirement_attachment = $this;
        $history->change = $change;
        $history->notes = $notes;

        // Only record the logged in user if there's someone logged in and the Fisdap Robot was not explicitly used
        if ($userContextId != "Fisdap Robot" && User::getLoggedInUser()) {
            $history->user_context = User::getLoggedInUser()->getCurrentUserContext();
        } elseif ($userContextId != "Fisdap Robot" && $userContextId) {
            // otherwise, if there is no logged in user but a user role has been specified, use that
            $history->user_context = EntityUtils::getEntity('UserContext', $userContextId);
        }

        $this->histories->add($history);
    }

    public function getHistorySummary()
    {
        $summaries = array();
        $histories = EntityUtils::getRepository("Requirement")->getFullAttachmentHistory($this);

        $userContext = User::getLoggedInUser()->getCurrentUserContext();

        foreach ($histories as $history) {
            //Set the user for the history record
            if (is_null($history->user_context->id)) {
                $user = "Fisdap Robot";
            } elseif ($userContext->id == $history->user_context->id) {
                $user = "You";
            } else {
                $user = $history->user_context->user->getName();
            }

            //Set the subject for the history record
            $subject = $userContext->id == $this->user_context->id ? "you" : $this->user_context->user->getName();

            switch ($history->change->id) {
                case 1:
                    $summary = $user . " marked " . $subject . " compliant" . ($history->notes ? " (" . $history->notes . ")" : "") . ".";
                    break;
                case 2:
                    $summary = $user . " marked " . $subject . " non-compliant" . ($history->notes ? " (" . $history->notes . ")" : "") . ".";
                    break;
                case 3:
                    $summary = $user . " archived the requirement.";
                    break;
                case 4:
                    $summary = $user . " assigned the requirement to " . $subject . ($history->notes ? " (" . $history->notes . ")" : "") . ".";
                    break;
                case 5:
                    $summary = $user . " created the requirement.";
                    break;
                case 6:
                    $summary = $user . " edited the requirement" . ($history->notes ? " " . $history->notes : "") . ".";
                    break;
                case 7:
                    $summary = $user . " updated " . $subject . ($subject == "you" ? "r" : "'s") . " " . ($history->notes ? $history->notes : "requirement") . ".";
                    break;
                case 8:
                    $summary = $user . " activated the requirement.";
                    break;
                case 9:
                    $summary = $user . " deactived the requirement.";
                    break;
            }
            $summaries[] = array("datetime" => $history->timestamp, "summary" => $summary);
        }
        return $summaries;
    }
}
