<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;


/**
 * Message stores the title and body of a message.
 * 
 * @Entity(repositoryClass="Fisdap\Data\MessageDelivery\DoctrineMessageDeliveryRepository")
 * @Table(name="fisdap2_messages_delivered")
 * @HasLifecycleCallbacks
 */
class MessageDelivery extends EntityBaseClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="Message")
     * @JoinColumn(name="message_id", referencedColumnName="id")
     */
    protected $message;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $recipient;
    
    /**
     * @Column(type="boolean")
     */
    protected $is_read;
    
    /**
     * @Column(type="boolean")
     */
    protected $archived;
    
    /**
     * @Column(type="integer")
     */
    protected $priority;

    /**
     * @Column(type="boolean")
     */
    protected $soft_delete;
    
    /**
     * @ManyToOne(targetEntity="Todo", cascade={"persist","remove"})
     * @JoinColumn(name="todo_id", referencedColumnName="id")
     *
     * The message can have a Todo associated, allowing completed status and recipient notes
     */
    protected $todo;    
    

    /*
     * Lifecycle callbacks
     */
       
    /**
     * @PrePersist
     */
    public function created()
    {
        if (!isset($this->is_read)) {
            $this->is_read = 0;
        }
        if (!isset($this->archived)) {
            $this->archived = 0;
        }
        if (!isset($this->priority)) {
            $this->priority = 0;
        }
        if (!isset($this->soft_delete)) {
            $this->soft_delete = 0;
        }
    }
    
    
    /**
     * Getters
     */
    public function get_message() {
        return $this->message;
    }
    
    public function get_recipient() {
        return $this->recipient;
    }
    
    public function get_is_read() {
        return $this->is_read;
    }
    
    public function get_archived() {
        return $this->archived;
    }
    
    public function get_priority() {
        return $this->priority;
    }
    
    public function get_soft_delete() {
        return $this->soft_delete;
    }
   
    
    /**
     * Setters
     */    
    public function set_message($value)
    {
        $this->message = self::id_or_entity_helper($value, 'Message');
    }

    public function set_recipient($value)
    {
        $this->recipient = self::id_or_entity_helper($value, 'User');
    }
    
    public function set_is_read($value)
    {
        $this->is_read = ($value) ? 1 : 0;
    }
    
    public function set_archived($value)
    {
        $this->archived = ($value) ? 1 : 0;
    }
    
    public function set_priority($value) {
        $this->priority = intval($value);
    }
    
    public function set_soft_delete($value)
    {
        $this->soft_delete = ($value) ? 1 : 0;
    }
    
    public function set_todo($value)
    {
        $this->todo = self::id_or_entity_helper($value, 'Todo');
    }
    
    
    /**
     * Methods
     */
    
    
    /**
     * Methods
     */
    
    /**
     * Essentially a checkPermissions function for creating MessageDelivery. We take in an array of recipients, which may include user-upplied IDs
     * and figure out which of them are valid (lgic depends on user role)
     *
     * @param array $recipients Array of recipients
     *
     * @return array Array of valid recipients
     */
    static function getValidRecipients($recipients) {
    	// get the user
    	$user = User::getLoggedInUser();
    	
    	// This breaks (it's the filtering for beta-only users...).  Not gonna do it.
    	/*
    	set_time_limit(0);
    	
        // Making it so that the list of recipients is always filtered so that only beta users are sent
        // messages...
        
        // cache the programs beta flag indexed by ID so we aren't constantly repinging the DB for the info...
        $programUseBetaCache = array();
        $cleanRecipients = array();
        
        foreach($recipients['recipients'] as $id => $recipient){
        	$user = \Fisdap\EntityUtils::getEntity('UserLegacy', $id);
        	
        	try{
        		$progId = $user->getProgramId();
        		
        		if(!isset($programUseBetaCache[$progId])){
        			$programUseBetaCache[$progId] = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $progId)->use_beta;
        		}
        		
        	 	if($programUseBetaCache[$progId]){
        	 		$cleanRecipients[$id] = $recipient;
        	 	}
        	}catch(\Exception $e){
        	}
        }
        
        $recipients['recipients'] = $cleanRecipients;
        */
        
        // staff are always allowed to deliver to ANYONE.
        if ($user->staff != NULL && $user->staff->isStaff()) {
    		$deliveryRepo = EntityUtils::getRepository('messageDelivery');
            // check if the number of recipients is huge. If so, let's bypass doctrine
            if (count($recipients) > 1300) {
                return $deliveryRepo->getValidRecipientsNoDoctrine($recipients);
            } else {
                return $deliveryRepo->getValidRecipients($recipients);
            }
        }
        
        // if num of recipients is one, and that one is the user, we're good to go
        if (count($recipients) == 1) {
            if (is_numeric($recipients[0]) && $recipients[0] == $user->id) {
                return $recipients;
            } else if ($recipients[0] instanceof User) {
                if ($recipients[0]->id == $user->id) {
                    return $recipients;
                }
            }
        }
        
        // if instructor and more than one recipient, perform a check query
        if ($user->getCurrentRoleName() == 'instructor') {
            // the query will return valid users in the instructor's program.
            $programId = $user->getProgramId();
            
            // the query only wants numeric IDs, so if these are objects we need to transform
            foreach($recipients as $key => $recipient) {
                if ($recipient instanceof User) {
                    $recipients[$key] = $recipient->id;
                }
            }
            $deliveryRepo = EntityUtils::getRepository('messageDelivery');
            $validRecipients = $deliveryRepo->getValidRecipients($recipients, $programId);
            
            return $validRecipients;
        } else {
            // students are not allowed to send messages to groups of users
            return array();
        }
        
        // if we're still here, wtf, just return no valid recipients
        return array();
    }
    
    /**
     * Check permissions for modifying an existing MessageDelivery
     *
     * @return boolean Either true for permission granted or false for permission denied
     */
    public function checkPermission() {
        $user = User::getLoggedInUser();
        
        if ($user->id) {
            // first, check if user is staff (grant all permissions)
            if($user->staff != NULL && $user->staff->isStaff()) {
                return TRUE;
            }
            
            // for regular users, only allowed to modify messages they have received
            if ($this->recipient->id == $user->id) {
                return TRUE;
            } else {
                return FALSE;
            }

            
        } else {
            return FALSE;
        }
    }
}
