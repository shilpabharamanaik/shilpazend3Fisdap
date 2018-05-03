<?php namespace Fisdap\Api\Users\Finder;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Queries\Specifications\ById;
use Fisdap\Api\Users\Queries\Specifications\UsersWithContextsAndProgram;
use Fisdap\Api\Users\UserContexts\Roles\Students\Queries\Specifications\InstructorStudents;
use Fisdap\Api\Users\UserContexts\Roles\Students\Queries\Specifications\ProgramStudents;
use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\User;
use Fisdap\Queries\Specifications\CommonSpec;
use Happyr\DoctrineSpecification\Spec;


/**
 * Service for retrieving one or more users by various criteria
 *
 * @package Fisdap\Api\Users
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class UsersFinder implements FindsUsers
{
    /**
     * @var UserRepository
     */
    protected $repository;


    /**
     * @param UserRepository $repository
     */
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * @inheritdoc
     */
    public function findById($id, array $associations = null, array $associationIds = null, $asArray = false)
    {
        $spec = CommonSpec::makeSpecWithAssociations($associations, $associationIds);
        $spec->andX(new ById($id));
        $spec->andX(new UsersWithContextsAndProgram);

        $user = $this->repository->match($spec, $asArray ? Spec::asArray() : null);

        if (empty($user)) {
            throw new ResourceNotFound("No User found with id '$id'");
        }

        $user = array_shift($user);

        return $user;
    }


    /**
     * @inheritdoc
     */
    public function findByUserContextId($userContextId)
    {
        $spec = Spec::andX(
            new UsersWithContextsAndProgram,
            Spec::in('id', $userContextId, 'userContexts')
        );

        $user = $this->repository->match($spec);

        if (empty($user)) {
            throw new ResourceNotFound("No User found with userContextId '$userContextId'");
        }

        $user = array_shift($user);

        return $user;
    }


    /**
     * @inheritdoc
     */
    public function findProgramStudents($queryParams)
    {
        return $this->repository->match(new ProgramStudents($queryParams), Spec::asArray());
    }


    /**
     * @inheritdoc
     */
    public function findInstructorStudents($queryParams)
    {
        return $this->repository->match(new InstructorStudents($queryParams), Spec::asArray());
    }


    /**
     * @inheritdoc
     */
    public function findOneByPsgUserId($psgUserId)
    {
        return $this->repository->findOneBy(['psg_user_id' => $psgUserId]);
    }


    /**
     * @inheritdoc
     */
    public function findOneByLtiUserId($ltiUserId)
    {
        return $this->repository->findOneBy(['lti_user_id' => $ltiUserId]);
    }


    /**
     * @inheritdoc
     */
    public function findByEmail($email)
    {
        return $this->repository->findBy(['email' => $email], ['last_name' => 'ASC', 'first_name' => 'ASC']);
    }
}
