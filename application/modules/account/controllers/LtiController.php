<?php

use Fisdap\Api\Products\Finder\FindsProducts;
use Fisdap\Api\Users\Finder\FindsUsers;
use Fisdap\Data\User\UserContext\UserContextRepository;
use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\Product;
use Fisdap\Entity\ProductPackage;
use Fisdap\Entity\SerialNumberLegacy;
use Fisdap\Entity\User;
use Fisdap\Entity\UserContext;
use Fisdap\Members\Auth\CommonAuthController;
use Fisdap\Members\BitwiseAnd;
use Fisdap\Members\Lti\AccountCreationJobsBuilder;
use Fisdap\Members\Lti\AuthAdapter;
use Fisdap\Members\Lti\LaunchException;
use Fisdap\Members\Lti\Session\FisdapUserIdentity;
use Fisdap\Members\Lti\Session\LtiSession;
use Happyr\DoctrineSpecification\Spec;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Support\Collection;

/**
 * Class Account_LtiController
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class Account_LtiController extends CommonAuthController
{
    /**
     * @var FindsUsers
     */
    private $usersFinder;

    /**
     * @var LtiSession
     */
    private $ltiSession;

    /**
     * @var UserContextRepository
     */
    private $userContextRepository;

    /**
     * @var FindsProducts
     */
    private $productsFinder;

    /**
     * @var AccountCreationJobsBuilder
     */
    private $accountCreationJobsBuilder;

    /**
     * @var BusDispatcher
     */
    private $busDispatcher;


    /**
     * Account_LtiController constructor.
     *
     * @param FindsUsers                 $usersFinder
     * @param UserRepository             $userRepository
     * @param LtiSession                 $ltiSession
     * @param UserContextRepository      $userContextRepository
     * @param FindsProducts              $productsFinder
     * @param AccountCreationJobsBuilder $accountCreationJobsBuilder
     * @param BusDispatcher              $busDispatcher
     */
    public function __construct(
        UserRepository $userRepository,
        FindsUsers $usersFinder,
        LtiSession $ltiSession,
        UserContextRepository $userContextRepository,
        FindsProducts $productsFinder,
        AccountCreationJobsBuilder $accountCreationJobsBuilder,
        BusDispatcher $busDispatcher
    ) {
        parent::__construct($userRepository);
        $this->usersFinder = $usersFinder;
        $this->ltiSession = $ltiSession;
        $this->userContextRepository = $userContextRepository;
        $this->accountCreationJobsBuilder = $accountCreationJobsBuilder;
        $this->busDispatcher = $busDispatcher;
        $this->productsFinder = $productsFinder;
    }


    public function init()
    {
        parent::init();

        $this->view->returnUrl = $this->ltiSession->getReturnUrl();
        $this->view->returnTitle = $this->ltiSession->getContextTitle();
    }


    public function indexAction()
    {
        $this->handleExistingLogin();

        $this->handleExistingUser();

        $this->handleUnlinkedAccounts();
    }


    private function handleExistingLogin()
    {
        if (! Zend_Auth::getInstance()->hasIdentity()) {
            return;
        }

        if ($this->currentUser->user()->getEmail() !== $this->ltiSession->getUser()->email) {
            $this->redirect('/account/lti/user-mismatch');
        }

        $userContext = $this->getOrCreateUserContext($this->currentUser->user());
        $user = $this->currentUser->getWritableUser();
        $user->setCurrentUserContext($userContext);
        $this->userRepository->update($user);
        $this->currentUser->reload();

        $this->updateProductAccess($userContext);

        $this->redirect($this->ltiSession->launchAndGetModuleRedirect());
    }


    private function handleExistingUser()
    {
        // check if LTI user already has a linked Fisdap account
        if ($this->ltiSession->hasPsgId()) {
            $user = $this->usersFinder->findOneByPsgUserId($this->ltiSession->getUser()->id);
        } else {
            $user = $this->usersFinder->findOneByLtiUserId($this->ltiSession->getUser()->id);
        }

        if ($user instanceof User) {
            $this->ltiSession->setFisdapUsername($user->getUsername());
            $userContext = $this->getOrCreateUserContext($user);
            
            $this->updateProductAccess($userContext);
            
            $this->login($user, $userContext);
        }
    }


    private function handleUnlinkedAccounts()
    {
        $usersByEmail = $this->usersFinder->findByEmail($this->ltiSession->getUser()->email);
        $usersCount = count($usersByEmail);

        if ($usersCount > 1) {
            $this->ltiSession->setFisdapAccounts(
                array_map(
                    function (User $user) {
                        $fisdapUser = new FisdapUserIdentity;
                        $fisdapUser->username = $user->getUsername();
                        $fisdapUser->fullName = $user->getFullName();

                        return $fisdapUser;
                    },
                    $usersByEmail
                )
            );

            $this->redirect('/account/lti/choose-account');
        } elseif ($usersCount == 1) {
            $this->ltiSession->setFisdapUsername($usersByEmail[0]->getUsername());
        }

        $this->redirect('/account/lti/login-form');
    }


    public function userMismatchAction()
    {
        $this->view->headScript()->appendFile("/js/account/lti/user-mismatch.js");

        $this->view->pageTitle = "User Mismatch";
    }


    public function chooseAccountAction()
    {
        $this->view->headLink()->appendStylesheet('/css/login/index.css');

        $this->view->pageTitle = "Set up account";

        $users = $this->ltiSession->getFisdapAccounts();

        $tableRowConfig = [];

        foreach ($users as $user) {
            $tableRowConfig[$user->username]['value'] = $user->username;
            $tableRowConfig[$user->username]['content'] = [$user->fullName . " - " . $user->username];
        }

        ksort($tableRowConfig);

        $this->view->tableConfig = ['rows' => $tableRowConfig];
    }


    public function loginFormAction()
    {
        $this->view->headLink()->appendStylesheet('/css/login/index.css');

        $this->view->pageTitle = 'Set up account';

        if ($this->hasParam('username')) {
            $username = $this->getParam('username');
            $this->ltiSession->setFisdapUsername($username);
        } else {
            $username = $this->ltiSession->getFisdapUsername();
        }

        $this->view->username = $username ?: null;
        $this->view->disableUsername = isset($username) ? 'disabled' : '';

        //if we've been kicked back to the login screen for a failed auth attempt, unlock the username field.
        $messages = $this->flashMessenger->getMessages();

        if (!empty($messages)) {
            $this->view->disableUsername = '';
        }
    }


    public function authAction()
    {
        if (is_null($this->ltiSession->getFisdapUsername())) {
            $username = $this->getParam('username');
            $this->ltiSession->setFisdapUsername($username);
        }

        $result = $this->authenticate($this->ltiSession->getFisdapUsername(), $this->getParam('password'));

        if ($result->isValid()) {
            $user = $this->userRepository->getOneByUsername($this->ltiSession->getFisdapUsername());

            $userContext = $this->getOrCreateUserContext($user);
            
            $user->setCurrentUserContext($userContext);
            
            $this->updateProductAccess($userContext);

            if ($this->ltiSession->hasPsgId()) {
                $user->setPsgUserId($this->ltiSession->getUser()->id);
            } else {
                $user->setLtiUserId($this->ltiSession->getUser()->id);
            }

            $this->userRepository->update($user);

            $this->currentUser->setUser($user);

            $this->processSuccessfulLogin(null, true, $this->ltiSession->launchAndGetModuleRedirect());
        }

        foreach ($result->getMessages() as $message) {
            $this->flashMessenger->addMessage($message);
        }

        if (!empty($this->ltiSession->getFisdapAccounts())) {
            $this->redirect('/account/lti/choose-account');
        }

        $this->redirect('/account/lti/login-form');
    }


    /**
     * @throws Zend_Form_Exception
     */
    public function createUserAction()
    {
        if ($this->ltiSession->getUser() === null) {
            $this->redirect('/');
        }

        $this->view->pageTitle = "User Information";

        $this->view->form = $form = new Account_Form_User;

        $form->populate(
            [
                'firstName' => $this->ltiSession->getUser()->firstName,
                'lastName'  => $this->ltiSession->getUser()->lastName,
                'email'     => $this->ltiSession->getUser()->email
            ]
        );

        $form->getElement('firstName')->setAttrib('disabled', 'disabled');
        $form->getElement('lastName')->setAttrib('disabled', 'disabled');
        $form->getElement('email')->setAttrib('disabled', 'disabled');


        if ($this->getRequest()->isPost()) {
            $productsOrPackages = [];
            $serialNumber = null;
            
            if ($this->ltiSession->getUser()->role === 'student') {
                $productsOrPackages = $this->getProductsOrPackages();

                $serialNumber = $this->createSerialNumber($productsOrPackages);
            }
            

            $createUserJob = $this->accountCreationJobsBuilder->buildCreateUserJobWithContext(
                $this->ltiSession->getUser()->programId,
                $this->ltiSession->getUser()->courseId,
                $this->ltiSession->getUser()->role,
                $productsOrPackages,
                is_null($serialNumber) ? [] : [$serialNumber->getNumber()],
                $this->ltiSession->getUser()->firstName,
                $this->ltiSession->getUser()->lastName,
                $username = $this->ltiSession->getUser()->id,
                $this->ltiSession->getUser()->email,
                $this->ltiSession->hasPsgId() ? null : $this->ltiSession->getUser()->id,
                $this->ltiSession->hasPsgId() ? $this->ltiSession->getUser()->id : null,
                !empty($this->getParam('birth_date')) ? new \DateTime($this->getParam('birth_date')) : null,
                $this->hasParam('gender') ? $this->getParam('gender') : null,
                $this->getParam('ethnicity')
            );

            /** @var User $user */
            $user = $this->busDispatcher->dispatch($createUserJob);

            // Auto assign requirements if the student has scheduler
            if ($serialNumber->hasScheduler()) {
                $user->getCurrentUserContext()->autoAttachRequirements();
            }

            $this->login($user, $user->getAllUserContexts()->first(), true);
        }
    }

    
    public function instructorIntroAction()
    {
        $this->view->pageTitle = "Welcome to Fisdap!";
        
        $instructorContexts = $this->userContextRepository->match(Spec::andX(
            Spec::eq('program', $this->ltiSession->getUser()->programId),
            Spec::leftJoin('role', 'role'),
            Spec::eq('name', 'instructor', 'role'),
            Spec::leftJoin('instructorRoleData', 'instructor'),
            new BitwiseAnd('permissions', 2, 'instructor'),
            Spec::orderBy('start_date')
        ));
        
        $this->view->instructorContexts = !empty($instructorContexts) ? $instructorContexts : null;
        
        if ($this->getRequest()->isPost()) {
            $user = $this->currentUser->getWritableUser();

            $user->setDemo(false);
            $this->userRepository->update($user);

            $this->currentUser->reload();
            
            $this->redirect($this->ltiSession->getModuleRedirect());
        }
    }
    

    /**
     * @param User $user
     *
     * @return UserContext
     */
    private function getOrCreateUserContext(User $user)
    {
        $productsOrPackages = $this->getProductsOrPackages();

        $spec = Spec::andX(
            Spec::join('user', 'user'),
            Spec::join('role', 'role'),
            Spec::join('program', 'program'),
            Spec::eq('id', $user->getId(), 'user'),
            Spec::eq('name', $this->ltiSession->getUser()->role, 'role'),
            Spec::eq('id', $this->ltiSession->getUser()->programId, 'program')
        );

        if ($this->ltiSession->getUser()->role === 'student') {
            $spec->andX(Spec::join('certification_level', 'certification_level'));
            $spec->andX(Spec::eq(
                'id',
                $this->getCertLevelIdFromFirstProductOrPackage($productsOrPackages),
                'certification_level'
            ));
        }

        $contexts = Collection::make($this->userContextRepository->match($spec));


        // check for context matching course
        /** @var Collection[] $contextsByCourse */
        $contextsByCourse = $contexts->groupBy(function (UserContext $userContext) {
            return $userContext->getCourseId();
        })->all();

        if (array_key_exists($this->ltiSession->getUser()->courseId, $contextsByCourse)) {
            $this->logger->debug('Found context matching course');
            return $contextsByCourse[$this->ltiSession->getUser()->courseId]->first();
        } elseif (! $contexts->isEmpty()) {
            // no context matching course was found, link course to first matching context
            $this->logger->debug('No context matching course was found; linking course to first context matching LTI criteria');
            $contexts->first()->setCourseId($this->ltiSession->getUser()->courseId);
            return $contexts->first();
        }


        // no contexts were found, create one
        $this->logger->debug('No context matching LTI criteria was found. Creating one...');

        $serialNumber = $this->createSerialNumber($productsOrPackages);

        return $this->busDispatcher->dispatch($this->accountCreationJobsBuilder->buildCreateUserContextJob(
            $this->ltiSession->getUser()->programId,
            $this->ltiSession->getUser()->courseId,
            $this->ltiSession->getUser()->role,
            $productsOrPackages,
            [$serialNumber->getNumber()],
            $user->getId()
        ));
    }


    /**
     * @param User        $user
     * @param UserContext $context
     * @param bool        $isNewUser
     *
     * @throws LaunchException
     */
    private function login(User $user, UserContext $context, $isNewUser = false)
    {
        $adapter = new AuthAdapter($user->getUsername());
        $auth = Zend_Auth::getInstance();
        $auth->authenticate($adapter);

        $this->updateUserProfile($user, $isNewUser);

        $user->setCurrentUserContext($context);
        $this->userRepository->update($user);

        $this->currentUser->setUser($user);

        $this->processSuccessfulLogin(null, true, $this->ltiSession->launchAndGetModuleRedirect());
    }


    /**
     * Update "ghost account" user profiles
     *
     * @param User $user
     * @param bool $isNewUser
     */
    private function updateUserProfile(User $user, $isNewUser = false)
    {
        if ($isNewUser === true) {
            return;
        }

        if ($user->getUsername() !== $this->ltiSession->getUser()->id) {
            return;
        }

        $changedFields = [];

        $currentFirstName = $user->getFirstName();
        $currentLastName = $user->getLastName();
        $currentEmail = $user->getEmail();

        $ltiFirstName = $this->ltiSession->getUser()->firstName;
        $ltiLastName = $this->ltiSession->getUser()->lastName;
        $ltiEmail = $this->ltiSession->getUser()->email;

        if ($currentFirstName !== $ltiFirstName) {
            $user->setFirstName($ltiFirstName);
            $changedFields[] = 'firstName';
        }

        if ($currentLastName !== $ltiLastName) {
            $user->setLastName($ltiLastName);
            $changedFields[] = 'lastName';
        }

        if ($currentEmail !== $ltiEmail) {
            $user->setEmail($ltiEmail);
            $changedFields[] = 'email';
        }

        if (count($changedFields) > 0) {
            $this->logger->info(
                'Updating user profile with LTI launch data',
                ['userId' => $user->getId(), 'changedFields' => $changedFields]
            );
        }
    }


    /**
     * @return Product[]|ProductPackage[]
     */
    private function getProductsOrPackages()
    {
        $productsOrPackages = array();
        if (is_array($this->ltiSession->getUser()->isbns)) {
            $productsOrPackages = $this->productsFinder->findProductsAndPackagesByIsbns(
                $this->ltiSession->getUser()->isbns
            );
        }
        return $productsOrPackages;
    }


    /**
     * @param Product[]|ProductPackage[] $productsOrPackages
     *
     * @return SerialNumberLegacy
     */
    private function createSerialNumber(array $productsOrPackages)
    {
        $serialNumber = $this->busDispatcher->dispatch(
            $this->accountCreationJobsBuilder->buildCreateSerialNumberJob(
                $this->ltiSession->getUser()->programId,
                $this->getCertLevelIdFromFirstProductOrPackage($productsOrPackages),
                $productsOrPackages
            )
        );

        return $serialNumber;
    }


    /**
     * @param UserContext $userContext
     */
    private function updateProductAccess(UserContext $userContext)
    {
        $productsAndPackages = Collection::make($this->getProductsOrPackages());

        $configuration = $productsAndPackages->filter(function ($productOrPackage) {
            return $productOrPackage instanceof Product;
        })->merge($this->productsFinder->getUniqueProductsFromPackages(
            $productsAndPackages->filter(function ($productOrPackage) {
                return $productOrPackage instanceof ProductPackage;
            })->all()
        ))->merge($this->productsFinder->getUserContextProducts($userContext->getId()))
            ->unique(function (Product $product) {
                return $product->getId();
            })->sum(function (Product $product) {
                return $product->getConfiguration();
            });
        
        $serialNumber = $userContext->getPrimarySerialNumber();

        //not all users will have a serial number (ie. instructors without preceptor training)
        if (!is_null($serialNumber)) {
            if ($serialNumber->getConfiguration() !== $configuration) {
                $this->logger->info(
                    "Updating product access",
                    [
                        'userContextId' => $userContext->getId(),
                        'serialNumber' => $serialNumber->getNumber(),
                        'oldConfiguration' => $serialNumber->getConfiguration(),
                        'newConfiguration' => $configuration
                    ]
                );

                $serialNumber->setConfiguration($configuration);

                $this->userContextRepository->update($userContext);
            }
        }
    }
    

    /**
     * @param array $productsOrPackages
     *
     * @return int
     */
    private function getCertLevelIdFromFirstProductOrPackage(array $productsOrPackages)
    {
        if (!empty($productsOrPackages[0]->getCertificationLevel())) {
            return $productsOrPackages[0]->getCertificationLevel()->getId();
        } else {
            return;
        }
    }
}
