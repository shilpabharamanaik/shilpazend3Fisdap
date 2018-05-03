<?php namespace Fisdap\Ascend\Greatplains\Contracts;

use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;

/**
 * Interface Phone
 *
 * Representation of a phone
 *
 * @package Fisdap\Ascend\Greatplains\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface Phone extends Arrayable
{
    const VALUE_FIELD = 'Value';
    const COUNTRY_CODE_FIELD = 'CountryCode';
    const EXTENSION_FIELD = 'Extension';
    const HAS_VALUE_FIELD = 'HasValue';

    /**
     * Get phone value
     *
     * @return string
     */
    public function getValue();

    /**
     * Get country code
     *
     * @return int
     */
    public function getCountryCode();

    /**
     * Get the extension if exists
     *
     * @return null|string
     */
    public function getExtension();

    /**
     * Get if the object has value
     *
     * @return bool
     */
    public function getHasValue();
}
