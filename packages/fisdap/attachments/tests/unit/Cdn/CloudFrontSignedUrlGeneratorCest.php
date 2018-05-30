<?php

use Aws\CloudFront\CloudFrontClient;
use Fisdap\Attachments\Cdn\CloudFrontSignedUrlGenerator;
use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;

class CloudFrontSignedUrlGeneratorCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }


    public function it_can_generate_a_signed_url(UnitTester $I)
    {
        // arrange
        /** @var \Aws\Sdk|Mockery\Mock $awsSdk */
        $awsSdk = Mockery::mock(Aws\Sdk::class);

        /** @var CloudFrontClient|Mockery\Mock $cloudFrontMock */
        $cloudFrontMock = Mockery::mock(CloudFrontClient::class);

        $awsSdk->shouldReceive('createCloudFront')->once()->andReturn($cloudFrontMock);

        /** @var AttachmentsKernel|Mockery\Mock $attachmentsKernelMock */
        $attachmentsKernelMock = Mockery::mock(AttachmentsKernel::class);
        $attachmentsKernelMock->shouldReceive('getConfigProvider->get')->times(3);

        $cloudFrontMock->shouldReceive('getSignedUrl')->once()->andReturn('http://example.com/notReallySigned');

        $cloudFrontSignedUrlGenerator = new CloudFrontSignedUrlGenerator($awsSdk, $attachmentsKernelMock);

        // act
        $signedUrl = $cloudFrontSignedUrlGenerator->generate('http://example.com/foo/bar/baz.nuts');

        // assert
        $I->assertTrue(is_string($signedUrl));
    }
}
