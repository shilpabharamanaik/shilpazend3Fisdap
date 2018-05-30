<?php namespace Fisdap\Api\Users\Entity\Traits;

use Doctrine\ORM\Mapping\Column;

/**
 * Trait Certifications
 *
 * @package Fisdap\Api\Users\Entity\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
trait Certifications
{
    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $emt_grad = false;

    /**
     * @var \DateTime
     * @Column(type="date", nullable=true)
     */
    protected $emt_grad_date;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $emt_cert = false;

    /**
     * @var \DateTime
     * @Column(type="date", nullable=true)
     */
    protected $emt_cert_date;


    /**
     * @return boolean
     */
    public function isEmtGrad()
    {
        return $this->emt_grad;
    }


    /**
     * @return \DateTime
     */
    public function getEmtGradDate()
    {
        return $this->emt_grad_date;
    }


    /**
     * @return boolean
     */
    public function isEmtCert()
    {
        return $this->emt_cert;
    }


    /**
     * @return \DateTime
     */
    public function getEmtCertDate()
    {
        return $this->emt_cert_date;
    }


    /**
     * @param mixed $emt_grad
     */
    public function setEmtGrad($emt_grad)
    {
        $this->emt_grad = $emt_grad;
    }


    /**
     * @param \DateTime $emt_grad_date
     */
    public function setEmtGradDate(\DateTime $emt_grad_date = null)
    {
        $this->emt_grad_date = $emt_grad_date;
    }


    /**
     * @param mixed $emt_cert
     */
    public function setEmtCert($emt_cert)
    {
        $this->emt_cert = $emt_cert;
    }


    /**
     * @param \DateTime $emt_cert_date
     */
    public function setEmtCertDate(\DateTime $emt_cert_date = null)
    {
        $this->emt_cert_date = $emt_cert_date;
    }
}
