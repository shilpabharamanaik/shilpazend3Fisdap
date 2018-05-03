<?php namespace Fisdap\Ascend\Greatplains\Contracts\Support;

/**
 * Interface Arrayable
 *
 * Object can be converted to array
 *
 * @package Fisdap\Ascend\Greatplains\Contracts\Support
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface Arrayable
{
    /**
     * Return object as array
     *
     * @return array
     */
    public function toArray();
}
