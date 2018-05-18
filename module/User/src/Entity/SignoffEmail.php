<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for signoff emails.
 * 
 * @Entity
 * @Table(name="fisdap2_signoff_emails")
 * @HasLifecycleCallbacks
 */
class SignoffEmail extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;

	/**
	 * @Column(type="text")
	 */
	protected $email_key;

	/**
	 * @Column(type="datetime")
	 */
	protected $sent_time;
	
	/**
	 * @Column(type="datetime", nullable=true)
	 */
	protected $expire_time;
	
	/**
	 * @Column(type="datetime", nullable=true)
	 */
	protected $signoff_time;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $has_signed_off = false;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $to_be_locked = false;
	
	/**
	 * @ManyToOne(targetEntity="PreceptorLegacy")
	 * @JoinColumn(name="preceptor_id", referencedColumnName="Preceptor_id")
	 */
	protected $preceptor;
	
	/**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
	protected $user;
	
	/**
	 * @OneToOne(targetEntity="Verification", mappedBy="email")
	 */
	protected $verification;
	
	/**
	 * This function should be used to send out a new physical email to the user
	 * specified in the model.  Should also set the expiration time and sent
	 * time.
	 */
	public function sendEmail()
	{
		if (!$this->verification) {
			throw new \Exception("You must set a verification ID before sending an email.");
		}
		
		if($this->email_key == null){
			$this->email_key = urlencode(md5(time()));
		}
		
		$this->sent_time = new \DateTime();
			
		$mail = new \Fisdap_TemplateMailer();
		$mail->addTo($this->preceptor->email)
			 ->setSubject('Signoff for run ' . $this->verification->run->id)
			 ->setViewParam('verification', $this->verification)
			 ->sendTextTemplate('email-signoff.phtml');
	}
	
	public function set_preceptor($value)
	{
		$this->preceptor = self::id_or_entity_helper($value, "PreceptorLegacy");
	}
}
