<?php namespace AscendLearning\Lti\Storage;

use AscendLearning\Lti\Entities\Consumer;
use AscendLearning\Lti\Entities\Context;
use AscendLearning\Lti\Entities\Nonce;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Franzl\Lti\ConsumerNonce;
use Franzl\Lti\ResourceLink;
use Franzl\Lti\ResourceLinkShareKey;
use Franzl\Lti\Storage\AbstractStorage;
use Franzl\Lti\ToolConsumer;
use Franzl\Lti\User;
use Psr\Log\LoggerInterface;

/**
 * Class DoctrineStorage
 *
 * @package AscendLearning\Lti\Storage
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class DoctrineStorage extends AbstractStorage
{
    /**
     * @var EntityManagerInterface|EntityManager
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger = null;


    /**
     * DoctrineStorage constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface|null   $logger
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger = null)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }


    /**
     * Load tool consumer object.
     *
     * @param ToolConsumer $consumer ToolConsumer object
     *
     * @return boolean True if the tool consumer object was successfully loaded
     */
    public function toolConsumerLoad($consumer)
    {
        try {
            /** @var Consumer $consumerEntity */
            $consumerEntity = $this->entityManager->find(Consumer::class, $consumer->getKey());

            if (is_null($consumerEntity)) {
                return false;
            }
        } catch (\Exception $e) {
            $this->log('critical', $e->getMessage(), (array) $consumer);

            return false;
        }

        $this->hydrateToolConsumer($consumerEntity, $consumer);

        return true;
    }


    /**
     * Save tool consumer object.
     *
     * @param ToolConsumer $consumer Consumer object
     *
     * @return boolean True if the tool consumer object was successfully saved
     */
    public function toolConsumerSave($consumer)
    {
        $now = new \DateTime;

        $from = !is_null($consumer->enableFrom) ? new \DateTime(
            date("{$this->date_format} {$this->time_format}", $consumer->enableFrom)
        ) : null;

        $until = !is_null($consumer->enableUntil) ? new \DateTime(
            date("{$this->date_format} {$this->time_format}", $consumer->enableUntil)
        ) : null;

        $last = !is_null($consumer->lastAccess) ? new \DateTime(date($this->date_format, $consumer->lastAccess)) : null;

        try {
            if (is_null($consumer->created)) {
                $consumerEntity = new Consumer(
                    $consumer->getKey(),
                    $consumer->name,
                    $consumer->secret,
                    $consumer->protected,
                    $consumer->enabled
                );

                $consumerEntity->setLtiVersion($consumer->ltiVersion);
                $consumerEntity->setConsumerName($consumer->consumerName);
                $consumerEntity->setConsumerVersion($consumer->consumerVersion);
                $consumerEntity->setConsumerGuid($consumer->consumerGuid);
                $consumerEntity->setCssPath($consumer->cssPath);
                $consumerEntity->setEnableFrom($from);
                $consumerEntity->setEnableUntil($until);
                $consumerEntity->setLastAccess($last);

                $this->entityManager->persist($consumerEntity);

                $consumer->created = $now->format("{$this->date_format} {$this->time_format}");
            } else {
                /** @var Consumer $consumerEntity */
                $consumerEntity = $this->entityManager->find(Consumer::class, $consumer->getKey());

                if (is_null($consumerEntity)) {
                    return false;
                }

                $consumerEntity->setKey($consumer->getKey());
                $consumerEntity->setName($consumer->name);
                $consumerEntity->setSecret($consumer->secret);
                $consumerEntity->setLtiVersion($consumer->ltiVersion);
                $consumerEntity->setConsumerName($consumer->consumerName);
                $consumerEntity->setConsumerVersion($consumer->consumerVersion);
                $consumerEntity->setConsumerGuid($consumer->consumerGuid);
                $consumerEntity->setCssPath($consumer->cssPath);
                $consumerEntity->setProtected($consumer->protected);
                $consumerEntity->setEnabled($consumer->enabled);
                $consumerEntity->setEnableFrom($from);
                $consumerEntity->setEnableUntil($until);
                $consumerEntity->setLastAccess($last);
            }

            $this->entityManager->flush();

            $consumer->updated = $now->format("{$this->date_format} {$this->time_format}");

            return true;
        } catch (\Exception $e) {
            $this->log('critical', $e->getMessage(), (array) $consumer);

            return false;
        }
    }


    /**
     * Delete tool consumer object.
     *
     * @param ToolConsumer $consumer Consumer object
     *
     * @return boolean True if the tool consumer object was successfully deleted
     */
    public function toolConsumerDelete($consumer)
    {
        try {
            /** @var Consumer $consumerEntity */
            $consumerEntity = $this->entityManager->find(Consumer::class, $consumer->getKey());

            if (is_null($consumerEntity)) {
                return false;
            }

            $this->entityManager->remove($consumerEntity);
            $this->entityManager->flush();

            $consumer->initialise();

            return true;
        } catch (\Exception $e) {
            $this->log('critical', $e->getMessage(), (array) $consumer);

            return false;
        }
    }


    /**
     * Load tool consumer objects.
     *
     * @return ToolConsumer[] Array of all defined ToolConsumer objects
     */
    public function toolConsumerList()
    {
        $consumers = [];

        /** @var Consumer[] $consumerEntities */
        $consumerEntities = $this->entityManager->getRepository(Consumer::class)->findAll();

        foreach ($consumerEntities as $consumerEntity) {
            $consumer = new ToolConsumer($consumerEntity->getKey(), $this);

            $this->hydrateToolConsumer($consumerEntity, $consumer);

            $consumers[] = $consumer;
        }

        return $consumers;
    }


    /**
     * @param Consumer     $consumer
     * @param ToolConsumer $toolConsumer
     */
    private function hydrateToolConsumer($consumer, $toolConsumer)
    {
        $toolConsumer->name = $consumer->getName();
        $toolConsumer->secret = $consumer->getSecret();
        $toolConsumer->ltiVersion = $consumer->getLtiVersion();
        $toolConsumer->consumerName = $consumer->getConsumerName();
        $toolConsumer->consumerVersion = $consumer->getConsumerVersion();
        $toolConsumer->consumerGuid = $consumer->getConsumerGuid();
        $toolConsumer->cssPath = $consumer->getCssPath();
        $toolConsumer->protected = $consumer->isProtected();
        $toolConsumer->enabled = $consumer->isEnabled();

        $toolConsumer->enableFrom = !is_null($consumer->getEnableFrom()) ? $consumer->getEnableFrom()
            ->getTimestamp() : null;

        $toolConsumer->enableUntil = !is_null($consumer->getEnableUntil()) ? $consumer->getEnableUntil()
            ->getTimestamp() : null;

        $toolConsumer->lastAccess = !is_null($consumer->getLastAccess()) ? $consumer->getLastAccess()
            ->getTimestamp() : null;

        $toolConsumer->created = $consumer->getCreated()->getTimestamp();
        $toolConsumer->updated = $consumer->getUpdated()->getTimestamp();
    }


    /**
     * Load resource link object.
     *
     * @param ResourceLink $resource_link Resource_Link object
     *
     * @return boolean True if the resource link object was successfully loaded
     */
    public function resourceLinkLoad($resource_link)
    {
        try {
            /** @var Context $context */
            $context = $this->entityManager->getRepository(Context::class)->findOneBy([
                'consumer' => $resource_link->getKey(),
                'id' => $resource_link->getId()
            ]);

            if (is_null($context)) {
                return false;
            }

            $resource_link->lti_context_id = $context->getLtiContextId();
            $resource_link->lti_resource_id = $context->getLtiResourceId();
            $resource_link->title = $context->getTitle();

            if (is_string($context->getSettings())) {
                $resource_link->settings = json_decode($context->getSettings(), true);
                if (!is_array($resource_link->settings)) {
                    $resource_link->settings = unserialize($context->getSettings());  // check for old serialized setting
                }
                if (!is_array($resource_link->settings)) {
                    $resource_link->settings = [];
                }
            } else {
                $resource_link->settings = [];
            }

            $resource_link->primary_consumer_key = $context->getPrimaryContext()->getConsumer()->getKey();
            $resource_link->primary_resource_link_id = $context->getPrimaryContext()->getId();

            $resource_link->share_approved = $context->getShareApproved();

            $resource_link->created = $context->getCreated()->getTimestamp();
            $resource_link->updated = $context->getUpdated()->getTimestamp();

            return true;
        } catch (\Exception $e) {
            $this->log('critical', $e->getMessage(), (array) $resource_link);

            return false;
        }
    }


    /**
     * Save resource link object.
     *
     * @param ResourceLink $resource_link Resource_Link object
     *
     * @return boolean True if the resource link object was successfully saved
     */
    public function resourceLinkSave($resource_link)
    {
        $now = new \DateTime;

        $settingsValue = json_encode($resource_link->settings);
        $key = $resource_link->getKey();
        $id = $resource_link->getId();
        $previous_id = $resource_link->getId(true);

        try {
            if (is_null($resource_link->created)) {

                /** @var Consumer $consumerEntity */
                $consumerEntity = $this->entityManager->find(Consumer::class, $key);

                $context = new Context($consumerEntity, $id, $resource_link->title, $settingsValue);

                $context->setLtiContextId($resource_link->lti_context_id);
                $context->setLtiResourceId($resource_link->lti_resource_id);

                $primaryContext = $this->entityManager->getRepository(Context::class)->findOneBy([
                    'consumer' => $key,
                    'id' => $id
                ]);

                if ($primaryContext instanceof Context) {
                    $context->setPrimaryContext($primaryContext);
                }

                $context->setShareApproved($resource_link->share_approved);
            } elseif ($id == $previous_id) {

                /** @var Context $context */
                $context = $this->entityManager->getRepository(Context::class)->findOneBy([
                    'consumer' => $key,
                    'id' => $id
                ]);

                $context->setLtiContextId($resource_link->lti_context_id);
                $context->setLtiResourceId($resource_link->lti_resource_id);
                $context->setTitle($resource_link->title);
                $context->setSettings($settingsValue);

                $primaryContext = $this->entityManager->getRepository(Context::class)->findOneBy([
                    'consumer' => $key,
                    'id' => $id
                ]);

                if ($primaryContext instanceof Context) {
                    $context->setPrimaryContext($primaryContext);
                }

                $context->setShareApproved($resource_link->share_approved);
            } else {

                /** @var Context $context */
                $context = $this->entityManager->getRepository(Context::class)->findOneBy([
                    'consumer' => $key,
                    'id' => $previous_id
                ]);

                $context->setId($id);

                $context->setLtiContextId($resource_link->lti_context_id);
                $context->setLtiResourceId($resource_link->lti_resource_id);
                $context->setTitle($resource_link->title);
                $context->setSettings($settingsValue);

                $primaryContext = $this->entityManager->getRepository(Context::class)->findOneBy([
                    'consumer' => $key,
                    'id' => $id
                ]);

                if ($primaryContext instanceof Context) {
                    $context->setPrimaryContext($primaryContext);
                }

                $context->setShareApproved($resource_link->share_approved);
            }

            $this->entityManager->flush();

            $resource_link->updated = $now->format("{$this->date_format} {$this->time_format}");

            return true;
        } catch (\Exception $e) {
            $this->log('critical', $e->getMessage(), (array) $resource_link);

            return false;
        }
    }


    /**
     * Delete resource link object.
     *
     * @param ResourceLink $resource_link Resource_Link object
     *
     * @return boolean True if the Resource_Link object was successfully deleted
     */
    public function resourceLinkDelete($resource_link)
    {
        try {
            /** @var Context $context */
            $context = $this->entityManager->getRepository(Context::class)->findOneBy([
                'consumer' => $resource_link->getKey(),
                'id' => $resource_link->getId()
            ]);

            if (is_null($context)) {
                return false;
            }

            $this->entityManager->remove($context);
            $this->entityManager->flush();

            $resource_link->initialise();

            return true;
        } catch (\Exception $e) {
            $this->log('critical', $e->getMessage(), (array) $resource_link);

            return false;
        }
    }


    /**
     * Get array of user objects.
     *
     * @param ResourceLink $resource_link Resource link object
     * @param boolean      $local_only    True if only users within the resource link are to be returned (excluding users sharing this resource link)
     * @param int          $id_scope      Scope value to use for user IDs
     *
     * @return array Array of User objects
     */
    public function resourceLinkGetUserResultSourcedIDs($resource_link, $local_only, $id_scope)
    {
        // TODO: Implement resourceLinkGetUserResultSourcedIDs() method.
    }

    /**
     * Get array of shares defined for this resource link.
     *
     * @param ResourceLink $resource_link Resource_Link object
     *
     * @return array Array of ResourceLinkShare objects
     */
    public function resourceLinkGetShares($resource_link)
    {
        // TODO: Implement resourceLinkGetShares() method.
    }


    /**
     * Load nonce object.
     *
     * @param ConsumerNonce $nonce Nonce object
     *
     * @return boolean True if the nonce object was successfully loaded
     */
    public function consumerNonceLoad($nonce)
    {
        // Delete any expired nonce values
        try {
            $critera = Criteria::create();
            $critera->where($critera->expr()->lte('expires', new \DateTime));
            $expiredNonces = $this->entityManager->getRepository(Nonce::class)->matching($critera);

            foreach ($expiredNonces as $expiredNonce) {
                $this->entityManager->remove($expiredNonce);
            }

            $this->entityManager->flush();
        } catch (\Exception $e) {
            $this->log('critical', $e->getMessage(), (array) $nonce);
        }

        // load the nonce
        try {
            $nonce = $this->entityManager->getRepository(Nonce::class)->findOneBy([
                'consumer' => $nonce->getKey(),
                'value' => $nonce->getValue()
            ]);

            if (is_null($nonce)) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->log('critical', $e->getMessage(), (array) $nonce);

            return false;
        }
    }


    /**
     * Save nonce object.
     *
     * @param ConsumerNonce $nonce Nonce object
     *
     * @return boolean True if the nonce object was successfully saved
     */
    public function consumerNonceSave($nonce)
    {
        try {
            /** @var Consumer $consumer */
            $consumer = $this->entityManager->find(Consumer::class, $nonce->getKey());

            $expires = new \DateTime(date("{$this->date_format} {$this->time_format}", $nonce->expires));

            $nonceEntity = new Nonce($consumer, $nonce->getValue(), $expires);

            $this->entityManager->persist($nonceEntity);
            $this->entityManager->flush();

            return true;
        } catch (\Exception $e) {
            $this->log('critical', $e->getMessage(), (array) $nonce);

            return false;
        }
    }


    /**
     * Load resource link share key object.
     *
     * @param ResourceLinkShareKey $share_key Resource_Link share key object
     *
     * @return boolean True if the resource link share key object was successfully loaded
     */
    public function resourceLinkShareKeyLoad($share_key)
    {
        // TODO: Implement resourceLinkShareKeyLoad() method.
        return false;
    }

    /**
     * Save resource link share key object.
     *
     * @param ResourceLinkShareKey $share_key Resource link share key object
     *
     * @return boolean True if the resource link share key object was successfully saved
     */
    public function resourceLinkShareKeySave($share_key)
    {
        // TODO: Implement resourceLinkShareKeySave() method.
        return false;
    }

    /**
     * Delete resource link share key object.
     *
     * @param ResourceLinkShareKey $share_key Resource link share key object
     *
     * @return boolean True if the resource link share key object was successfully deleted
     */
    public function resourceLinkShareKeyDelete($share_key)
    {
        // TODO: Implement resourceLinkShareKeyDelete() method.
        return false;
    }

    /**
     * Load user object.
     *
     * @param User $user User object
     *
     * @return boolean True if the user object was successfully loaded
     */
    public function userLoad($user)
    {
        // TODO: Implement userLoad() method.
        return false;
    }

    /**
     * Save user object.
     *
     * @param User $user User object
     *
     * @return boolean True if the user object was successfully saved
     */
    public function userSave($user)
    {
        // TODO: Implement userSave() method.
        return false;
    }

    /**
     * Delete user object.
     *
     * @param User $user User object
     *
     * @return boolean True if the user object was successfully deleted
     */
    public function userDelete($user)
    {
        // TODO: Implement userDelete() method.
        return false;
    }


    /**
     * @param string $severity
     * @param string $message
     * @param array  $context
     */
    private function log($severity, $message, array $context = [])
    {
        if ($this->logger !== null) {
            $this->logger->$severity($message, $context);
        }
    }
}
