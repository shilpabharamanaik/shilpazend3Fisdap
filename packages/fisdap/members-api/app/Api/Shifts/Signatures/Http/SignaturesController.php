<?php namespace Fisdap\Api\Shifts\Signatures\Http;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Shifts\Signatures\Job\CreateSignature;
use Fisdap\Api\Shifts\Signatures\SignaturesTransformer;
use Fisdap\Entity\Signature;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;

final class SignaturesController extends Controller
{
    use ResponseHelpers;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(Manager $fractal, SignaturesTransformer $transformer, EntityManagerInterface $em)
    {
        $this->fractal = $fractal;
        $this->transformer = $transformer;
        $this->em = $em;
    }

    public function index()
    {
        return 5;
    }
    
    public function show($userId, $id)
    {
        return $this->respondWithCollection((array) $this->em->getRepository(Signature::class)->find($id), $this->transformer);
    }
    
    public function store($userId, CreateSignature $createSignatureJob, BusDispatcher $busDispatcher)
    {
        $createSignatureJob->setUserId($userId);
        $signature = $busDispatcher->dispatch($createSignatureJob);
        return $this->respondWithItem($signature, $this->transformer);
    }
    
    public function update()
    {
        
    }
    
    public function destroy()
    {
        
    }
}


