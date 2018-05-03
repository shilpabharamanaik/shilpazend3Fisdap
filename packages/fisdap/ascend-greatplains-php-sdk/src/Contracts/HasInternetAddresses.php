<?php namespace Fisdap\Ascend\Greatplains\Contracts;

/**
 * Interface HasInternetAddresses
 *
 * Internet addresses interface for model
 *
 * @package Fisdap\Ascend\Greatplains\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface HasInternetAddresses
{
    const INTERNET_ADDRESSES_FIELD = 'InternetAddresses';

    /**
     * Get the internet address collection
     *
     * @return InternetAddressCollection
     */
    public function getInternetAddressCollection();
}
