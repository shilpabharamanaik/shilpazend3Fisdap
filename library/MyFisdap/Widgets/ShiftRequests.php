<?php

class MyFisdap_Widgets_ShiftRequests extends MyFisdap_Widgets_Base
{
    //protected $registeredCallbacks = array('updateClassSection');

    public function render()
    {
        $user = \Fisdap\Entity\User::getLoggedInUser();
		$currentContext = $user->getCurrentUserContext();

        if ($currentContext->isInstructor()) {
            $pendingRequestCount = \Fisdap\EntityUtils::getRepository("ShiftRequest")->getPendingRequestCountByProgram($currentContext->getProgram()->getId());
        } else {
            $pendingRequestCount = \Fisdap\EntityUtils::getRepository("ShiftRequest")->getPendingRequestCountByOwner($currentContext->id);
        }

        if ($pendingRequestCount > 1) {
            $html = "<a href='/scheduler/requests/'>You have " . $pendingRequestCount . " pending shift requests.</a>";
        } else if ($pendingRequestCount == 1) {
            $html = "<a href='/scheduler/requests/'>You have 1 pending shift request.</a>";
        } else {
            $html = "You have no pending shift requests.";
        }

        return $html;
    }

    public function getDefaultData()
    {
        return array("classSection" => null);
    }

    public static function userCanUseWidget($widgetId)
    {
        $currentContext = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $widgetId)->user->getCurrentUserContext();

        if (!$currentContext->isInstructor() && $currentContext->getPrimarySerialNumber()->hasScheduler()) {
            return true;
        }

        // User must be an instructor that has the "Edit Scheduler" permission...
        if ($currentContext->isInstructor() &&
            (   $currentContext->hasPermission('Edit Field Schedules') ||
                $currentContext->hasPermission('Edit Clinic Schedules') ||
                $currentContext->hasPermission('Edit Lab Schedules'))) {
            return true;
        }

        return false;
    }
}
