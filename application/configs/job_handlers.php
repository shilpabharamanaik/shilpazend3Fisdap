<?php

use Fisdap\Members\Queue\JobHandlers\RunReport;
use Fisdap\Members\Queue\JobHandlers\SaveRequirement;
use Fisdap\Members\Queue\JobHandlers\SaveRequirementAssignModal;
use Fisdap\Members\Queue\JobHandlers\TestJobHandler;
use Fisdap\Members\Queue\JobHandlers\UpdateCompliance;


/*
 * This file should return an associative array mapping fully-qualified class names for job handlers
 * to a short name, which can be resolved by the IoC container.
 */
return [
    TestJobHandler::class             => 'TestJobHandler',
    RunReport::class                  => 'RunReport',
    SaveRequirement::class            => 'SaveRequirement',
    SaveRequirementAssignModal::class => 'SaveRequirementAssignModal',
    UpdateCompliance::class           => 'UpdateCompliance'
];