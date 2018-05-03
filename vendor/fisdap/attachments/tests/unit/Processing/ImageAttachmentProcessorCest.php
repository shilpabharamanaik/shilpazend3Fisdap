<?php

use Fisdap\Attachments\Configuration\AttachmentConfig;
use Fisdap\Attachments\Configuration\ImageAttachmentVariationConfig;
use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Attachments\Processing\ImageAttachmentProcessor;
use Fisdap\Attachments\Processing\ImageFilters\ThumbnailFilter;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;


class ImageAttachmentProcessorCest
{
    /**
     * @var AttachmentsKernel|Mockery\Mock
     */
    private $attachmentsKernelMock;

    /**
     * @var LogsAttachmentEvents|Mockery\Mock
     */
    private $attachmentsLoggerMock;

    /**
     * @var ImageManager|Mockery\Mock
     */
    private $imageManagerMock;


    public function _before(UnitTester $I)
    {
        $this->imageManagerMock = Mockery::mock(ImageManager::class);
        $this->attachmentsKernelMock = Mockery::mock(AttachmentsKernel::class);
        $this->attachmentsLoggerMock = Mockery::mock(LogsAttachmentEvents::class);
    }


    public function _after(UnitTester $I)
    {
    }


    public function it_can_process_an_image_with_a_single_variation(UnitTester $I)
    {
        // arrange
        /** @var Attachment|Mockery\Mock $attachmentMock */
        $attachmentMock = Mockery::mock(Attachment::class);
        $attachmentConfig = new AttachmentConfig();
        $imageAttachmentVariationConfig = new ImageAttachmentVariationConfig();
        $imageAttachmentVariationConfig->name = 'thumbnail';
        $imageAttachmentVariationConfig->imageProcessorFilterClassName = ThumbnailFilter::class;
        $attachmentConfig->variationConfigurations = [$imageAttachmentVariationConfig];

        $attachmentMock->shouldReceive('getSavePath')->once()->andReturn('foos/12345/abc123');

        $this->attachmentsKernelMock->shouldReceive('generateTempSavePath')->with('foos/12345/abc123')->once()
            ->andReturn('attachments/temp/foos/12345/abc123');

        /** @var Image|Mockery\Mock $imageMock */
        $imageMock = Mockery::mock(Image::class);
        $this->imageManagerMock->shouldReceive('make')->once()->andReturn($imageMock);
        $attachmentMock->shouldReceive('getFileName')->once()->andReturn('foobar.jpg');

        /** @var Image|Mockery\Mock $variationImageMock */
        $variationImageMock = Mockery::mock(Image::class);
        $imageMock->shouldReceive('orientate->filter')->once()->andReturn($variationImageMock);

        $attachmentMock->shouldReceive('getFileNameWithoutExtension')->once()->andReturn('foobar');
        $attachmentMock->shouldReceive('getExtension')->once()->andReturn('jpg');

        $variationImageMock->shouldReceive('save')->once();
        $attachmentMock->shouldReceive('addVariationFileName')->once();

        $attachmentMock->shouldReceive('getId')->once();
        $this->attachmentsLoggerMock->shouldReceive('info')->once();

        $imageAttachmentProcessor = new ImageAttachmentProcessor(
            $this->imageManagerMock, $this->attachmentsKernelMock, $this->attachmentsLoggerMock
        );

        // act
        $imageAttachmentProcessor->setAttachmentType('foo');
        $imageAttachmentProcessor->process($attachmentMock, $attachmentConfig);
    }
}