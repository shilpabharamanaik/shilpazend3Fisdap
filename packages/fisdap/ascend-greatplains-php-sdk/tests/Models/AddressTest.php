<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Models;

use Fisdap\Ascend\Greatplains\Models\Address;
use Fisdap\Ascend\Greatplains\Contracts\Address as AddressInterface;
use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;
use Fisdap\Ascend\Greatplains\Contracts\HasPhoneNumbers;
use Fisdap\Ascend\Greatplains\Contracts\HasInternetAddresses;
use Fisdap\Ascend\Greatplains\Contracts\InternetAddressCollection;
use Fisdap\Ascend\Greatplains\Contracts\Phone;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class AddressTest
 *
 * Tests for address model
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Models
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class AddressTest extends TestCase
{
    /**
     * @var Address
     */
    protected $address;

    /**
     * Get address to use in other tests
     *
     * @return Address
     */
    protected function getAddress()
    {
        if (!$this->address) {
            $internetAddresses = mockery::mock(InternetAddressCollection::class);
            $internetAddresses->shouldReceive('toArray')->andReturn(['EmailToAddress' => 'test@test.com']);

            $phone1 = mockery::mock(Phone::class);
            $phone1->shouldReceive('toArray')->andReturn([]);
            $phone2 = mockery::mock(Phone::class);
            $phone2->shouldReceive('toArray')->andReturn([]);
            $phone3 = mockery::mock(Phone::class);
            $phone3->shouldReceive('toArray')->andReturn([]);
            $fax = mockery::mock(Phone::class);
            $fax->shouldReceive('toArray')->andReturn([]);

            $this->address = new Address(
                'id',
                'line1',
                'line2',
                'line3',
                'minneapolis',
                'mn',
                '55410',
                'US',
                $internetAddresses,
                'Jason',
                $phone1,
                $phone2,
                $phone3,
                $fax
            );
        }

        return $this->address;
    }

    /**
     * Test address has correct contracts
     */
    public function testAddressHasCorrectContracts()
    {
        $this->assertInstanceOf(AddressInterface::class, $this->getAddress());
        $this->assertInstanceOf(Arrayable::class, $this->getAddress());
        $this->assertInstanceOf(HasPhoneNumbers::class, $this->getAddress());
        $this->assertInstanceOf(HasInternetAddresses::class, $this->getAddress());
    }

    /**
     * Test address has correct fields
     */
    public function testAddressHasCorrectFields()
    {
        $this->assertStringMatchesFormat('Id', AddressInterface::ID_FIELD);
        $this->assertStringMatchesFormat('Line1', AddressInterface::LINE_1_FIELD);
        $this->assertStringMatchesFormat('Line2', AddressInterface::LINE_2_FIELD);
        $this->assertStringMatchesFormat('Line3', AddressInterface::LINE_3_FIELD);
        $this->assertStringMatchesFormat('City', AddressInterface::CITY_FIELD);
        $this->assertStringMatchesFormat('State', AddressInterface::STATE_FIELD);
        $this->assertStringMatchesFormat('PostalCode', AddressInterface::POSTAL_CODE_FIELD);
        $this->assertStringMatchesFormat('CountryRegion', AddressInterface::COUNTRY_REGION_FIELD);
        $this->assertStringMatchesFormat('InternetAddresses', AddressInterface::INTERNET_ADDRESSES_FIELD);
        $this->assertStringMatchesFormat('ContactPerson', AddressInterface::CONTACT_PERSON_FIELD);
        $this->assertStringMatchesFormat('Phone1', AddressInterface::PHONE_1_FIELD);
        $this->assertStringMatchesFormat('Phone2', AddressInterface::PHONE_2_FIELD);
        $this->assertStringMatchesFormat('Phone3', AddressInterface::PHONE_3_FIELD);
        $this->assertStringMatchesFormat('Fax', AddressInterface::FAX_FIELD);
    }

    /**
     * Test address has correct getters
     */
    public function testAddressHasCorrectGetters()
    {
        $this->assertStringMatchesFormat('id', $this->getAddress()->getId());
        $this->assertStringMatchesFormat('line1', $this->getAddress()->getLine1());
        $this->assertStringMatchesFormat('line2', $this->getAddress()->getLine2());
        $this->assertStringMatchesFormat('line3', $this->getAddress()->getLine3());
        $this->assertStringMatchesFormat('minneapolis', $this->getAddress()->getCity());
        $this->assertStringMatchesFormat('mn', $this->getAddress()->getState());
        $this->assertStringMatchesFormat('55410', $this->getAddress()->getPostalCode());
        $this->assertStringMatchesFormat('US', $this->getAddress()->getCountryRegion());
        $this->assertStringMatchesFormat('Jason', $this->getAddress()->getContactPerson());

        $this->assertInstanceOf(InternetAddressCollection::class, $this->getAddress()->getInternetAddressCollection());
        $this->assertInstanceOf(Phone::class, $this->getAddress()->getPhone1());
        $this->assertInstanceOf(Phone::class, $this->getAddress()->getPhone2());
        $this->assertInstanceOf(Phone::class, $this->getAddress()->getPhone3());
        $this->assertInstanceOf(Phone::class, $this->getAddress()->getFax());
    }

    /**
     * Test can get address as array
     */
    public function testCanGetAddressAsArray()
    {
        $array = $this->getAddress()->toArray();

        $this->assertCount(14, $array);

        $this->assertArrayHasKey(AddressInterface::ID_FIELD, $array);
        $this->assertArrayHasKey(AddressInterface::LINE_1_FIELD, $array);
        $this->assertArrayHasKey(AddressInterface::LINE_2_FIELD, $array);
        $this->assertArrayHasKey(AddressInterface::LINE_3_FIELD, $array);
        $this->assertArrayHasKey(AddressInterface::CITY_FIELD, $array);
        $this->assertArrayHasKey(AddressInterface::STATE_FIELD, $array);
        $this->assertArrayHasKey(AddressInterface::POSTAL_CODE_FIELD, $array);
        $this->assertArrayHasKey(AddressInterface::COUNTRY_REGION_FIELD, $array);
        $this->assertArrayHasKey(AddressInterface::INTERNET_ADDRESSES_FIELD, $array);
        $this->assertArrayHasKey(AddressInterface::CONTACT_PERSON_FIELD, $array);
        $this->assertArrayHasKey(AddressInterface::PHONE_1_FIELD, $array);
        $this->assertArrayHasKey(AddressInterface::PHONE_2_FIELD, $array);
        $this->assertArrayHasKey(AddressInterface::PHONE_3_FIELD, $array);
        $this->assertArrayHasKey(AddressInterface::FAX_FIELD, $array);
    }
}
