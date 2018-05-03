<?php

use Fisdap\Attachments\Core\ConfigProvider\ProvidesConfig;
use Fisdap\Attachments\Core\TypeMapper\AttachmentTypeMapper;


class AttachmentTypeMapperCest
{
    /**
     * @var ProvidesConfig|Mockery\Mock
     */
    private $configProviderMock;

    /**
     * @var AttachmentTypeMapper
     */
    private $attachmentTypeMapper;


    public function _before(UnitTester $I)
    {
        $this->configProviderMock = Mockery::mock(ProvidesConfig::class);
        $this->attachmentTypeMapper = new AttachmentTypeMapper($this->configProviderMock);
    }

    public function _after(UnitTester $I)
    {
    }


    public function it_can_get_an_attachment_entity_class_name(UnitTester $I)
    {
        // arrange
        $this->configProviderMock->shouldReceive('get')->with('types.foo.entity')->once()->andReturn('FooAttachment');

        // act
        $attachmentEntityClassName = $this->attachmentTypeMapper->getAttachmentEntityClassName('foo');

        // assert
        $I->assertEquals('FooAttachment', $attachmentEntityClassName);
    }


    public function it_can_determine_the_attachment_type_from_its_entity_class_name(UnitTester $I)
    {
        // arrange
        $this->configProviderMock->shouldReceive('get')->with('types')->once()
            ->andReturn(['foo' => ['entity' => 'FooAttachment']]);

        // act
        $attachmentType = $this->attachmentTypeMapper->getAttachmentTypeFromEntityClassName('FooAttachment');

        // assert
        $I->assertEquals('foo', $attachmentType);
    }


    public function it_returns_null_when_an_attachment_category_entity_class_name_does_not_exist(UnitTester $I)
    {
        // arrange
        $this->configProviderMock->shouldReceive('get')->with('types.foo.entity')->once()->andReturn('FooAttachment');

        // act
        $attachmentCategoryEntityClassName = $this->attachmentTypeMapper->getAttachmentCategoryEntityClassName('foo');

        // assert
        $I->assertNull($attachmentCategoryEntityClassName);
    }
}