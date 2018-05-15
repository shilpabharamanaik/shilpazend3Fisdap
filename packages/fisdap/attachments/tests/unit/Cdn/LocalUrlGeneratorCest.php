<?php

use Fisdap\Attachments\Cdn\LocalUrlGenerator;
use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;

class LocalUrlGeneratorCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }


    public function it_can_pass_through_a_url_for_local_testing_purposes(UnitTester $I)
    {
        // arrange
        /** @var AttachmentsKernel|Mockery\Mock $attachmentsKernelMock */
        $attachmentsKernelMock = Mockery::mock(AttachmentsKernel::class);
        $localUrlGenerator = new LocalUrlGenerator($attachmentsKernelMock);

        // act
        $testUrl = 'http://i.am.a/url';
        $generatedUrl = $localUrlGenerator->generate($testUrl);

        // assert
        $I->assertEquals($testUrl, $generatedUrl);
    }
}
