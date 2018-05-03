<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Models;

use Fisdap\Ascend\Greatplains\Models\Phone;
use Fisdap\Ascend\Greatplains\Contracts\Phone as PhoneInterface;
use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class PhoneTest
 *
 * Tests for phone model
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Models
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class PhoneTest extends TestCase
{
    /**
     * Test phone has correct contracts
     */
    public function testPhoneHasCorrectContracts()
    {
        $phone = new Phone('5154940511');
        $this->assertInstanceOf(PhoneInterface::class, $phone);
        $this->assertInstanceOf(Arrayable::class, $phone);
    }

    /**
     * Test phone has correct fields
     */
    public function testPhoneHasCorrectFields()
    {
        $this->assertStringMatchesFormat(PhoneInterface::VALUE_FIELD, 'Value');
        $this->assertStringMatchesFormat(PhoneInterface::COUNTRY_CODE_FIELD, 'CountryCode');
        $this->assertStringMatchesFormat(PhoneInterface::EXTENSION_FIELD, 'Extension');
        $this->assertStringMatchesFormat(PhoneInterface::HAS_VALUE_FIELD, 'HasValue');
    }

    /**
     * Test phone has correct getters
     */
    public function testPhoneHasCorrectGetters()
    {
        $phone = new Phone('5154940511', 1, '123');
        $phone1 = new Phone('515-494-0511', 1, '123');
        $phone2 = new Phone('(515) 494-0511', 1, '123');
        $phone3 = new Phone('(515)494-0511', 1, '123');
        $phone4 = new Phone('+5154940511', 1, '123');
        $phone5 = new Phone('515-494-0511x1234', 1, '123456');
        $phone6 = new Phone('515-555-0511 x1234', 1, '123456');
        $phone7 = new Phone('515-555-0511 x1234', 1);

        $this->assertStringMatchesFormat('5154940511', $phone->getValue());
        $this->assertStringMatchesFormat('5154940511', $phone1->getValue());
        $this->assertStringMatchesFormat('5154940511', $phone2->getValue());
        $this->assertStringMatchesFormat('5154940511', $phone3->getValue());
        $this->assertStringMatchesFormat('5154940511', $phone4->getValue());
        $this->assertStringMatchesFormat('5154940511', $phone5->getValue());
        $this->assertStringMatchesFormat('5155550511', $phone6->getValue());

        $this->assertStringMatchesFormat('123456', $phone5->getExtension());
        $this->assertStringMatchesFormat('123456', $phone6->getExtension());
        $this->assertStringMatchesFormat('1234', $phone7->getExtension());

        $this->assertEquals(1, $phone->getCountryCode());
        $this->assertStringMatchesFormat('123', $phone->getExtension());
        $this->assertTrue($phone->getHasValue());
    }

    /**
     * Test phone class has correct to array
     */
    public function testClassHasCorrectToArrayFunction()
    {
        $phone = new Phone('5154940511', 1, '123');
        $array = $phone->toArray();

        $this->assertArrayHasKey('Value', $array);
        $this->assertArrayHasKey('CountryCode', $array);
        $this->assertArrayHasKey('Extension', $array);
        $this->assertArrayHasKey('HasValue', $array);
    }

    /**
     * Test phone may not have value
     */
    public function testPhoneMayNotHaveValue()
    {
        $phone = new Phone(null);
        $this->assertFalse($phone->getHasValue());
    }
}
