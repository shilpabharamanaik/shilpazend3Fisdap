<?php namespace Fisdap\Api\Users\Jobs\Models;

use Swagger\Annotations as SWG;


/**
 * Class Licenses
 *
 * @package Fisdap\Api\Users\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 *
 * @SWG\Definition(definition="UserLicenses")
 */
class Licenses
{
    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $licenseNumber = null;

    /**
     * @var \DateTime|null
     * @SWG\Property(type="string", format="dateTime")
     */
    public $licenseExpirationDate = null;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $licenseState = null;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $stateLicenseNumber = null;

    /**
     * @var \DateTime|null
     * @SWG\Property(type="string", format="dateTime")
     */
    public $stateLicenseExpirationDate = null;


    /**
     * Licenses constructor.
     *
     * @param null|string    $licenseNumber
     * @param \DateTime|null $licenseExpirationDate
     * @param null|string    $licenseState
     * @param null|string    $stateLicenseNumber
     * @param \DateTime|null $stateLicenseExpirationDate
     */
    public function __construct(
        $licenseNumber = null,
        \DateTime $licenseExpirationDate = null,
        $licenseState = null,
        $stateLicenseNumber = null,
        \DateTime $stateLicenseExpirationDate = null
    ) {
        $this->licenseNumber = $licenseNumber;
        $this->licenseExpirationDate = $licenseExpirationDate;
        $this->licenseState = $licenseState;
        $this->stateLicenseNumber = $stateLicenseNumber;
        $this->stateLicenseExpirationDate = $stateLicenseExpirationDate;
    }
}