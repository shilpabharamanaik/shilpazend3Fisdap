<?php namespace Fisdap\Ascend\Greatplains\Contracts;

/**
 * Interface HasPhoneNumbers
 *
 * Representing an object that has phone numbers
 *
 * @package Fisdap\Ascend\Greatplains\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface HasPhoneNumbers
{
    const PHONE_1_FIELD = 'Phone1';
    const PHONE_2_FIELD = 'Phone2';
    const PHONE_3_FIELD = 'Phone3';
    const FAX_FIELD = 'Fax';

    /**
     * Get first phone number
     *
     * @return Phone|null
     */
    public function getPhone1();

    /**
     * Get second phone number
     *
     * @return Phone|null
     */
    public function getPhone2();

    /**
     * Get third phone number
     *
     * @return Phone|null
     */
    public function getPhone3();

    /**
     * Get fax number
     *
     * @return Phone|null
     */
    public function getFax();
}
