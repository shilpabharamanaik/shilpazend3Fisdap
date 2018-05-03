<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\PostLoad;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\Table;
use Exception;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;


/**
 * Entity class for Signatures.
 * 
 * @Entity
 * @Table(name="fisdap2_signatures")
 * @HasLifecycleCallbacks
 */
class Signature extends EntityBaseClass
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
	protected $signature_string_old;

    /**
     * @Column(type="text")
     */
    protected $signature_string;

	/**
	 * @Column(type="string", nullable=true)
	 */
	protected $name;

	/**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
	protected $user;
	
	/**
	 * @OneToOne(targetEntity="Verification", mappedBy="signature")
	 */
	protected $verification;
	
	/**
	 * This function converts the signature string into a gzcompressed string
	 * before saving.
	 * 
	 * @PrePersist
	 */
	public function compressSignatureString()
    {
        $this->signature_string = gzcompress($this->signature_string);
	}
	
	/**
	 * This function converts the signature string into a standard string
	 * after loading it up.
	 *
	 * @PostLoad
	 */
	public function uncompressSignatureString()
	{
        $this->signature_string = gzuncompress($this->signature_string);
	}

    public function setSignatureString($signature)
    {
        $this->signature_string = $signature;
    }

    public function getSignatureString()
    {
        return $this->signature_string;
    }

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setUser(User $user)
	{
		$this->user = $user;
	}

	public function getUser()
	{
		return $this->user;
	}
}

