<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Models;

use Fisdap\Ascend\Greatplains\Models\InternetAddress;
use Fisdap\Ascend\Greatplains\Contracts\InternetAddress as InternetAddressInterface;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class InternetAddressTest
 *
 * Tests for the internet address class
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Models
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class InternetAddressTest extends TestCase
{
    /**
     * Test internet address class has correct contracts
     */
    public function testInternetAddressHasCorrectContracts()
    {
        $email = new InternetAddress('EmailBccAddress', 'jmichels@fisdap.net');
        $this->assertInstanceOf(InternetAddressInterface::class, $email);
    }

    /**
     * Test internet address has correct fields
     */
    public function testInternetAddressHasCorrectFields()
    {
        $this->assertStringMatchesFormat(InternetAddressInterface::ADDITIONAL_INFORMATION_TYPE, 'AdditionalInformation');
        $this->assertStringMatchesFormat(InternetAddressInterface::EMAIL_BCC_ADDRESS_TYPE, 'EmailBccAddress');
        $this->assertStringMatchesFormat(InternetAddressInterface::EMAIL_CC_ADDRESS_TYPE, 'EmailCcAddress');
        $this->assertStringMatchesFormat(InternetAddressInterface::EMAIL_TO_ADDRESS_TYPE, 'EmailToAddress');
        $this->assertStringMatchesFormat(InternetAddressInterface::INTERNET_FIELD_1_TYPE, 'InternetField1');
        $this->assertStringMatchesFormat(InternetAddressInterface::INTERNET_FIELD_2_TYPE, 'InternetField2');
        $this->assertStringMatchesFormat(InternetAddressInterface::INTERNET_FIELD_3_TYPE, 'InternetField3');
        $this->assertStringMatchesFormat(InternetAddressInterface::INTERNET_FIELD_4_TYPE, 'InternetField4');
        $this->assertStringMatchesFormat(InternetAddressInterface::INTERNET_FIELD_5_TYPE, 'InternetField5');
        $this->assertStringMatchesFormat(InternetAddressInterface::INTERNET_FIELD_6_TYPE, 'InternetField6');
        $this->assertStringMatchesFormat(InternetAddressInterface::INTERNET_FIELD_7_TYPE, 'InternetField7');
        $this->assertStringMatchesFormat(InternetAddressInterface::INTERNET_FIELD_8_TYPE, 'InternetField8');
        $this->assertStringMatchesFormat(InternetAddressInterface::MESSENGER_ADDRESS_TYPE, 'MessengerAddress');
    }

    /**
     * Test class has correct getters and can be created correctly
     */
    public function testClassHasCorrectGettersAndCanBeCreatedCorrectly()
    {
        $email = new InternetAddress('EmailBccAddress', 'jmichels@fisdap.net');

        $this->assertStringMatchesFormat('EmailBccAddress', $email->getType());
        $this->assertStringMatchesFormat('jmichels@fisdap.net', $email->getValue());
    }

    /**
     * Test internet address can fail with incorrect data
     *
     * @expectedException \Fisdap\Ascend\Greatplains\Exceptions\InvalidArgumentException
     */
    public function testInternetAddressCanFailWithIncorrectData()
    {
        $email = new InternetAddress('email', 'jmichels@fisdap.net');
    }
}
