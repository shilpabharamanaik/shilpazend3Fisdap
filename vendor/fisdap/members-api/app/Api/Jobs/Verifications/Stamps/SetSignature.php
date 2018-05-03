<?php namespace Fisdap\Api\Jobs\Verifications\Stamps;


use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Entity\Signature;
use Swagger\Annotations as SWG;

/**
 * Class SetSignature
 * @package Fisdap\Api\Jobs\Verifications\Stamps
 * @author  Isaac White <isaac.white@ascendlearning.com>
 *
 * @SWG\Definition(
 *     definition="Signature",
 *     required={ "type" }
 * )
 */
final class SetSignature extends Job implements RequestHydrated
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     * @SWG\Property(type="string", description="This is the user's signature")
     */
    public $signature_string;

    /**
     * @var string
     * @SWG\Property(type="string", description="This is the name of the person who signed")
     */
    public $name;

    /**
     * @return Signature
     */
    public function handle()
    {
        $signature = new Signature;
        
        $signature->setSignatureString($this->signature_string);
        $signature->setName($this->name);
        
        return $signature;
    }

}

