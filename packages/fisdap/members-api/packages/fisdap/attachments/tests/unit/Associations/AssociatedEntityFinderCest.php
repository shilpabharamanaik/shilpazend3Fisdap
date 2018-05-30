<?php

use Fisdap\Attachments\Associations\AssociatedEntityFinder;
use Fisdap\Attachments\Associations\Entities\HasAttachments;
use Fisdap\Attachments\Associations\Repositories\StoresAttachments;
use Fisdap\Attachments\Configuration\AttachmentConfig;
use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Illuminate\Container\Container;

class AssociatedEntityFinderCest
{
    /**
     * @var Container|Mockery\MockInterface
     */
    private $containerMock;

    /**
     * @var AttachmentsKernel|Mockery\MockInterface
     */
    private $attachmentsKernelMock;

    /**
     * @var HasAttachments|Mockery\MockInterface
     */
    private $associatedEntityMock;

    /**
     * @var AssociatedEntityFinder
     */
    private $associatedEntityFinder;



    public function _before(UnitTester $I)
    {
        $this->containerMock = Mockery::mock(Container::class);
        $this->attachmentsKernelMock = Mockery::mock(AttachmentsKernel::class);
        $this->associatedEntityFinder = new AssociatedEntityFinder(
            $this->containerMock,
            $this->attachmentsKernelMock
        );
        $this->associatedEntityMock = Mockery::mock(HasAttachments::class);
    }


    public function _after(UnitTester $I)
    {
    }


    public function it_can_find_an_associated_entity(UnitTester $I)
    {
        // arrange
        $attachmentType = 'foo';
        $associatedEntityId = 12345;

        $this->associatedEntityRepositoryMock()->shouldReceive('findAssociatedEntity')->once()
            ->withArgs([$attachmentType, $associatedEntityId])->andReturn($this->associatedEntityMock);

        // act
        $associatedEntity = $this->associatedEntityFinder->find('foo', 12345);

        // assert
        $I->assertSame($this->associatedEntityMock, $associatedEntity);
    }


    /**
     * @return StoresAttachments|\Mockery\MockInterface
     */
    private function associatedEntityRepositoryMock()
    {
        $attachmentConfigStub = new AttachmentConfig();
        $attachmentConfigStub->associatedEntityRepositoryInterfaceName = StoresAttachments::class;

        $this->attachmentsKernelMock->shouldReceive('getAttachmentConfig')->once()
            ->andReturn($attachmentConfigStub);

        $associatedEntityRepositoryMock = Mockery::mock(StoresAttachments::class);

        $this->containerMock->shouldReceive('make')->once()->andReturn($associatedEntityRepositoryMock);

        return $associatedEntityRepositoryMock;
    }
}
