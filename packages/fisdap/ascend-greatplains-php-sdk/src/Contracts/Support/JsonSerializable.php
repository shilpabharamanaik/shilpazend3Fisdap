<?php namespace Fisdap\Ascend\Greatplains\Contracts\Support;

/**
 * Interface JsonSerializable
 *
 * Object can return json
 *
 * @package Fisdap\Ascend\Greatplains\Contracts\Support
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface JsonSerializable
{
    /**
     * Return json representation
     *
     * @return string
     */
    public function toJson();
}
