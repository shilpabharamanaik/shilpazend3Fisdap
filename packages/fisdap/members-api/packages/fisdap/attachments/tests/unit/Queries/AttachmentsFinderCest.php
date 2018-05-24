<?php

use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;
use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Attachments\Entity\AttachmentNotFound;
use Fisdap\Attachments\Queries\AttachmentsFinder;
use Fisdap\Attachments\Repository\AttachmentsRepository;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;


class AttachmentsFinderCest
{
    /**
     * @var MapsAttachmentTypes|Mockery\Mock
     */
    private $attachmentTypeMapperMock;

    /**
     * @var AttachmentsRepository|Mockery\Mock
     */
    private $attachmentsRepositoryMock;

    /**
     * @var EventDispatcher|Mockery\Mock
     */
    private $eventDispatcherMock;

    /**
     * @var AttachmentsFinder
     */
    private $attachmentsFinder;


    public function _before(UnitTester $I)
    {
        $this->attachmentTypeMapperMock = Mockery::mock(MapsAttachmentTypes::class);
        $this->attachmentsRepositoryMock = Mockery::mock(AttachmentsRepository::class);
        $this->eventDispatcherMock = Mockery::mock(EventDispatcher::class);
        $this->attachmentsFinder = new AttachmentsFinder(
            $this->attachmentTypeMapperMock, $this->attachmentsRepositoryMock, $this->eventDispatcherMock
        );
    }

    public function _after(UnitTester $I)
    {
    }


    public function it_can_find_an_attachment(UnitTester $I)
    {
        // arrange
        /** @var Attachment|Mockery\Mock $attachmentMock */
        $attachmentMock = Mockery::mock(Attachment::class);

        $this->attachmentTypeMapperMock->shouldReceive('getAttachmentEntityClassName')->once()
            ->with('foo')->andReturn(Attachment::class);

        $this->attachmentsRepositoryMock->shouldReceive('match')->once()->andReturn([$attachmentMock]);

        $attachmentMock->shouldReceive('toArray')->once();
        $this->eventDispatcherMock->shouldReceive('fire')->once();

        // act
        $attachment = $this->attachmentsFinder->findAttachment('foo', 'abc123');

        // assert
        $I->assertSame($attachmentMock, $attachment);
    }


    public function it_throws_an_exception_when_no_attachment_could_be_found(UnitTester $I)
    {
        // assert
        $I->assertTrue(
            $I->seeExceptionThrown(AttachmentNotFound::class, function() {

                // arrange
                $this->attachmentTypeMapperMock->shouldReceive('getAttachmentEntityClassName')->once()
                    ->with('foo')->andReturn(Attachment::class);

                $this->attachmentsRepositoryMock->shouldReceive('match')->once()->andReturnNull();

                // act
                $this->attachmentsFinder->findAttachment('foo', 'abc123');
            })
        );
    }


    public function it_can_find_all_attachments_for_an_entity_by_type(UnitTester $I)
    {
        // arrange
        $this->attachmentTypeMapperMock->shouldReceive('getAttachmentEntityClassName')->once()
            ->with('foo')->andReturn(Attachment::class);

        $attachmentsMock = [Mockery::mock(Attachment::class), Mockery::mock(Attachment::class)];

        $this->attachmentsRepositoryMock->shouldReceive('match')->once()->andReturn($attachmentsMock);

        $this->eventDispatcherMock->shouldReceive('fire')->once();

        // act
        $attachments = $this->attachmentsFinder->findAllAttachments('foo', 12345);

        // assert
        $I->assertSame($attachmentsMock, $attachments);
    }


    public function it_throws_an_exception_when_no_attachments_could_be_found(UnitTester $I)
    {
        // assert
        $I->assertTrue(
            $I->seeExceptionThrown(AttachmentNotFound::class, function() {

                // arrange
                $this->attachmentTypeMapperMock->shouldReceive('getAttachmentEntityClassName')->once()
                    ->with('foo')->andReturn(Attachment::class);

                $attachmentsMock = [];

                $this->attachmentsRepositoryMock->shouldReceive('match')->once()->andReturn($attachmentsMock);

                // act
                $this->attachmentsFinder->findAllAttachments('foo', 12345);
            })
        );
    }
}