<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreRemove;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Message
 *
 * Message stores the title and body of a message.
 *
 * @Entity
 * @Table(name="fisdap2_messages")
 * @HasLifecycleCallbacks
 */
class Message
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
 
    /**
     * @Column(type="string", length=256, nullable=true)
     */
    protected $title;
    
    /**
     * @Column(type="text", nullable=true)
     */
    protected $body;
    
    /**
     * @Column(type="boolean")
     */
    protected $soft_delete;
    
    /**
     * @ManyToOne(targetEntity="MessageAuthorType")
     * Quick reference: 1 = system, 2 = staff, 3 = user
     */
    protected $author_type;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * @ManyToOne(targetEntity="Event", cascade={"persist","remove"})
     * @JoinColumn(name="due_event_id", referencedColumnName="event_id")
     */
    protected $due;

    /**
     * @ManyToOne(targetEntity="Event", cascade={"persist","remove"})
     * @JoinColumn(name="event_event_id", referencedColumnName="event_id")
     */
    protected $event;
 
    /*
     * Lifecycle callbacks
     */
       
    /**
     * @PrePersist
     */
    public function created()
    {
        if (!isset($this->soft_delete)) {
            $this->soft_delete = 0;
        }

        if (!isset($this->created)) {
            $this->created = $this->updated = new \DateTime("now");
        }
    }
    
    /**
     * @PreRemove
     */
    public function deleted()
    {
        // if the message is being deleted, we need to delete all associated deliveries
        $this->deleteDeliveries(false); // FALSE means a hard delete
    }
   
    
    /**
     * Getters
     */
    public function get_title()
    {
        return $this->title;
    }
    
    public function get_body()
    {
        return $this->body;
    }
    
    public function get_soft_delete()
    {
        return $this->soft_delete;
    }
    
    public function get_author_type()
    {
        return $this->author_type;
    }
    
    public function get_author()
    {
        return $this->author;
    }

    
    /**
     * Setters
     */
    public function set_title($value)
    {
        // store the plain text version of the supplied value
        $this->title = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    public function set_body($value)
    {
        $this->body = $value;
    }
    
    public function set_created(\DateTime $datetime)
    {
        $this->created = $datetime;
        
        // if updated value not set yet, set it to the created time
        if (!$this->updated) {
            $this->updated = $datetime;
        }
    }
    
    public function set_updated(\DateTime $datetime)
    {
        $this->updated = $datetime;
    }
    
    public function set_author_type($value)
    {
        $this->author_type = self::id_or_entity_helper($value, 'MessageAuthorType');
    }

    public function set_author($value)
    {
        $this->author = self::id_or_entity_helper($value, 'User');
    }
    
    public function set_due($value)
    {
        $this->due = self::id_or_entity_helper($value, 'Event');
    }

    public function set_event($value)
    {
        $this->event = self::id_or_entity_helper($value, 'Event');
    }

    public function set_soft_delete($value)
    {
        $this->soft_delete = ($value) ? 1 : 0;
        
        // since the message is being soft-deleted, all associated deliveries need to be soft-deleted.
        if ($this->soft_delete == 1) {
            $this->deleteDeliveries();
        } else {
            // undelete the deliveries
            $this->deleteDeliveries(true, true);
        }
    }
    
    /**
     * Methods
     */
    
    /**
     * Establish delivery to specified recipient users
     * Can be used to set enhance delivery with a sub-type, such as Todo by using the keyed array $subTypes argument
     *
     * @param array() $recipients An array of user IDs or User entities
     * @param boolean $read Should the delivered message be marked as read (1) or unread(0)
     * @param integer $priority Should the delivered message be set to priority (1) or normal (0)
     *
     * @return array Array of user IDs to whom delivery was successful
     */
    public function deliver($recipients = array(), $read = 0, $priority = 0, $subTypes = array())
    {
        $successfulDeliveries = array();

        // Save the message entity at this point, because it is needed by the MessageDelivery entites we will create
        $this->save();

        
        // Since $recipients may include user-supplied values, for user-generated deliveries we need to validate recipients
        // getValidRecipients returns the subset of our recipients who are valid users
        if ($this->author_type->id == 1) {
            $validRecipients = $recipients;
        } else {
            $validRecipients = MessageDelivery::getValidRecipients($recipients);
        }
        
        // for large sets of recipients we need to revert to native MySQL to do the delivery inserts.
        // 1300 is an arbitrary number (slightly more than number of users in inver hills college as of early 2012, natch)
        if (count($validRecipients) > 1300) {
            // @todo subtypes
            // format a long INSERT query
            $insertQuery = 'INSERT INTO fisdap2_messages_delivered (message_id, user_id, is_read, archived, priority, soft_delete) VALUES ';
            foreach ($validRecipients as $key => $recipient) {
                // add to the query string
                if ($key > 0) {
                    $insertQuery .= ', (';
                } else {
                    $insertQuery .= ' (';
                }
                
                $recipient = ($recipient instanceof User) ? $recipient->id : $recipient;
                
                $data = array(
                    ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $this->id) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")),
                    ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $recipient) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")),
                    ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $read) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")),
                    0,
                    ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $priority) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")),
                    0
                );
                
                // @todo subtypes
                $insertQuery .= implode(', ', $data) . ')';
                
                // let's just put this feller in successfulDeliveries without any real checking
                $successfulDeliveries[$recipient] = true;
            }

            //  run with Zend's $db adapter for mysql
            $db = \Zend_Registry::get('db');
            
            // insert
            $db->query($insertQuery);
        } else {
            foreach ($validRecipients as $recipient) {
                $delivery = new MessageDelivery();
                $delivery->set_message($this);
                $delivery->set_recipient($recipient);
                $delivery->set_is_read($read);
                $delivery->set_priority($priority);
    
                // Should we add sub-types to this delivery?
                // This assumes that cascaded persistence is set on these property/relationships on MessageDelivery
                if (!empty($subTypes)) {
                    foreach ($subTypes as $prop => $typeEntity) {
                        // test to see if the setter method for this entity type exists on MessageDelivery
                        $setMethod = 'set_' . $prop;
                        if (method_exists($delivery, $setMethod)) {
                            // clone the typeEntity, since each MessageDelivery gets its own unique
                            $deliveredTypeEntity = clone $typeEntity;
                            $delivery->{$setMethod}($deliveredTypeEntity);
                        }
                    }
                }
                
                $delivery->save(false); // persist the entity, but don't flush yet
                
                $recipientId = ($recipient instanceof User) ? $recipient->id : $recipient;
                $successfulDeliveries[$recipientId] = $delivery;
            }
        }
        
        // flush to create the entities we saved
        $this->flush();
        
        return $successfulDeliveries;
    }
    
    /**
     * Delete deliveries associated with this message
     *
     * @param boolean $soft Should deliveries be soft-deleted (TRUE) or actually deleted (FALSE)?
     * @param boolean $undelete Should deliveries actually be undeleted (TRUE) rather than deleted (FALSE)? Only works with soft_delete status.
     */
    public function deleteDeliveries($soft = true, $undelete = false)
    {
        $deliveryRepo = EntityUtils::getRepository('MessageDelivery');
        $deliveries = $deliveryRepo->getByMessage($this->id);
        
        foreach ($deliveries as $delivery) {
            if ($soft) {
                if ($undelete) {
                    $delivery->set_soft_delete(0);
                } else {
                    $delivery->set_soft_delete(1);
                }
            } else {
                $delivery->delete();
            }
        }
    }
    
    /**
     * Check permissions for creating or modifying a message
     *
     * @param string $action Either 'create' or 'modify'
     * @param string $messageType Optional: The type of message to be created. Should be null for modified messages
     * @param \Fisdap\Entity\Message $message Required only for "modify" action: The message to be modified
     * @param User $user Optional: The user account being checked. Current user is assumed if $user not supplied.
     *
     * @return boolean Either true for permission granted or false for permission denied
     */
    public static function checkPermission($action, $messageType = null, $message = null, $user = null)
    {
        if ($user == null) {
            $user = User::getLoggedInUser();
        }
        
        if ($user->id) {
            
            // check based on action and message type
            switch ($action) {
                case 'create':
                    if ($user->isStaff()) {
                        // staff can always create everything
                        return true;
                    } elseif ($user->getCurrentRoleName() == 'instructor') {
                        // instructors can create all types
                        return true;
                    } else {
                        // students can only create todo items
                        if ($messageType == 'todo') {
                            return true;
                        } else {
                            return false;
                        }
                    }
                    break;
                
                case 'modify':
                    // we need to examine the relevant message. Don't rely on supplied $messageType option
                    if ($message == null) {
                        return false;
                    }
                    
                    // only authors can modify their messages
                    // only todo items can be modifeid (except when staff)
                    $author = $message->get_author();
                    if ($author->id == $user->id && $messageType == 'todo') {
                        return true;
                    } elseif ($author->id == $user->id && $user->isStaff()) {
                        return true;
                    } else {
                        return false;
                    }
                    break;
            }
        } else {
            return false;
        }
    }
}
