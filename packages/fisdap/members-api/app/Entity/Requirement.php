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
use Fisdap\EntityUtils;

/**
 * Requirement
 *
 * @Entity(repositoryClass="Fisdap\Data\Requirement\DoctrineRequirementRepository")
 * @Table(name="fisdap2_requirements")
 */
class Requirement extends Enumerated
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $name;

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $expires = 1;

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $universal = 0;

    /**
     * @var RequirementCategory
     * @ManyToOne(targetEntity="RequirementCategory")
     * @JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="RequirementAssociation", mappedBy="requirement", cascade={"persist","remove"})
     */
    protected $requirement_associations;
    
    /**
    * @var ArrayCollection
    * @OneToMany(targetEntity="RequirementAttachment", mappedBy="user_context", cascade={"persist","remove"})
    */
    protected $requirement_attachments;
    
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="RequirementHistory", mappedBy="requirement", cascade={"persist","remove"})
     */
    protected $requirement_histories;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="RequirementAutoAttachment", mappedBy="requirement", cascade={"persist","remove"})
     */
    protected $requirement_auto_attachments;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="RequirementNotification", mappedBy="requirement", cascade={"persist","remove"})
     */
    protected $requirement_notifications;

    public function init()
    {
        $this->requirement_associations = new ArrayCollection;
        $this->requirement_attachments = new ArrayCollection;
        $this->requirement_histories = new ArrayCollection;
        $this->requirement_auto_attachments = new ArrayCollection;
        $this->requirement_notifications = new ArrayCollection;
    }

    public function set_category($value, $userContextId = null)
    {
        $category = self::id_or_entity_helper($value, "RequirementCategory");

        if ($category->id != $this->category->id && $this->id) {
            $this->recordHistory(6, "category", $userContextId);
        }

        $this->category = $category;
        return $this;
    }

    public function set_name($value, $userContextId = null)
    {
        if ($value != $this->name && $this->id) {
            $this->recordHistory(6, "name", $userContextId);
        }

        $this->name = $value;
    }

    public function set_expires($value, $userContextId = null)
    {
        if ($value != $this->expires && $this->id) {
            $this->recordHistory(6, "expiration status", $userContextId);
        }

        $this->expires = $value;
    }

    /**
     * Add association between Requirement and RequirementAssociation
     * @param RequirementAssociation $assoc
     */
    public function addAssociation(RequirementAssociation $assoc)
    {
        $this->requirement_associations->add($assoc);
        $assoc->requirement = $this;
    }


    /**
     * Add auto attachment
     * @param RequirementAutoAttachment $auto
     */
    public function addAutoAttachment(RequirementAutoAttachment $auto)
    {
        $this->requirement_auto_attachments->add($auto);
        $auto->requirement = $this;
    }

    public function getAssocationByProgram($program_id)
    {
        if ($this->requirement_associations) {
            foreach ($this->requirement_associations as $assoc) {
                if ($assoc->program->id == $program_id) {
                    return $assoc;
                }
            }
        }

        return false;
    }

    public function getAllAssociationsByProgram($program_id)
    {
        $associations = array();
        if ($this->requirement_associations) {
            foreach ($this->requirement_associations as $assoc) {
                if ($assoc->program->id == $program_id) {
                    $associations[] = $assoc;
                }
            }
        }

        return $associations;
    }

    public function getProgramLevelAssocByProgram($program_id)
    {
        $assocations = $this->getAllAssociationsByProgram($program_id);

        foreach ($assocations as $assoc) {
            if (!$assoc->site) {
                return $assoc;
            }
        }

        return false;
    }

    public function getSiteAssocByProgram($site_id, $program_id)
    {
        $assocations = $this->getAllAssociationsByProgram($program_id);

        foreach ($assocations as $assoc) {
            if ($assoc->site->id == $site_id) {
                return $assoc;
            }
        }

        return false;
    }

    public function recordHistory($change, $notes = null, $userContextId = null)
    {
        $history = EntityUtils::getEntity("RequirementHistory");
        $history->requirement = $this;
        $history->change = $change;
        $history->notes = $notes;

        if (User::getLoggedInUser()) {
            $history->user_context = User::getLoggedInUser()->getCurrentUserContext();
        } elseif ($userContextId) {
            $history->user_context = EntityUtils::getEntity('UserContext', $userContextId);
        }

        $this->requirement_histories->add($history);
    }

    public function getType()
    {
        $type = "program";

        foreach ($this->requirement_associations as $association) {
            if ($association->site) {
                $type = "site";
            }

            if ($association->global) {
                $type = "shared";
            }
        }

        return $type;
    }

    /**
     * @param array $site_ids the ids of the sites to be associated with this requirement
     * @param ProgramLegacy|int $program the program (or program id) with which to associate these site requirements
     * @param bool $global a flag to determine if this requirement is shared with the network
     */
    public function createSiteAssociations($site_ids, $program, $global = false)
    {
        foreach ($site_ids as $site_id) {
            $requirement_association = EntityUtils::getEntity('RequirementAssociation');
            $requirement_association->set_program($program);
            $requirement_association->set_site($site_id);
            $requirement_association->start_date = new \DateTime();
            $requirement_association->end_date = new \DateTime();
            if ($global) {
                $requirement_association->global = $global;
            }
            $this->addAssociation($requirement_association);
            $requirement_association->save();
        }
    }

    /**
     * @param ProgramLegacy|int $program the program (or program id) with which to associate this program requirement
     */
    public function createProgramAssociation($program)
    {
        // Create a single association between this requirement and this user's program
        $requirement_association = EntityUtils::getEntity('RequirementAssociation');
        $requirement_association->set_program($program);
        $requirement_association->start_date = new \DateTime();
        $requirement_association->end_date = new \DateTime();
        $this->addAssociation($requirement_association);
        $requirement_association->save();
    }

    /**
     * @param array $due_dates an array of due dates keyed with group ids; the groups' requirements are due on the given date
     * @param array $userContextId_csl an array of comma-separated lists of user role ids, keyed by group id
     * @param array $compute_compliance_userContextIds an array of user role ids for which compliance with need to be re-computed
     * @param bool $sendNotification a flag determining whether or not these users should receive an email notification of this assignment
     * @param int $assigner_userContextId the user role id of the user who is doing the assigning here, for use with background workers
     * @return array an array of user role ids for which compliance with need to be re-computed
     */
    public function assignRequirementByDueDateGroup($due_dates, $userContextId_csl, $compute_compliance_userContextIds, $sendNotification = false, $assigner_userContextId = null)
    {
        // Our user has grouped user_contexts and due dates.
        // We'll need to step through each selected due date option and get its respective list of user role ids
        foreach ($due_dates as $temp_group_id => $due_date) {
            // the user role ids are in another select box (each option is a comma separated list) and have corresponding indexes (the temp group id)
            $userContextIds = explode(",", $userContextId_csl[$temp_group_id]);
            $compute_compliance_userContextIds = $this->assignRequirementToUserContexts($userContextIds, $due_date, $compute_compliance_userContextIds, $sendNotification, $assigner_userContextId);
        }

        return $compute_compliance_userContextIds;
    }

    /**
     * @param array           $userContextIds                   an array of UserContext ids to be assigned this requirement
     * @param \DateTime|string $due_date                         the date on which the requirement is due
     * @param array           $compute_compliance_userContextIds an array of user role ids for which compliance with need to be re-computed
     * @param bool            $sendNotification                 a flag determining whether or not these users should receive an email notification of this assignment
     * @param int             $assigner_userContextId            the user role id of the user who is doing the assigning here, for use with background workers
     *
*@return array an array of user role ids for which compliance with need to be re-computed
     *
     */
    public function assignRequirementToUserContexts($userContextIds, $due_date, $compute_compliance_userContextIds, $sendNotification = false, $assigner_userContextId = null)
    {
        // make our due date string a date time
        if (!($due_date instanceof \DateTime)) {
            $due_date = new \DateTime($due_date . " 23:59:59");
        }

        // For each user role, assign the requirement to them
        foreach ($userContextIds as $userContextId) {
            // this could already be the user role entity
            $user_context = (is_int($userContextId) || is_string($userContextId)) ? EntityUtils::getEntity('UserContext', $userContextId) : $userContextId;
            $user_context->assignRequirement($this, null, 0, $due_date, null, $assigner_userContextId, $sendNotification);

            // compute compliance for ALL users going to this site, regardless of whether they had the
            // attachment before or not
            $compute_compliance_userContextIds[] = $user_context->id;
        }

        return $compute_compliance_userContextIds;
    }
}
