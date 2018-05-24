<?php

use AscendLearning\Lti\Entities\ToolProvider;
use Fisdap\Api\Products\Queries\Specifications\MatchingUserContext;
use Fisdap\Api\Support\FisdapUrls;
use Fisdap\Data\Product\ProductRepository;
use Fisdap\Members\Lti\Session\LtiToolProvidersSession;
use Franzl\Lti\Storage\DummyStorage;
use Franzl\Lti\ToolConsumer;
use Happyr\DoctrineSpecification\Spec;


/**
 * Class Lti_ConsumerController
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class Lti_ConsumerController extends Fisdap_Controller_Private
{
    /**
     * @var ProductRepository
     */
    private $productRepository;


    /**
     * Lti_ConsumerController constructor.
     *
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;

        ToolProvider::setDefaultOauthConsumerKey('fisdap-members');
    }


    /**
     * @param LtiToolProvidersSession $ltiToolProvidersSession
     *
     * @throws Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function indexAction(LtiToolProvidersSession $ltiToolProvidersSession)
    {
        $this->_helper->layout->disableLayout();

        /** @var ToolProvider $toolProvider */
        $toolProvider = $this->em->find(ToolProvider::class, $this->getParam('toolProviderId'));
        
        $this->validateProductAccess($toolProvider);

        $this->view->assign([
            'launchUrl' => $toolProvider->getLaunchUrl(),
            'launchParams' => $this->signedLaunchParams($toolProvider),
        ]);

        if (! is_null($toolProvider->getLogoutUrl())) {
            $ltiToolProvidersSession->addLaunchedTool(
                $toolProvider->getResourceLinkTitle(), $toolProvider->getLogoutUrl()
            );
        }
    }


    /**
     * @param ToolProvider $toolProvider
     *
     * @throws Exception
     */
    private function validateProductAccess(ToolProvider $toolProvider)
    {
        $userContextHasProduct = $this->productRepository->match(
                Spec::countOf(Spec::andX(
                    new MatchingUserContext($this->currentUser->context()->getRole()->getFullEntityClassName()),
                    Spec::eq('id', $this->currentUser->context()->getId(), 'userContext'),
                    Spec::eq('moodle_context', $toolProvider->getResourceLinkTitle()),
                    Spec::eq('moodle_course_id', $toolProvider->getContextId())
                ))
            ) > 0;

        if ($userContextHasProduct === false) {
            throw new \Exception(
                "User does not have access to LTI Tool Provider 
                {$toolProvider->getResourceLinkTitle()}:{$toolProvider->getContextTitle()}"
            );
        }
    }


    /**
     * @param ToolProvider $toolProvider
     *
     * @return array
     */
    private function launchParams(ToolProvider $toolProvider)
    {
        $launchParams = [
            "user_id"                            => $this->currentUser->user()->getUsername(),
            "roles"                              => $this->ltiRole($this->currentUser->context()->getRole()->getName()),

            "resource_link_id"                   => uniqid('', true),

            // this is required by version 2.7.x of the Moodle LTI Provider plugin
            "resource_link_title"                => $toolProvider->getResourceLinkTitle(),

            "context_id"                         => $toolProvider->getContextId(),
            "context_title"                      => $toolProvider->getContextTitle(),

            "lis_person_name_full"               => $this->currentUser->user()->getFullName(),
            "lis_person_name_family"             => $this->currentUser->user()->getLastName(),
            "lis_person_name_given"              => $this->currentUser->user()->getFirstName(),
            "lis_person_contact_email_primary"   => $this->currentUser->user()->getEmail(),
            
            "launch_presentation_return_url"     => FisdapUrls::getMembersUrl()
        ];

        foreach ($toolProvider->getCustomParameters() as $customParameter) {
            $launchParams['custom_' . key($customParameter)] = current($customParameter);
        }

        // Basic LTI uses OAuth to sign requests
        // OAuth Core 1.0 spec: http://oauth.net/core/1.0/
        $launchParams["oauth_callback"] = "about:blank";
        $launchParams["oauth_consumer_key"] = $toolProvider->getOauthConsumerKey();
        $launchParams["oauth_version"] = "1.0";
        $launchParams["oauth_nonce"] = uniqid('', true);
        $launchParams["oauth_timestamp"] = (new DateTime())->getTimestamp();
        $launchParams["oauth_signature_method"] = "HMAC-SHA1";

        return $launchParams;
    }


    /**
     * @param ToolProvider $toolProvider
     *
     * @return array
     */
    private function signedLaunchParams(ToolProvider $toolProvider)
    {
        $toolConsumer = new ToolConsumer($toolProvider->getOauthConsumerKey(), new DummyStorage());
        $toolConsumer->secret = $toolProvider->getSecret();

        return $toolConsumer->signParameters(
            $toolProvider->getLaunchUrl(),
            'basic-lti-launch-request',
            'LTI-1p0',
            $this->launchParams($toolProvider)
        );
    }


    /**
     * @param string $fisdapRoleName
     *
     * @return string
     */
    private function ltiRole($fisdapRoleName)
    {
        if ($fisdapRoleName === 'instructor') {
            return 'Teacher';
        }
        
        return 'Student';
    }
}
