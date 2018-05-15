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

/**
 * Entity class for Requirement Notifications
 *
 * @Entity
 * @Table(name="fisdap2_requirement_notifications")
 */
class RequirementNotification extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var \Fisdap\Entity\ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy", inversedBy="requirement_notifications")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;
    
    /**
     * @var \Fisdap\Entity\Requirement
     * @ManyToOne(targetEntity="Requirement", inversedBy="requirement_notifications")
     */
    protected $requirement;
    
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $send_assignment_notification = false;
    
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $send_non_compliant_assignment = false;
    
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="RequirementNotificationWarning", mappedBy="notification", cascade={"persist","remove"})
     */
    protected $warnings;
    
    public function init()
    {
        $this->warnings = new ArrayCollection();
    }
    
    public function set_requirement($value)
    {
        $this->requirement = self::id_or_entity_helper($value, "Requirement");
    }
    
    public function set_program($value)
    {
        $this->program = self::id_or_entity_helper($value, "ProgramLegacy");
    }
    
    /**
     * Send notifications for two cases:
     * 1). newly assigned requirements
     * 2). requirements that have expired or become due
     *
     * @param array $usersToNotify contains all of the info to send in the following format
     * array(1) {
        [userContextId]=>
        array(2) {
          [0]=>
          array(4) {
            ["requirementName"]=>
            string(8) "CPR Card"
            ["status"]=>
            string(7) "expired"
            ["name"]=>
            string(11) "Sam Tape"
            ["email"]=>
            string(19) "ahammond@fisdap.net"
            }
          [1]=>
            array(4) {
              ["requirementName"]=>
              string(7) "TB Test"
              ["status"]=>
              string(7) "expired"
              ["name"]=>
              string(8) "Sam Tape"
              ["email"]=>
              string(16) "stape@fisdap.net"
            }
        }
       }
     */
    public static function sendNotifications($usersToNotify, $emailTemplate)
    {
        switch ($emailTemplate) {
            case "non-compliance-notification.phtml":
                $subject = "Attention: You Are Non-Compliant";
                break;
            case "requirement-assigned-notification.phtml":
                $subject = "New Requirement Assigned";
                break;
            default:
                $subject = "Compliance Notification";
                break;
        }

        foreach ($usersToNotify as $userContextId => $requirements) {
            $email = $requirements[0]["email"];
            
            $emailTemplateParams = array(
                "name" => $requirements[0]["name"],
                "requirements" => $requirements,
                "count" => count($requirements)
            );
            
            $mail = new \Fisdap_TemplateMailer();
            $mail->addTo($email)
                 ->setSubject($subject)
                 ->setViewParams($emailTemplateParams)
                 ->sendHtmlTemplate($emailTemplate);
        }
    }
    
    public static function sendWarnings($usersToNotify)
    {
        foreach ($usersToNotify as $userContextId => $requirements) {
            $email = $requirements[0]["email"];
            
            //Rekey the requirements to organize them by day
            $emailRequirements = array();
            foreach ($requirements as $requirement) {
                $emailRequirements[$requirement['days']][] = $requirement;
            }
            
            $emailTemplateParams = array(
                "name" => $requirements[0]["name"],
                "requirements" => $emailRequirements,
                "count" => count($requirements),
            );
            
            $mail = new \Fisdap_TemplateMailer();
            $mail->addTo($email)
                 ->setSubject("Non-Compliance Warning")
                 ->setViewParams($emailTemplateParams)
                 ->sendHtmlTemplate("non-compliance-warning.phtml");
        }
    }
}
