<?php namespace Fisdap\Timestamps;

/**
 * Contract for implementing created and updated timestamps
 *
 * @package Fisdap\Timestamps
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface HasTimestamps
{
    /**
     * @return \DateTime
     */
    public function getCreated();


    /**
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created);


    /**
     * @return \DateTime
     */
    public function getUpdated();


    /**
     * @param \DateTime $updated
     */
    public function setUpdated(\DateTime $updated);
}
