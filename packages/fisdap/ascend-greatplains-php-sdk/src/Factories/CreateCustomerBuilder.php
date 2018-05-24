<?php namespace Fisdap\Ascend\Greatplains\Factories;

use Fisdap\Ascend\Greatplains\Collections\AddressCollection;
use Fisdap\Ascend\Greatplains\Collections\InternetAddressCollection;
use Fisdap\Ascend\Greatplains\Contracts\Address as AddressInterface;
use Fisdap\Ascend\Greatplains\Contracts\Phone as PhoneInterface;
use Fisdap\Ascend\Greatplains\Contracts\Factories\CreateCustomerBuilder as CreateCustomerBuilderInterface;
use Fisdap\Ascend\Greatplains\Models\Address;
use Fisdap\Ascend\Greatplains\Models\Customer;
use Fisdap\Ascend\Greatplains\Models\InternetAddress;
use Fisdap\Ascend\Greatplains\Models\Phone;

/**
 * Class CreateCustomerBuilder
 *
 * Build a new customer and return an entity
 *
 * @package Fisdap\Ascend\Greatplains\Factories
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class CreateCustomerBuilder implements CreateCustomerBuilderInterface
{
    /**
     * ID of customer
     *
     * @var string
     */
    private $id;

    /**
     * Name of customer
     *
     * @var string
     */
    private $name;

    /**
     * Array of addresses
     *
     * @var array
     */
    private $addresses;

    /**
     * CreateCustomerBuilder constructor.
     *
     * @param $id
     * @param $name
     * @param array $addresses
     */
    public function __construct($id, $name, $addresses = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->addresses = $addresses;
    }

    /**
     * Build and return a customer entity
     *
     * @return Customer
     */
    public function buildCustomerEntity()
    {
        return new Customer(
            $this->id,
            $this->name,
            $this->buildAddressCollection()
        );
    }

    /**
     * Build the internet address collection
     *
     * @param array $internetAddresses
     * @return InternetAddressCollection
     */
    protected function buildInternetAddressCollection($internetAddresses = [])
    {
        $internetAddressCollection = new InternetAddressCollection();

        if (!empty($internetAddresses)) {
            foreach ($internetAddresses as $key => $value) {
                $internetAddressCollection->append(new InternetAddress($key, $value));
            }
        }

        return $internetAddressCollection;
    }

    /**
     * Build an address collection
     *
     * @return AddressCollection
     */
    protected function buildAddressCollection()
    {
        $addressCollection = new AddressCollection();

        foreach ($this->addresses as $address) {

            $address = new Address(
                $address[AddressInterface::ID_FIELD],
                $address[AddressInterface::LINE_1_FIELD],
                $address[AddressInterface::LINE_2_FIELD],
                $address[AddressInterface::LINE_3_FIELD],
                $address[AddressInterface::CITY_FIELD],
                $address[AddressInterface::STATE_FIELD],
                $address[AddressInterface::POSTAL_CODE_FIELD],
                $address[AddressInterface::COUNTRY_REGION_FIELD],
                $this->buildInternetAddressCollection($address[AddressInterface::INTERNET_ADDRESSES_FIELD]),
                $address[AddressInterface::CONTACT_PERSON_FIELD],
                $this->buildPhoneObject($address[AddressInterface::PHONE_1_FIELD]),
                $this->buildPhoneObject($address[AddressInterface::PHONE_2_FIELD]),
                $this->buildPhoneObject($address[AddressInterface::PHONE_3_FIELD]),
                $this->buildPhoneObject($address[AddressInterface::FAX_FIELD])
            );

            $addressCollection->append($address);
        }
        return $addressCollection;
    }

    /**
     * Build phone object
     *
     * @param null $data
     * @return Phone|null
     */
    protected function buildPhoneObject($data = null)
    {
        return (is_array($data)) ? new Phone(
            $data[PhoneInterface::VALUE_FIELD],
            $data[PhoneInterface::COUNTRY_CODE_FIELD],
            $data[PhoneInterface::EXTENSION_FIELD]
        ) : null;
    }
}
