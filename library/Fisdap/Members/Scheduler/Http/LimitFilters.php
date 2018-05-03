<?php namespace Fisdap\Members\Scheduler\Http;

use Fisdap\Entity\User;


/**
 * Trait LimitFilters
 *
 * @package Fisdap\Members\Scheduler\Http
 */
trait LimitFilters
{
    /**
     * This function will limit the given filter set if the user doesn't have appropriate permissions
     *
     * @param array $filters a filter set the user is trying to use
     *
     * @return array filters the now limited filter set
     */
    protected function limitFilters($filters)
    {
        $user = User::getLoggedInUser();

        // silly js/php boolean issues
        if ($filters['avail_open_window'] == 'false' || $filters['avail_open_window'] == false) {
            $filters['avail_open_window'] = false;
        }
        if ($filters['avail_open_window'] == 'true' || $filters['avail_open_window'] == true) {
            $filters['avail_open_window'] = true;
        }

        if ($user->getCurrentRoleName() != 'instructor') {
            // student must always be restricted to the following:
            $userContext = $user->getCurrentUserContext();
            $userCertLevel = $userContext->certification_level->id;
            $filters['avail_open_window'] = true;
            $filters["avail_certs"] = array($userCertLevel);
            $filters["avail_groups"] = 'all';

            if (!$userContext->program->program_settings->student_view_full_calendar) {
                $filters["chosen_students"] = array($userContext->id);
            }
        }

        if ( ! is_array($filters['chosen_students']) || is_null($filters['chosen_students'])
            || empty($filters['chosen_students'])
        ) {
            $filters['chosen_students'] = 'all';
        }
        if (!is_array($filters['avail_certs'])) {
            $filters['avail_certs'] = 'all';
        }
        if (!is_array($filters['avail_groups'])) {
            $filters['avail_groups'] = 'all';
        }

        return $filters;
    }
}