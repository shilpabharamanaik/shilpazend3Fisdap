<?php

use Doctrine\Common\Collections\ArrayCollection;
use Fisdap\Attachments\Associations\Entities\EntityAttachmentsSupport;
use Fisdap\Attachments\Associations\Entities\HasAttachments;
use Fisdap\Attachments\Entity\Attachment;


class EntityAttachmentsSupportTest extends \Codeception\TestCase\Test implements HasAttachments
{
    use EntityAttachmentsSupport;


    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var ArrayCollection|Mockery\Mock
     */
    protected $attachments;


    protected function _before()
    {
    }

    protected function _after()
    {
    }


    /** @test */
    public function it_can_get_an_id()
    {
        // arrange
        $this->id = 1;

        // act
        $id = $this->getId();

        // assert
        $this->assertSame($this->id, $id);
    }


    /** @test */
    public function it_can_get_a_collection_of_attachments()
    {
        // arrange
        $this->attachments = new ArrayCollection();

        // act
        $attachments = $this->getAttachments();

        // assert
        $this->assertSame($this->attachments, $attachments);
    }


    /** @test */
    public function it_can_get_an_attachment_by_id()
    {
        // arrange
        $attachment = Mockery::mock(Attachment::class);
        $this->attachments = Mockery::mock(ArrayCollection::class);
        $this->attachments->shouldReceive('matching')->once()->andReturn([$attachment]);

        // act
        $actualAttachment = $this->getAttachmentById('abc123');

        // assert
        $this->assertSame($attachment, $actualAttachment);
    }


    /** @test */
    public function it_can_add_an_attachment_to_the_collection()
    {
        // arrange
        /** @var Attachment $attachment */
        $attachment = Mockery::mock(Attachment::class);
        $this->attachments = Mockery::mock(ArrayCollection::class);
        $this->attachments->shouldReceive('add')->once()->with($attachment);

        // act
        $this->addAttachment($attachment);
    }


    /** @test */
    public function it_can_remove_an_attachment_from_the_collection()
    {
        // arrange
        /** @var Attachment $attachment */
        $attachment = Mockery::mock(Attachment::class);
        $this->attachments = Mockery::mock(ArrayCollection::class);
        $this->attachments->shouldReceive('removeElement')->once()->with($attachment);

        // act
        $this->removeAttachment($attachment);
    }
}