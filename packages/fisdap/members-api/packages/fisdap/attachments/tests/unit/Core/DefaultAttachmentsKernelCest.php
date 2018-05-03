<?php

use Doctrine\Common\Annotations\AnnotationReader;
use Fisdap\Attachments\Configuration\AttachmentConfig;
use Fisdap\Attachments\Core\ConfigProvider\ProvidesConfig;
use Fisdap\Attachments\Core\Kernel\DefaultAttachmentsKernel;
use Fisdap\Attachments\Core\MissingAttachmentConfiguration;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;
use Fisdap\Attachments\Entity\Attachment;
use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\Factory as Filesystem;


class DefaultAttachmentsKernelCest
{
    /**
     * @var Container|Mockery\Mock
     */
    private $containerMock;

    /**
     * @var ProvidesConfig|Mockery\Mock
     */
    private $configProviderMock;

    /**
     * @var Filesystem|Mockery\Mock
     */
    private $filesystemMock;

    /**
     * @var MapsAttachmentTypes|Mockery\Mock
     */
    private $attachmentTypeMapperMock;

    /**
     * @var AnnotationReader|Mockery\Mock
     */
    private $annotationReaderMock;

    /**
     * @var DefaultAttachmentsKernel
     */
    private $attachmentsKernel;


    public function _before(UnitTester $I)
    {
        $this->containerMock = Mockery::mock(Container::class);
        $this->configProviderMock = Mockery::mock(ProvidesConfig::class);
        $this->filesystemMock = Mockery::mock(Filesystem::class);
        $this->attachmentTypeMapperMock = Mockery::mock(MapsAttachmentTypes::class);
        $this->annotationReaderMock = Mockery::mock(AnnotationReader::class);

        $this->attachmentsKernel = new DefaultAttachmentsKernel(
            $this->containerMock,
            $this->configProviderMock,
            $this->filesystemMock,
            $this->attachmentTypeMapperMock
        );
        $this->attachmentsKernel->setAnnotationReader($this->annotationReaderMock);
    }

    public function _after(UnitTester $I)
    {
    }


    public function it_can_get_the_filesystem_factory(UnitTester $I)
    {
        // act
        $filesystem = $this->attachmentsKernel->getFilesystem();

        // assert
        $I->assertTrue($filesystem instanceof Filesystem);
    }


    public function it_can_get_the_attachments_config_provider(UnitTester $I)
    {
        // act
        $configProvider = $this->attachmentsKernel->getConfigProvider();

        // assert
        $I->assertTrue($configProvider instanceof ProvidesConfig);
    }


    public function it_can_retrieve_an_attachment_config(UnitTester $I)
    {
        // arrange
        $this->attachmentTypeMapperMock->shouldReceive('getAttachmentEntityClassName')->once()->with('foo')
            ->andReturn(Attachment::class);
        $this->annotationReaderMock->shouldReceive('getClassAnnotation')->once()->andReturn(new AttachmentConfig());

        // act
        $attachmentConfig = $this->attachmentsKernel->getAttachmentConfig('foo');

        // assert
        $I->assertTrue($attachmentConfig instanceof AttachmentConfig);
    }


    public function it_throws_an_exception_when_missing_attachment_config(UnitTester $I)
    {
        // assert
        $I->assertTrue(
            $I->seeExceptionThrown(MissingAttachmentConfiguration::class, function() {
                // arrange
                $this->attachmentTypeMapperMock->shouldReceive('getAttachmentEntityClassName')->once()
                    ->with('foo')->andReturn(Attachment::class);
                $this->annotationReaderMock->shouldReceive('getClassAnnotation')->once()->andReturnNull();

                // act
                $this->attachmentsKernel->getAttachmentConfig('foo');
            })
        );
    }


    public function it_can_retrieve_the_mime_types_blacklist(UnitTester $I)
    {
        // arrange
        $this->configProviderMock->shouldReceive('get')->withArgs(['mime_types_blacklist', []])->once()->andReturn([]);

        // act
        $mimeTypesBlacklist = $this->attachmentsKernel->getMimeTypesBlacklist();

        // assert
        $I->assertEquals([], $mimeTypesBlacklist);
    }


    public function it_can_generate_a_temp_save_path(UnitTester $I) {
        // arrange
        $this->configProviderMock->shouldReceive('get')->with('public_path')->once()->andReturn('path/to/public');
        $this->configProviderMock->shouldReceive('get')->with('temp_public_relative_path')->once()
            ->andReturn('attachments/temp');

        // act
        $actualTempSavePath = $this->attachmentsKernel->generateTempSavePath('foos/12345/abc123');

        // assert
        $expectedTempSavePath = 'path/to/public/attachments/temp/foos/12345/abc123';
        $I->assertEquals($expectedTempSavePath, $actualTempSavePath);
    }


    public function it_can_generate_a_temp_public_url_root(UnitTester $I)
    {
        // arrange
        $this->configProviderMock->shouldReceive('get')->with('temp_public_relative_path')->once()
            ->andReturn('attachments/temp');

        // act
        $actualTempPublicUrlRoot = $this->attachmentsKernel->generateTempPublicUrlRoot('foos/12345/abc123');

        // assert
        $expectedTempPublicUrlRoot = '/attachments/temp/foos/12345/abc123';
        $I->assertEquals($expectedTempPublicUrlRoot, $actualTempPublicUrlRoot);
    }


    public function it_can_get_the_default_filesystem_disk_name(UnitTester $I)
    {
        // arrange
        $this->configProviderMock->shouldReceive('get')->with('filesystem_disks.default')->once()
            ->andReturn('fly');
        $this->configProviderMock->shouldReceive('get')->with('filesystem_disks.foo')->once()->andReturn('foo');

        // act
        $actualConnectionName = $this->attachmentsKernel->getFilesystemDiskName('foo');

        // assert
        $I->assertEquals('foo', $actualConnectionName);
    }


    public function it_can_get_the_filesystem_disk_name_for_an_attachment_type(UnitTester $I)
    {
        // arrange
        $this->configProviderMock->shouldReceive('get')->with('filesystem_disks.default')->once()->andReturn('fly');

        // act
        $actualConnectionName = $this->attachmentsKernel->getFilesystemDiskName();

        // assert
        $I->assertEquals('fly', $actualConnectionName);
    }


    public function it_can_get_the_filesystem_temp_connection_name(UnitTester $I)
    {
        // arrange
        $this->configProviderMock->shouldReceive('get')->with('filesystem_disks.temp')->once()->andReturn('temp');

        // act
        $actualTempConn = $this->attachmentsKernel->getFilesystemTempDiskName();

        // assert
        $I->assertEquals('temp', $actualTempConn);
    }


    public function it_can_get_the_default_cdn_name(UnitTester $I)
    {
        // arrange
        $this->configProviderMock->shouldReceive('get')->with('cdn.default')->once()->andReturn('cloudfront');

        // act
        $actualCdnName = $this->attachmentsKernel->getCdnName();

        // assert
        $I->assertEquals('cloudfront', $actualCdnName);
    }


    public function it_can_get_the_cdn_name_for_an_attachment_type(UnitTester $I)
    {
        // arrange
        $this->configProviderMock->shouldReceive('get')->with('cdn.default')->once()->andReturn('cloudfront');
        $this->configProviderMock->shouldReceive('get')->with('cdn.foo')->once()->andReturn('foudclue');

        // act
        $actualCdnName = $this->attachmentsKernel->getCdnName('foo');

        // assert
        $I->assertEquals('foudclue', $actualCdnName);
    }


    public function it_can_get_the_cdn_url_root(UnitTester $I)
    {
        // arrange
        $cdnName = 'cloudfront';
        $this->configProviderMock->shouldReceive('get')->with("cdn.$cdnName.url_root")->once()->andReturn('http://foo');

        // act
        $actualCdnUrlRoot = $this->attachmentsKernel->getCdnUrlRoot($cdnName);

        // assert
        $I->assertEquals('http://foo', $actualCdnUrlRoot);
    }
}