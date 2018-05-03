<?php namespace Fisdap\Api\Programs\GoalSets\Finder;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\ResourceFinder\ResourceFinder;
use Fisdap\Data\Goal\GoalRepository;
use Fisdap\Data\Site\SiteLegacyRepository;

/**
 * Service for retrieving goal-sets
 *
 * @package Fisdap\Api\Programs\GoalSets\Finder
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class GoalSetsFinder extends ResourceFinder implements FindsGoalSets
{
    /**
     * @var SiteLegacyRepository
     */
    protected $repository;


    /**
     * @param GoalRepository $repository
     */
    public function __construct(GoalRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * @inheritdoc
     */
    public function findProgramGoalSets($programId)
    {
        $programGoalSets = $this->repository->getProgramGoalSets($programId);

        if (empty($programGoalSets)) {
            throw new ResourceNotFound("No goal-sets found for program");
        }

        return $programGoalSets;
    }
}
