<?php namespace Fisdap\AppHealthChecks\HealthChecks;


/**
 * Contract for a health check
 *
 * @package Fisdap\AppHealthChecks\HealthChecks
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface ChecksHealth
{

    const STATUS_SUCCESS = 'OK';
    const STATUS_QUIETFAIL = 'FAILED (QUIET)';
    const STATUS_FAILURE = 'FAILED';
    const STATUS_UNKNOWN = 'UNKNOWN';


    /**
     * @return string
     */
    public function getName();


    /**
     * @return void
     */
    public function check();


    /**
     * @return string
     */
    public function getStatus();


    /**
     * @return float
     */
    public function getRunTime();


    /**
     * @return array
     */
    public function getError();
} 