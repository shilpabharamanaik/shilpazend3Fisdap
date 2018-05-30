<?php

use Fisdap\Attachments\Configuration\AttachmentConfig;
use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Transformation\AttachmentTransformer;
use Fisdap\Attachments\Transformation\AttachmentTransformerFactory;
use Fisdap\Attachments\Transformation\TransformsAttachments;
use Illuminate\Container\Container;

class AttachmentTransformerFactoryCest
{
    /**
     * @var Container|Mockery\Mock
     */
    private $containerMock;

    /**
     * @var AttachmentsKernel|Mockery\Mock
     */
    private $attachmentsKernelMock;

    /**
     * @var AttachmentTransformerFactory
     */
    private $attachmentTransformerFactory;


    public function _before(UnitTester $I)
    {
        $this->containerMock = Mockery::mock(Container::class);
        $this->attachmentsKernelMock = Mockery::mock(AttachmentsKernel::class);

        $this->attachmentTransformerFactory = new AttachmentTransformerFactory(
            $this->containerMock,
            $this->attachmentsKernelMock
        );
    }


    public function _after(UnitTester $I)
    {
    }


    public function it_can_create_an_attachment_transformer(UnitTester $I)
    {
        // arrange
        $fakeAttachmentConfig = new AttachmentConfig();
        $fakeAttachmentConfig->transformerClassName = AttachmentTransformer::class;

        $this->attachmentsKernelMock->shouldReceive('getAttachmentConfig')->once()->with('fake')
            ->andReturn($fakeAttachmentConfig);
        $this->containerMock->shouldReceive('make')->once()->with(AttachmentTransformer::class)
            ->andReturn(Mockery::mock(TransformsAttachments::class));

        // act
        $transformer = $this->attachmentTransformerFactory->create('fake');

        // assert
        $I->assertTrue($transformer instanceof TransformsAttachments);
    }


    public function it_can_create_a_default_attachment_transformer(UnitTester $I)
    {
        // arrange
        $fakeAttachmentConfig = new AttachmentConfig();

        $this->attachmentsKernelMock->shouldReceive('getAttachmentConfig')->once()->with('fake')
            ->andReturn($fakeAttachmentConfig);
        $this->containerMock->shouldReceive('make')->once()->with(TransformsAttachments::class)
            ->andReturn(Mockery::mock(TransformsAttachments::class));

        // act
        $transformer = $this->attachmentTransformerFactory->create('fake');

        // assert
        $I->assertTrue($transformer instanceof TransformsAttachments);
    }
}
