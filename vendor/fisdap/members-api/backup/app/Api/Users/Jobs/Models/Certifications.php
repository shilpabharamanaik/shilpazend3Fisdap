<?php namespace Fisdap\Api\Users\Jobs\Models;

use Swagger\Annotations as SWG;


/**
 * Class Certifications
 *
 * @package Fisdap\Api\Users\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 *
 * @SWG\Definition(definition="UserCertifications")
 */
class Certifications
{
    /**
     * @var bool
     * @SWG\Property
     */
    public $emtGrad = false;

    /**
     * @var \DateTime|null
     * @SWG\Property(type="string", format="dateTime")
     */
    public $emtGradDate = null;

    /**
     * @var bool
     * @SWG\Property
     */
    public $emtCert = false;

    /**
     * @var \DateTime|null
     * @SWG\Property(type="string", format="dateTime")
     */
    public $emtCertDate = null;


    /**
     * Certifications constructor.
     *
     * @param bool $emtGrad
     * @param \DateTime|null $emtGradDate
     * @param bool $emtCert
     * @param \DateTime|null $emtCertDate
     */
    public function __construct(
        $emtGrad = false,
        \DateTime $emtGradDate = null,
        $emtCert = false,
        \DateTime $emtCertDate = null
    ) {
        $this->emtGrad = $emtGrad;
        $this->emtGradDate = $emtGradDate;
        $this->emtCert = $emtCert;
        $this->emtCertDate = $emtCertDate;
    }
}