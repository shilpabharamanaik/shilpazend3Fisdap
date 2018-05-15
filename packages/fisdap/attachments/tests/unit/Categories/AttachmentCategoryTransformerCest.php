<?php

use Fisdap\Attachments\Categories\AttachmentCategoryTransformer;
use Fisdap\Attachments\Categories\Entity\AttachmentCategory;

class AttachmentCategoryTransformerCest
{
    /**
     * @var AttachmentCategoryTransformer
     */
    private $attachmentCategoryTransfomer;


    public function _before(UnitTester $I)
    {
        $this->attachmentCategoryTransfomer = new AttachmentCategoryTransformer();
    }


    public function _after(UnitTester $I)
    {
    }


    public function it_can_transform_an_attachment_category(UnitTester $I)
    {
        // arrange
        $attachmentCategory = new AttachmentCategory('foo');

        // act
        $transformedAttachmentCategory = $this->attachmentCategoryTransfomer->transform($attachmentCategory);

        // assert
        $I->assertEquals('foo', $transformedAttachmentCategory['name']);
    }


    public function it_can_transform_an_attachment_category_array(UnitTester $I)
    {
        // arrange
        $attachmentCategoryArray = [
            'name' => 'foo'
        ];

        // act
        $transformedAttachmentCategory = $this->attachmentCategoryTransfomer->transform($attachmentCategoryArray);

        // assert
        $I->assertEquals('foo', $transformedAttachmentCategory['name']);
    }
}
