<?php namespace Fisdap\Api\Shifts\Signatures\Job;


use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\Signature;
use Swagger\Annotations as SWG;

final class CreateSignature extends Job implements RequestHydrated
{
    public $signatureString;

    public $name;
    
    protected $userId;

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function handle(
        UserRepository $userRepository
    )
    {
        $signature = new Signature;

        $user = $this->validResource($userRepository, 'userId');
        $signature->setUser($user);
        $signature->setName($this->name);
        $this->signatureString = utf8_encode($this->signatureString);
        $signature->setSignatureString($this->signatureString);

        $signature->save();
        $signature->signature_string = gzuncompress($signature->signature_string);
        return $signature;
    }
    
    public function rules() 
    {
        return [
            'signatureString' => 'required|string',
            'name' => 'required|string'
        ];
    }

    /**
     * @return array
     * TODO: Finish this.
     */
    public function toArray()
    {
        return array();
    }

}

