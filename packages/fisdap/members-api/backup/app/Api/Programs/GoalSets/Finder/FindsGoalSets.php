<?php namespace Fisdap\Api\Programs\GoalSets\Finder;

use Fisdap\Api\ResourceFinder\FindsResources;

/**
 * Contract for retrieving goal-sets
 *
 * @package Fisdap\Api\Programs\GoalSets\Finder
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
interface FindsGoalSets extends FindsResources
{
    /**
     * @param $programId
     * @return array
     */
    public function findProgramGoalSets($programId);
}