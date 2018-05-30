<?php namespace Fisdap\Members\Lti;

use Fisdap\Doctrine\Extensions\ColumnType\UuidType;
use Fisdap\Members\Lti\Session\LtiSession;
use Fisdap\Members\Lti\Session\LtiSessionUser;
use Franzl\Lti\ToolProvider;
use Psr\Log\LoggerInterface;
use Rhumsaa\Uuid\Uuid;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\SapiEmitter;

/**
 * Handles LTI launch request, initializing an LtiSession
 *
 * @package Fisdap\Members\Lti
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class LaunchHandler
{
    /**
     * @var LtiSession
     */
    private $session;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SapiEmitter
     */
    private $emitter;


    /**
     * LaunchHandler constructor.
     *
     * @param LtiSession $session
     * @param LoggerInterface $logger
     * @param SapiEmitter $emitter
     */
    public function __construct(LtiSession $session, LoggerInterface $logger, SapiEmitter $emitter)
    {
        $this->session = $session;
        $this->logger = $logger;
        $this->emitter = $emitter;
    }


    /**
     * @param ToolProvider $toolProvider
     *
     * @throws \Exception
     */
    public function __invoke(ToolProvider $toolProvider)
    {
        $this->logger->debug("LTI ToolProvider User", json_decode(json_encode($toolProvider->user), true));

        $this->initializeSession($toolProvider);

        $this->emitter->emit(new RedirectResponse('/account/lti'));
    }


    /**
     * @param ToolProvider $toolProvider
     *
     * @throws \Exception
     */
    private function initializeSession(ToolProvider $toolProvider)
    {
        $this->initLtiSessionUser($toolProvider);

        $this->convertPsgId($this->session->getUser()->id);

        $this->initRole($toolProvider);

        $this->initFisdapModule($toolProvider);

        $this->session->setContextTitle(explode(':', $toolProvider->resourceLink->title)[0]);

        $this->logger->info('LTI session initialized', $this->session->toArray());
    }


    /**
     * @param ToolProvider $toolProvider
     */
    private function initLtiSessionUser(ToolProvider $toolProvider)
    {
        $this->session->setUser(new LtiSessionUser);
        $this->session->getUser()->id = $toolProvider->user->getId();
        $this->session->getUser()->firstName = $toolProvider->user->firstName;
        $this->session->getUser()->lastName = $toolProvider->user->lastName;
        $this->session->getUser()->email = $toolProvider->user->email;
        $this->session->getUser()->courseId = $toolProvider->resourceLink->getSetting('custom_course_id', null);
        $this->session->getUser()->programId = $toolProvider->resourceLink->getSetting('custom_program_id');
        $this->session->getUser()->isbns = explode(
            ',',
            $toolProvider->resourceLink->getSetting('custom_fisdap_isbns')
        );
    }


    /**
     * Converts PSG/JBL UCS GUID to a string that can be stored as binary
     *
     * @param $guid
     */
    private function convertPsgId(&$guid)
    {
        if (Uuid::isValid($guid)) {
            $guid = UuidType::transposeUuid($guid);

            $this->session->hasPsgId(true);
        }
    }


    /**
     * @param ToolProvider $toolProvider
     *
     * @throws LaunchException
     */
    private function initRole(ToolProvider $toolProvider)
    {
        if ($toolProvider->user->isStaff()) {
            $this->session->getUser()->role = LtiSessionUser::INSTRUCTOR_ROLE;
        } elseif ($toolProvider->user->isLearner()) {
            $this->session->getUser()->role = LtiSessionUser::STUDENT_ROLE;
        } else {
            throw new LaunchException('Unknown/unsupported role: ' . implode(', ', $toolProvider->user->roles));
        }
    }


    /**
     * @param ToolProvider $toolProvider
     */
    private function initFisdapModule(ToolProvider $toolProvider)
    {
        switch ($toolProvider->resourceLink->lti_resource_id) {
            case 1:
                $this->session->setFisdapModule(LtiSession::SCHEDULER);
                break;
            case 2:
                $this->session->setFisdapModule(LtiSession::SKILLS_TRACKER);
                break;
            case 3:
                $this->session->setFisdapModule(LtiSession::LEARNING_CENTER);
                break;
            default:
                $this->session->setFisdapModule(LtiSession::MY_FISDAP);
                break;
        }
    }
}
