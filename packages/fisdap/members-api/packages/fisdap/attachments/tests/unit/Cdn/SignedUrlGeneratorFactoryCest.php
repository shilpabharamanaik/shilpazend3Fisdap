<?php

use Fisdap\Attachments\Cdn\SignedUrlGenerator;
use Fisdap\Attachments\Cdn\SignedUrlGeneratorFactory;
use Fisdap\Attachments\Cdn\UnsupportedCdn;
use Illuminate\Container\Container;

class SignedUrlGeneratorFactoryCest
{
    /**
     * @var Container|Mockery\Mock
     */
    private $containerMock;

    /**
     * @var SignedUrlGeneratorFactory
     */
    private $signedUrlGeneratorFactory;


    public function _before(UnitTester $I)
    {
        $this->containerMock = Mockery::mock(Container::class);
        $this->signedUrlGeneratorFactory = new SignedUrlGeneratorFactory($this->containerMock);
    }

    public function _after(UnitTester $I)
    {
    }


    public function it_can_create_a_cloudfront_signed_url_generator(UnitTester $I)
    {
        // arrange
        $signedUrlGeneratorMock = Mockery::mock(SignedUrlGenerator::class);
        $this->containerMock->shouldReceive('make')->once()->andReturn($signedUrlGeneratorMock);

        // act
        $actualSignedUrlGenerator = $this->signedUrlGeneratorFactory->create('cloudfront');

        // assert
        $I->assertSame($signedUrlGeneratorMock, $actualSignedUrlGenerator);
    }


    public function it_can_create_a_local_signed_url_generator(UnitTester $I)
    {
        // arrange
        $signedUrlGeneratorMock = Mockery::mock(SignedUrlGenerator::class);
        $this->containerMock->shouldReceive('make')->once()->andReturn($signedUrlGeneratorMock);

        // act
        $actualSignedUrlGenerator = $this->signedUrlGeneratorFactory->create('local');

        // assert
        $I->assertSame($signedUrlGeneratorMock, $actualSignedUrlGenerator);
    }


    public function it_throws_an_exception_when_an_unsupported_cdn_is_requested(UnitTester $I)
    {
        // assert
        $I->assertTrue(
            $I->seeExceptionThrown(UnsupportedCdn::class, function () {
                // act
                $this->signedUrlGeneratorFactory->create('foo');
            })
        );
    }
}
