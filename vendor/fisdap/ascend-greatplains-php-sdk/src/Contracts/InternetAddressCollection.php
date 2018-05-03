<?php namespace Fisdap\Ascend\Greatplains\Contracts;

use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;

/**
 * Interface InternetAddressCollection
 *
 * InternetAddress collection to store internet address objects
 *
 * @package Fisdap\Ascend\Greatplains\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface InternetAddressCollection extends Arrayable
{
    /**
     * Add a new internet address to the collection
     *
     * @param InternetAddress $internetAddress
     * @return InternetAddressCollection
     */
    public function append(InternetAddress $internetAddress);
}
