<?php namespace Fisdap\Members\Shifts;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Fisdap\Api\Shifts\Attachments\Entities\ShiftAttachment;
use Fisdap\Entity\ShiftLegacy;
use Illuminate\Contracts\Container\Container;
use Psr\Log\LoggerInterface;
use Fisdap\Api\Client\Shifts\Attachments\Gateway\ShiftAttachmentsGateway;


/**
 * Subscribes to ShiftLegacy Doctrine lifecycle events
 *
 * @package Fisdap\Members\Shifts
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class ShiftEventsSubscriber implements EventSubscriber
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @param Container       $container
     * @param LoggerInterface $logger
     */
    public function __construct(Container $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }


    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
        ];
    }


    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        if ( ! $args->getObject() instanceof ShiftLegacy) return;

        /** @var ShiftLegacy $shift */
        $shift = $args->getObject();

        if ( ! $shift->getAttachments()->isEmpty()) {
            $attachmentIds = [];

            /** @var ShiftAttachment $shiftAttachment */
            foreach ($shift->getAttachments() as $shiftAttachment) {
                $attachmentIds[] = $shiftAttachment->getId();

                // clear association with signoff Verifications
                $shiftAttachment->getVerifications()->clear();
                $args->getEntityManager()->flush($shiftAttachment);
            }

            // todo - inject this properly
            // delete attachments through API web service
            $shiftAttachmentsGateway = $this->container->make(ShiftAttachmentsGateway::class);

            $shiftAttachmentsGateway->delete($shift->getId(), $attachmentIds);
            $this->logger->debug("Deleted shift attachments: " . implode(',', $attachmentIds));
        }
    }
}