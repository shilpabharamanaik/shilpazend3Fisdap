<?php

use Fisdap\Attachments\Associations\Entities\HasAttachments;
use Fisdap\Attachments\Categories\Entity\AttachmentCategory;
use Fisdap\Attachments\Cdn\LocalUrlGenerator;
use Fisdap\Attachments\Cdn\SignedUrlGenerator;
use Fisdap\Attachments\Cdn\SignedUrlGeneratorFactory;
use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Attachments\Transformation\AttachmentTransformer;
use Illuminate\Container\Container;

class AttachmentTransformerCest
{
    /**
     * @var HasAttachments|Mockery\Mock
     */
    private $associatedEntityMock;

    /**
     * @var Attachment|Mockery\Mock
     */
    private $fakeAttachment;

    /**
     * @var AttachmentsKernel|Mockery\Mock
     */
    private $attachmentsKernelMock;

    /**
     * @var SignedUrlGeneratorFactory|Mockery\Mock
     */
    private $signedUrlGeneratorFactoryMock;

    /**
     * @var AttachmentTransformer
     */
    private $attachmentTransformer;

    /**
     * @var Container|Mockery\Mock
     */
    private $containerMock;


    public function _before(UnitTester $I)
    {
        $this->associatedEntityMock = Mockery::mock(HasAttachments::class);

        $this->fakeAttachment = Mockery::mock(new Attachment(
            12345,
            $this->associatedEntityMock,
            'somepic.jpg',
            67890,
            'image/jpeg',
            'fakes/12345/abc123',
            'abc123',
            'foo',
            'bar'
        ));

        $this->attachmentsKernelMock = Mockery::mock(AttachmentsKernel::class);
        $this->containerMock = Mockery::mock(Container::class);
        $this->signedUrlGeneratorFactoryMock = new SignedUrlGeneratorFactory($this->containerMock);
        $this->attachmentTransformer = new AttachmentTransformer(
            $this->attachmentsKernelMock,
            $this->signedUrlGeneratorFactoryMock
        );
    }

    public function _after(UnitTester $I)
    {
    }


    public function it_can_set_the_attachment_type_fluently(UnitTester $I)
    {
        // act
        $attachmentTransformer = $this->attachmentTransformer->setAttachmentType('fake');

        // assert
        $I->assertSame($this->attachmentTransformer, $attachmentTransformer);
    }


    public function it_can_transform_an_unprocessed_attachment_without_categories(UnitTester $I)
    {
        // arrange
        $this->associatedEntityMock->shouldReceive('getId')->once();
        $this->attachmentsKernelMock->shouldReceive('generateTempPublicUrlRoot')->once()
            ->andReturn('attachments/temp/fakes/12345/abc123');
        $this->attachmentsKernelMock->shouldReceive('getConfigProvider->get')->once()->with('app_url')
            ->andReturn('http://a.server.com');

        // act
        $transformedAttachment = $this->attachmentTransformer->setAttachmentType('fake')->transform($this->fakeAttachment);

        // assert
        $I->assertEquals(
            [
                'id' => 'abc123',
                'associatedEntityId' => null,
                'userContextId' => 12345,
                'fileName' => 'somepic.jpg',
                'size' => 67890,
                'mimeType' => 'image/jpeg',
                'savePath' => 'fakes/12345/abc123',
                'variationFileNames' => null,
                'nickname' => 'foo',
                'notes' => 'bar',
                'categories' => null,
                'created' => null,
                'updated' => null,
                'processed' => false,
                'tempUrl' => 'http%3A%2F%2Fa.server.comattachments%2Ftemp%2Ffakes%2F12345%2Fabc123%2Fsomepic.jpg',
            ],
            $transformedAttachment
        );
    }


    public function it_can_transform_an_unprocessed_attachment_with_categories(UnitTester $I)
    {
        // arrange
        $this->associatedEntityMock->shouldReceive('getId')->once();
        $attachmentCategory = new AttachmentCategory('ECG');

        $this->fakeAttachment->addCategory($attachmentCategory);

        $this->attachmentsKernelMock->shouldReceive('generateTempPublicUrlRoot')->once()
            ->andReturn('attachments/temp/fakes/12345/abc123');
        $this->attachmentsKernelMock->shouldReceive('getConfigProvider->get')->once()->with('app_url')
            ->andReturn('http://a.server.com');

        // act
        $transformedAttachment = $this->attachmentTransformer->setAttachmentType('fake')->transform($this->fakeAttachment);

        // assert
        $I->assertEquals(
            [
                'id' => 'abc123',
                'associatedEntityId' => null,
                'userContextId' => 12345,
                'fileName' => 'somepic.jpg',
                'size' => 67890,
                'mimeType' => 'image/jpeg',
                'savePath' => 'fakes/12345/abc123',
                'variationFileNames' => null,
                'nickname' => 'foo',
                'notes' => 'bar',
                'categories' => [
                    'ECG'
                ],
                'created' => null,
                'updated' => null,
                'processed' => false,
                'tempUrl' => 'http%3A%2F%2Fa.server.comattachments%2Ftemp%2Ffakes%2F12345%2Fabc123%2Fsomepic.jpg',
            ],
            $transformedAttachment
        );
    }


    public function it_can_transform_a_processed_attachment_without_categories(UnitTester $I)
    {
        // arrange
        $attachment = new Attachment(
            12345,
            $this->associatedEntityMock,
            'somepic.jpg',
            67890,
            'image/jpeg',
            'fakes/12345/abc123',
            'abc123',
            'foo',
            'bar'
        );

        $attachment->setProcessed(true);

        $this->associatedEntityMock->shouldReceive('getId')->once();

        $this->attachmentsKernelMock->shouldReceive('getCdnName')->once()->with('fake')->andReturn('local');

        $this->attachmentsKernelMock->shouldReceive('getCdnUrlRoot')->once()->with('local')->andReturn('http://a.cdn.com');


        /** @var SignedUrlGenerator|Mockery\Mock $signedUrlGeneratorMock */
        $localSignedUrlGenerator = new LocalUrlGenerator($this->attachmentsKernelMock);
        $this->containerMock->shouldReceive('make')->once()->with(LocalUrlGenerator::class)
            ->andReturn($localSignedUrlGenerator);

        // act
        $transformedAttachment = $this->attachmentTransformer->setAttachmentType('fake')->transform($attachment);

        // assert
        $I->assertEquals(
            [
                'id' => 'abc123',
                'associatedEntityId' => null,
                'userContextId' => 12345,
                'fileName' => 'somepic.jpg',
                'size' => 67890,
                'mimeType' => 'image/jpeg',
                'savePath' => 'fakes/12345/abc123',
                'variationFileNames' => null,
                'nickname' => 'foo',
                'notes' => 'bar',
                'categories' => null,
                'created' => null,
                'updated' => null,
                'processed' => true,
                'originalUrl' => 'http://a.cdn.com/fakes/12345/abc123/somepic.jpg',
            ],
            $transformedAttachment
        );
    }


    public function it_can_transform_a_processed_attachment_array_without_categories(UnitTester $I)
    {
        // arrange
        $attachment = [
            'id' => 'abc123',
            'associatedEntityId' => null,
            'userContextId' => 12345,
            'fileName' => 'somepic.jpg',
            'size' => 67890,
            'mimeType' => 'image/jpeg',
            'savePath' => 'fakes/12345/abc123',
            'variationFileNames' => ['thumbnail' => 'somepic-thumbnail.jpg'],
            'nickname' => 'foo',
            'notes' => 'bar',
            'categories' => null,
            'processed' => true,
            'created' => new \DateTime('January 6, 2014 15:00:00'),
            'updated' => null,
        ];

        $this->attachmentsKernelMock->shouldReceive('getCdnName')->once()->with('fake')->andReturn('local');

        $this->attachmentsKernelMock->shouldReceive('getCdnUrlRoot')->once()->with('local')->andReturn('http://a.cdn.com');

        /** @var SignedUrlGenerator|Mockery\Mock $signedUrlGeneratorMock */
        $localSignedUrlGenerator = new LocalUrlGenerator($this->attachmentsKernelMock);
        $this->containerMock->shouldReceive('make')->once()->with(LocalUrlGenerator::class)
            ->andReturn($localSignedUrlGenerator);

        // act
        $transformedAttachment = $this->attachmentTransformer->setAttachmentType('fake')->transform($attachment);

        // assert
        $I->assertEquals(
            [
                'id' => 'abc123',
                'associatedEntityId' => null,
                'userContextId' => 12345,
                'fileName' => 'somepic.jpg',
                'size' => 67890,
                'mimeType' => 'image/jpeg',
                'savePath' => 'fakes/12345/abc123',
                'variationFileNames' => ['thumbnail' => 'somepic-thumbnail.jpg'],
                'nickname' => 'foo',
                'notes' => 'bar',
                'categories' => null,
                'created' => '2014-01-06 15:00:00',
                'updated' => null,
                'processed' => true,
                'originalUrl' => 'http://a.cdn.com/fakes/12345/abc123/somepic.jpg',
                'variationUrls' => [
                    'thumbnail' => 'http://a.cdn.com/fakes/12345/abc123/somepic-thumbnail.jpg'
                ]
            ],
            $transformedAttachment
        );
    }

    // todo - test filename with spaces
}
