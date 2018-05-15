<?php

use Fisdap\Attachments\Categories\Commands\Modification\RenameAttachmentCategoryCommand;
use Fisdap\Attachments\Categories\Entity\AttachmentCategory;
use Fisdap\Attachments\Categories\Entity\AttachmentCategoryNotFound;

/**
 * Class RenameAttachmentCategoryCommandCest
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class RenameAttachmentCategoryCommandCest extends AttachmentCategoryCommandCestTemplate
{
    public function _before(UnitTester $I)
    {
        parent::_before($I);
    }


    public function it_can_rename_an_attachment_category_by_id(UnitTester $I)
    {
        // arrange
        $command = new RenameAttachmentCategoryCommand('bar', null, 1);

        /** @var AttachmentCategory|Mockery\mock $fakeAttachmentCategory */
        $fakeAttachmentCategory = new AttachmentCategory('foo');

        $this->repositoryMock->shouldReceive('getOneById')->once()->with(1)->andReturn($fakeAttachmentCategory);

        $this->repositoryMock->shouldReceive('update')->once()->with($fakeAttachmentCategory);

        $this->loggerMock->shouldReceive('info')->once();

        // act
        $attachmentCategory = $command->handle(
            $this->attachmentTypeMapperMock,
            $this->repositoryMock,
            $this->loggerMock
        );

        // assert
        $I->assertEquals($fakeAttachmentCategory, $attachmentCategory);
    }


    public function it_can_rename_an_attachment_category_by_name(UnitTester $I)
    {
        // arrange
        $command = new RenameAttachmentCategoryCommand('bar', 'foo', null, 'fake');

        /** @var AttachmentCategory|Mockery\mock $fakeAttachmentCategory */
        $fakeAttachmentCategory = new AttachmentCategory('foo');

        $this->attachmentTypeMapperMock->shouldReceive('getAttachmentCategoryEntityClassName')->once()->with('fake')
            ->andReturn(AttachmentCategory::class);

        $this->repositoryMock->shouldReceive('getOneByNameAndType')->once()
            ->withArgs(['foo', AttachmentCategory::class])->andReturn($fakeAttachmentCategory);

        $this->repositoryMock->shouldReceive('update')->once()->with($fakeAttachmentCategory);

        $this->loggerMock->shouldReceive('info')->once();

        // act
        $attachmentCategory = $command->handle(
            $this->attachmentTypeMapperMock,
            $this->repositoryMock,
            $this->loggerMock
        );

        // assert
        $I->assertEquals($fakeAttachmentCategory, $attachmentCategory);
    }


    public function it_throws_an_exception_when_an_attachment_category_could_not_be_found(UnitTester $I)
    {
        // assert
        $I->assertTrue(
            $I->seeExceptionThrown(AttachmentCategoryNotFound::class, function () {
                // arrange
                $command = new RenameAttachmentCategoryCommand('bar', null, 1);

                $this->repositoryMock->shouldReceive('getOneById')->once()->with(1)->andReturn(null);

                // act
                $command->handle(
                    $this->attachmentTypeMapperMock,
                    $this->repositoryMock,
                    $this->loggerMock
                );
            })
        );
    }
}
