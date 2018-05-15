<?php

use Fisdap\Attachments\Processing\AttachmentProcessorFactory;
use Fisdap\Attachments\Processing\ImageAttachmentProcessor;
use Fisdap\Attachments\Processing\ProcessesAttachments;
use Illuminate\Container\Container;

class AttachmentProcessorFactoryCest
{
    /**
     * @var Container|\Mockery\Mock
     */
    private $containerMock;

    /**
     * @var AttachmentProcessorFactory
     */
    private $attachmentProcessorFactory;


    public function _before(UnitTester $I)
    {
        $this->containerMock = Mockery::mock(Container::class);
        $this->attachmentProcessorFactory = new AttachmentProcessorFactory($this->containerMock);
    }

    public function _after(UnitTester $I)
    {
    }


    public function it_can_create_an_image_attachment_procesor(UnitTester $I)
    {
        // arrange
        $attachmentProcessor = Mockery::mock(ProcessesAttachments::class);
        $this->containerMock->shouldReceive('make')->once()->with(ImageAttachmentProcessor::class)
            ->andReturn($attachmentProcessor);

        // act
        $imageAttachmentProcessor = $this->attachmentProcessorFactory->create('image/jpeg');

        // assert
        $I->assertSame($attachmentProcessor, $imageAttachmentProcessor);
    }


    public function it_returns_null_for_unsupported_mime_media_types(UnitTester $I)
    {
        // act/assert
        $I->assertNull($this->attachmentProcessorFactory->create('text/html'));
    }
}
