<?php

use Fisdap\Attachments\Categories\Commands\Creation\CreateAttachmentCategoriesCommand;
use Fisdap\Attachments\Categories\Commands\Exceptions\MissingAttachmentCategoryEntityClass;
use Fisdap\Attachments\Categories\Entity\AttachmentCategory;

/**
 * Class CreateAttachmentCategoriesCommandCest
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class CreateAttachmentCategoriesCommandCest extends AttachmentCategoryCommandCestTemplate
{
    /**
     * @var CreateAttachmentCategoriesCommand
     */
    private $createAttachmentCategoriesCommand;


    public function _before(UnitTester $I)
    {
        parent::_before($I);

        $this->createAttachmentCategoriesCommand = new CreateAttachmentCategoriesCommand('fake', ['foo', 'bar', 'baz']);
    }


    public function it_can_handle_creation_of_attachment_categories(UnitTester $I)
    {
        // arrange
        $fakeAttachmentCategories = [
            new AttachmentCategory('foo'),
            new AttachmentCategory('bar'),
            new AttachmentCategory('baz')
        ];

        $this->attachmentTypeMapperMock->shouldReceive('getAttachmentCategoryEntityClassName')->once()->with('fake')
            ->andReturn(AttachmentCategory::class);

        $this->repositoryMock->shouldReceive('storeCollection')->once()->with($fakeAttachmentCategories);

        $this->loggerMock->shouldReceive('info')->once();

        // act
        $attachmentCategories = $this->createAttachmentCategoriesCommand->handle(
            $this->attachmentTypeMapperMock,
            $this->repositoryMock,
            $this->loggerMock
        );

        // assert
        $I->assertEquals($fakeAttachmentCategories, $attachmentCategories);
    }


    public function it_throws_an_exception_when_no_attachment_category_class_was_found(UnitTester $I)
    {
        // assert
        $I->assertTrue(
            $I->seeExceptionThrown(
                MissingAttachmentCategoryEntityClass::class,
                function () {
                    // arrange
                    $this->attachmentTypeMapperMock->shouldReceive('getAttachmentCategoryEntityClassName')->once()
                        ->with('fake')->andReturnNull();

                    // act
                    $this->createAttachmentCategoriesCommand->handle(
                        $this->attachmentTypeMapperMock,
                        $this->repositoryMock,
                        $this->loggerMock
                    );
                }
            )
        );
    }
}
