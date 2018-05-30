<?php namespace Fisdap\Api\Jobs;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\SelfHandling;

/**
 * Class Job
 * @package Fisdap\Api\Jobs
 * @author  Isaac White <iwhite@fisdap.net>
 *
 * TODO: SelfHandling is deprecated
 */
abstract class Job implements SelfHandling
{
    use Queueable;

    protected $em;

    public function __construct(EntityManagerInterface $em = null)
    {
        $this->em = $em;
    }

    /**
     * This finds a resource parameter for a given repo. You will
     * be informed why you are no longer loved if the resource cannot
     * be found.
     *
     * @param Repository $repository
     * @param string $resourceParameter
     * @param bool $required
     *
     * @return EntityBaseClass
     */
    protected function validResource($repository, $resourceParameter, $required = true)
    {
        $resource = $repository->find($this->$resourceParameter);

        if (empty($resource) && $required) {
            $param = $this->$resourceParameter; // Need to get the id from the object.
            throw new ResourceNotFound("No resource found for $resourceParameter with id: $param");
        }

        return $resource;
    }

    /**
     * This finds a resource parameter for a given EntityBaseClass and id.
     *
     * @param string $entityClass
     * @param integer $resourceParameter|null
     * @param bool $required
     *
     * @return EntityBaseClass|null
     */
    protected function validResourceEntityManager($entityClass, $resourceParameter, $required = false)
    {
        if (!$required && $resourceParameter == null) {
            return null;
        }

        if (!$resourceParameter) {
            throw new ResourceNotFound("No resource found for $entityClass.");
        }

        $resource = $this->em->getRepository($entityClass)->find($resourceParameter);
        
        if (empty($resource) && $required) {
            throw new ResourceNotFound("No resource found for $entityClass with id '$resourceParameter'.");
        }

        return $resource;
    }
}
