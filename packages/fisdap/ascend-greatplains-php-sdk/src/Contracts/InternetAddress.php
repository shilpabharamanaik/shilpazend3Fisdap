<?php namespace Fisdap\Ascend\Greatplains\Contracts;

/**
 * Interface InternetAddress
 *
 * Properties to represent an individual internet address
 *
 * @package Fisdap\Ascend\Greatplains\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface InternetAddress
{
    const ADDITIONAL_INFORMATION_TYPE = 'AdditionalInformation';
    const EMAIL_BCC_ADDRESS_TYPE = 'EmailBccAddress';
    const EMAIL_CC_ADDRESS_TYPE = 'EmailCcAddress';
    const EMAIL_TO_ADDRESS_TYPE = 'EmailToAddress';
    const INTERNET_FIELD_1_TYPE = 'InternetField1';
    const INTERNET_FIELD_2_TYPE = 'InternetField2';
    const INTERNET_FIELD_3_TYPE = 'InternetField3';
    const INTERNET_FIELD_4_TYPE = 'InternetField4';
    const INTERNET_FIELD_5_TYPE = 'InternetField5';
    const INTERNET_FIELD_6_TYPE = 'InternetField6';
    const INTERNET_FIELD_7_TYPE = 'InternetField7';
    const INTERNET_FIELD_8_TYPE = 'InternetField8';
    const MESSENGER_ADDRESS_TYPE = 'MessengerAddress';

    /**
     * Get internet address type
     *
     * @return string
     */
    public function getType();

    /**
     * Get the internet address value
     *
     * @return string
     */
    public function getValue();
}
