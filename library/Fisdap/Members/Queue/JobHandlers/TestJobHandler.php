<?php namespace Fisdap\Members\Queue\JobHandlers;

/**
 * Class TestJobHandler
 *
 * @package Fisdap\Members\Queue\JobHandlers
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class TestJobHandler
{
    public function fire($job, $data)
    {
        mail('bgetsug@fisdap.net', 'Job processed!', print_r($data, true), 'From: fisdap-robot@fisdap.net');
    }
}