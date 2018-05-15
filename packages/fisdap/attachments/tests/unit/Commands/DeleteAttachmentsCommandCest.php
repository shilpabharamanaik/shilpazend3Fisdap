<?php

use Fisdap\Attachments\Commands\Deletion\DeleteAttachmentsCommand;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Attachments\Queries\FindsAttachments;
use Fisdap\Attachments\Repository\AttachmentsRepository;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

/**
 * Class DeleteAttachmentsCommandCest
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class DeleteAttachmentsCommandCest
{
    /**
     * @var FindsAttachments|Mockery\mock
     */
    private $attachmentsFinderMock;

    /**
     * @var AttachmentsRepository|Mockery\mock
     */
    private $attachmentsRepositoryMock;

    /**
     * @var LogsAttachmentEvents|Mockery\mock
     */
    private $loggerMock;

    /**
     * @var EventDispatcher|Mockery\mock
     */
    private $eventDispatcherMock;

    /**
     * @var Dispatcher|Mockery\mock
     */
    private $dispatcherMock;

    /**
     * @var DeleteAttachmentsCommand
     */
    private $deleteAttachmentsCommand;


    public function _before(UnitTester $I)
    {
        $this->attachmentsFinderMock = Mockery::mock(FindsAttachments::class);
        $this->attachmentsRepositoryMock = Mockery::mock(AttachmentsRepository::class);
        $this->loggerMock = Mockery::mock(LogsAttachmentEvents::class);
        $this->eventDispatcherMock = Mockery::mock(EventDispatcher::class);
        $this->dispatcherMock = Mockery::mock(Dispatcher::class);

        $this->deleteAttachmentsCommand = new DeleteAttachmentsCommand('fake', ['abc123']);
    }


    public function _after(UnitTester $I)
    {
    }


    public function it_can_handle_attachment_deletion(UnitTester $I)
    {
        // arrange
        /** @var Attachment|Mockery\mock $fakeAttachment */
        $fakeAttachment = Mockery::mock(Attachment::class);

        $this->attachmentsFinderMock->shouldReceive('findAttachment')->once()->withArgs(['fake', 'abc123'])
            ->andReturn($fakeAttachment);

        $this->attachmentsRepositoryMock->shouldReceive('destroy')->once()->with($fakeAttachment);

        $fakeAttachment->shouldReceive('getSavePath')->once();

        $this->loggerMock->shouldReceive('info')->once();

        $this->eventDispatcherMock->shouldReceive('fire')->once();

        $this->dispatcherMock->shouldReceive('dispatch')->once();

        // act
        $this->deleteAttachmentsCommand->handle(
            $this->attachmentsFinderMock,
            $this->attachmentsRepositoryMock,
            $this->loggerMock,
            $this->eventDispatcherMock,
            $this->dispatcherMock
        );
    }
}
