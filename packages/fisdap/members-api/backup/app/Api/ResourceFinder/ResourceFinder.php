<?php namespace Fisdap\Api\ResourceFinder;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Queries\Specifications\ById;
use Fisdap\Data\Repository\Repository;
use Fisdap\Queries\Specifications\CommonSpec;
use Happyr\DoctrineSpecification\Spec;


/**
 * Template for a resource finder
 *
 * @package Fisdap\Api\ResourceFinder
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class ResourceFinder implements FindsResources
{
    /**
     * @var Repository
     */
    protected $repository;


    /**
     * @inheritdoc
     */
    public function findById($id, array $associations = null, array $associationIds = null, $asArray = false) {
        $spec = CommonSpec::makeSpecWithAssociations($associations, $associationIds);
        $spec->andX(new ById($id));

        $resource = $this->repository->match($spec, $asArray ? Spec::asArray() : null);

        if (empty($resource)) {
            throw new ResourceNotFound("No resource found with ID '$id'");
        }

        return array_shift($resource);
    }
}