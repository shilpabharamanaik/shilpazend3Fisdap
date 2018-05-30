<?php

use Fisdap\Attachments\Categories\Entity\AttachmentCategory;
use Fisdap\Attachments\Categories\Queries\AttachmentCategoriesFinder;
use Fisdap\Attachments\Categories\Repository\AttachmentCategoriesRepository;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;

class AttachmentCategoriesFinderCest
{
    /**
     * @var MapsAttachmentTypes|Mockery\mock
     */
    private $attachmentTypeMapperMock;

    /**
     * @var AttachmentCategoriesRepository|Mockery\mock
     */
    private $repositoryMock;

    /**
     * @var AttachmentCategoriesFinder
     */
    private $attachmentCategoriesFinder;


    public function _before(UnitTester $I)
    {
        $this->attachmentTypeMapperMock = Mockery::mock(MapsAttachmentTypes::class);
        $this->repositoryMock = Mockery::mock(AttachmentCategoriesRepository::class);

        $this->attachmentCategoriesFinder = new AttachmentCategoriesFinder(
            $this->attachmentTypeMapperMock,
            $this->repositoryMock
        );
    }


    public function _after(UnitTester $I)
    {
    }


    public function it_can_find_an_attachment_category_by_id(UnitTester $I)
    {
        // arrange
        $fakeAttachmentCategory = new AttachmentCategory('foo');

        $this->repositoryMock->shouldReceive('getOneById')->once()->andReturn($fakeAttachmentCategory);

        // act
        $attachmentCategory = $this->attachmentCategoriesFinder->findById(1);

        // assert
        $I->assertEquals($fakeAttachmentCategory, $attachmentCategory);
    }


    public function it_can_find_all_attachment_catgories_for_an_attachment_type(UnitTester $I)
    {
        // arrange
        $fakeAttachmentCategories = [
            new AttachmentCategory('foo'),
            new AttachmentCategory('bar'),
            new AttachmentCategory('baz')
        ];

        $this->attachmentTypeMapperMock->shouldReceive('getAttachmentCategoryEntityClassName')->once()
            ->andReturn(AttachmentCategory::class);

        $this->repositoryMock->shouldReceive('getAllByType')->once()->andReturn($fakeAttachmentCategories);

        // act
        $attachmentCategories = $this->attachmentCategoriesFinder->findAll('fake');

        // assert
        $I->assertEquals($fakeAttachmentCategories, $attachmentCategories);
    }
}
