<?php

use Fisdap\Attachments\Associations\Entities\HasAttachments;
use Fisdap\Attachments\Categories\Entity\AttachmentCategory;
use Fisdap\Attachments\Categories\Entity\AttachmentCategoryNotFound;
use Fisdap\Attachments\Categories\Repository\AttachmentCategoriesRepository;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;
use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Attachments\Entity\AttachmentFactory;
use Symfony\Component\HttpFoundation\File\File;

class AttachmentFactoryCest
{
    /**
     * @var Attachment|Mockery\Mock
     */
    private $fakeAttachment;

    /**
     * @var HasAttachments|Mockery\Mock
     */
    private $associatedEntityMock;

    /**
     * @var MapsAttachmentTypes|Mockery\Mock
     */
    private $attachmentTypeMapperMock;

    /**
     * @var AttachmentCategoriesRepository|\Mockery\Mock
     */
    private $attachmentCategoriesRepositoryMock;

    /**
     * @var AttachmentFactory
     */
    private $attachmentFactory;

    /**
     * @var File
     */
    private $fakeFile;


    public function _before(UnitTester $I)
    {
        $this->associatedEntityMock = Mockery::mock(HasAttachments::class);


        $this->fakeAttachment = Mockery::mock(new Attachment(
            12345,
            $this->associatedEntityMock,
            'fakeattach.txt',
            12345,
            'text/plain',
            'fakes/12345/abc123',
            'abc123',
            'foo',
            'bar'
        ));

        $this->attachmentTypeMapperMock = Mockery::mock(MapsAttachmentTypes::class);
        $this->attachmentCategoriesRepositoryMock = Mockery::mock(AttachmentCategoriesRepository::class);

        $this->attachmentFactory = new AttachmentFactory(
            $this->attachmentTypeMapperMock,
            $this->attachmentCategoriesRepositoryMock
        );

        file_put_contents('/tmp/fakeattach.txt', 'lorum ipsum dolor sit amet');
        $this->fakeFile = new File('/tmp/fakeattach.txt');

        $this->attachmentTypeMapperMock->shouldReceive('getAttachmentEntityClassName')->once()
            ->with('fake')->andReturn(Attachment::class);
        $this->attachmentTypeMapperMock->shouldReceive('getAttachmentCategoryEntityClassName')->once()
            ->with('fake')->andReturn(AttachmentCategory::class);
    }

    public function _after(UnitTester $I)
    {
        // cleanup
        unlink('/tmp/fakeattach.txt');
    }


    public function it_can_create_an_attachment_without_categories(UnitTester $I)
    {
        // arrange
        $this->fakeAttachment->shouldReceive('createFromFile')->once();

        // act
        $attachment = $this->attachmentFactory->create(
            'fake',
            12345,
            $this->associatedEntityMock,
            'abc123',
            $this->fakeFile,
            'fakes/12345/abc123',
            'foo',
            'bar'
        );

        // assert
        $I->assertEquals('abc123', $attachment->getId());
        $I->assertEquals(12345, $attachment->getUserContextId());
        $I->assertEquals('fakeattach.txt', $attachment->getFileName());
        $I->assertEquals('fakes/12345/abc123', $attachment->getSavePath());
        $I->assertEquals('foo', $attachment->getNickname());
        $I->assertEquals('bar', $attachment->getNotes());
        $I->assertEquals($this->fakeFile->getMimeType(), $attachment->getMimeType());
        $I->assertEquals($this->fakeFile->getSize(), $attachment->getSize());
        $I->assertEquals($this->fakeFile->getExtension(), $attachment->getExtension());
        $I->assertEquals('fakeattach', $attachment->getFileNameWithoutExtension());
    }


    public function it_can_create_an_attachment_with_categories(UnitTester $I)
    {
        // arrange
        $fakeCategory = new AttachmentCategory('ECG');

        $this->fakeAttachment->shouldReceive('createFromFile');

        $this->attachmentCategoriesRepositoryMock->shouldReceive('getOneByNameAndType')->once()
            ->andReturn($fakeCategory);

        // act
        $attachment = $this->attachmentFactory->create(
            'fake',
            12345,
            $this->associatedEntityMock,
            'abc123',
            $this->fakeFile,
            'fakes/12345/abc123',
            'foo',
            'bar',
            ['ECG']
        );

        // assert
        $I->assertEquals('abc123', $attachment->getId());
        $I->assertEquals(12345, $attachment->getUserContextId());
        $I->assertEquals('fakeattach.txt', $attachment->getFileName());
        $I->assertEquals('fakes/12345/abc123', $attachment->getSavePath());
        $I->assertEquals('foo', $attachment->getNickname());
        $I->assertEquals('bar', $attachment->getNotes());
        $I->assertSame($fakeCategory, $attachment->getCategories()[0]);
    }


    public function it_throws_an_exception_when_an_invalid_category_has_been_chosen(UnitTester $I)
    {
        // assert
        $I->assertTrue(
            $I->seeExceptionThrown(AttachmentCategoryNotFound::class, function () {
                // arrange
                $this->fakeAttachment->shouldReceive('createFromFile');

                $this->attachmentCategoriesRepositoryMock->shouldReceive('getOneByNameAndType')->andReturnNull();

                // act
                $this->attachmentFactory->create(
                    'fake',
                    12345,
                    $this->associatedEntityMock,
                    'abc123',
                    $this->fakeFile,
                    'fakes/12345/abc123',
                    'foo',
                    'bar',
                    ['POO']
                );
            })
        );
    }
}
