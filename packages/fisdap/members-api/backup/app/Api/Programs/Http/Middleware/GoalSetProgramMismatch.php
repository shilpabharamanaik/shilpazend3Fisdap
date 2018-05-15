<?php namespace Fisdap\Api\Programs\Http\Middleware;

use Closure;
use Fisdap\Api\Programs\Http\Exceptions\GoalSetIdProgramIdMismatch;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Data\Goal\GoalRepository;
use Fisdap\Entity\GoalSet;

;
use Fisdap\Entity\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;

/**
 * Ensures provided GoalSet belongs to program or is one of the three defaults.
 *
 * @package Fisdap\Api\Programs\Http\Middleware
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class GoalSetProgramMismatch
{
    /**
     * @var User|null
     */
    private $user;

    /**
     * @var GoalRepository
     */
    private $goalRepository;


    /**
     * @param AuthManager $auth
     * @param GoalRepository $goalRepository
     */
    public function __construct(AuthManager $auth, GoalRepository $goalRepository)
    {
        $this->user = $auth->guard()->user();
        $this->goalRepository = $goalRepository;
    }


    /**
     * @param Request $request
     * @param Closure $next
     *
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $programId = $request->route()->getParameter('programId');
        $goalSetId = $request->query()['goalSetId'];

        /** @var GoalSet $goalSet */
        $goalSet = $this->goalRepository->getGoalSetById($goalSetId);

        if ($goalSet) {
            if ($goalSet->program->getId() > 0 && $goalSet->program->getId() != $programId) {
                throw new GoalSetIdProgramIdMismatch('GoalSet provided does not belong to program.');
            }
        } else {
            throw new ResourceNotFound("GoalSetId {$goalSetId} not found.");
        }


        return $next($request);
    }
}
