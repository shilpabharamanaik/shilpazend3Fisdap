<?php namespace Fisdap\Members\Commerce\Services;

use Fisdap\Ascend\Greatplains\Contracts\Address;
use Fisdap\Ascend\Greatplains\Contracts\InternetAddress;
use Fisdap\Ascend\Greatplains\Contracts\Phone;
use Fisdap\Ascend\Greatplains\Factories\CreateCustomerBuilder;
use Fisdap\Entity\ProgramLegacy;

/**
 * Class CustomerTransformer
 * @package Fisdap\Members\Commerce\Services
 * @author Sam Tape <stape@fisdap.net>
 */
class CustomerTransformer
{
    /**
     * @param ProgramLegacy $program
     * @return CreateCustomerBuilder
     */
    public function transformToGPCustomer(ProgramLegacy $program)
    {
        $addresses = [
            [
                Address::LINE_1_FIELD             => $program->billing_address,
                Address::LINE_2_FIELD             => $program->billing_address2,
                Address::LINE_3_FIELD             => $program->billing_address3,
                Address::CITY_FIELD               => $program->billing_city,
                Address::STATE_FIELD              => $program->billing_state,
                Address::POSTAL_CODE_FIELD        => $program->billing_zip,
                Address::COUNTRY_REGION_FIELD     => $program->billing_country,
                Address::INTERNET_ADDRESSES_FIELD => $this->getInternetAddresses($program),
                Address::CONTACT_PERSON_FIELD     => $program->billing_contact,
                Address::PHONE_1_FIELD            => [Phone::VALUE_FIELD => $program->billing_phone, Phone::COUNTRY_CODE_FIELD => null, Phone::EXTENSION_FIELD => null],
                Address::PHONE_2_FIELD            => null,
                Address::PHONE_3_FIELD            => null,
                Address::FAX_FIELD                => $this->getFaxNumber($program),
            ]
        ];

        $customerBuilder = new CreateCustomerBuilder($program->customer_id, $program->name, $addresses);

        return $customerBuilder;
    }

    /**
     * Get the fax number after doing necessary checks
     *
     * @param ProgramLegacy $program
     * @return array|null
     */
    protected function getFaxNumber(ProgramLegacy $program)
    {
        $fax = null;

        if ($this->isNotEmptyAndNotUnspecified($program->billing_fax)) {
            $fax = [Phone::VALUE_FIELD => $program->billing_fax, Phone::COUNTRY_CODE_FIELD => null, Phone::EXTENSION_FIELD => null];
        }

        return $fax;
    }

    /**
     * Get internet addresses after doing necessary checks for validity
     *
     * @param ProgramLegacy $program
     * @return array
     */
    protected function getInternetAddresses(ProgramLegacy $program)
    {
        $internetAddresses = [
            InternetAddress::EMAIL_TO_ADDRESS_TYPE  => $program->billing_email
        ];

        if ($this->isNotEmptyAndNotUnspecified($program->contact_email)) {
            $internetAddresses[InternetAddress::EMAIL_CC_ADDRESS_TYPE] = $program->contact_email;
        }

        return $internetAddresses;
    }

    /**
     * Check to make sure a value is not empty and is not 'unspecified'
     *
     * @param string $value
     * @return bool
     */
    protected function isNotEmptyAndNotUnspecified($value)
    {
        return (!empty($value) && $value != 'Unspecified') ? true : false;
    }
}
