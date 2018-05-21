<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Entity class for Password Reset
 *
 * @Entity
 * @Table(name="fisdap2_password_reset")
 * @HasLifecycleCallbacks
 */
class PasswordReset extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var \Fisdap\Entity\User
     * @ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $code;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $expiration_date;

    public function __construct()
    {
        $this->expiration_date = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUser()
    {
        return $this->user;
    }

    /**
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_user($value)
    {
        $this->user = self::id_or_entity_helper($value, "User");
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function generateCode()
    {
        return md5(uniqid(rand()));
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Email the user a "how to reset" email
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function email()
    {
        $mail = new \Fisdap_TemplateMailer();
        $mail->addTo($this->user->email)
             ->setSubject("Password Reset Confirmation")
             ->setViewParam('passwordReset', $this)
             ->setViewParam('urlRoot', \Util_HandyServerUtils::getCurrentServerRoot())
             ->sendHtmlTemplate('password-reset.phtml');
    }

    /**
     * Given a password reset code, retrieve the entity
     * @param string $code
     * @return \Fisdap\Entity\PasswordReset or NULL if the given code doesn't exist
     */
    public static function getByCode($code)
    {
        $passwordReset = EntityUtils::getRepository("PasswordReset")->findOneByCode($code);
        
        if ($passwordReset->id) {
            return $passwordReset;
        }
        
        return null;
    }
}
