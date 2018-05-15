<?php namespace Fisdap\Api\Users\Jobs;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Users\Events\PasswordResetWasCreated;
use Fisdap\Data\Repository\DoctrineRepository;
use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\PasswordReset;
use Fisdap\Entity\User;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Swagger\Annotations as SWG;

/**
 * A Job (Command) for creating a new password reset (PasswordReset Entity)
 *
 * Object properties will be hydrated automatically using JSON from an HTTP request body
 *
 * @package Fisdap\Api\Users\Jobs
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(
 *     definition="PasswordReset",
 *     required={"userId"}
 * )
 */
final class CreatePasswordReset extends Job implements RequestHydrated
{
    /**
     * @var integer
     * @see User
     * @SWG\Property
     */
    protected $userId;
    
    /**
     * Simple helper to use URL $userId
     *
     * @param $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $em
     * @param EventDispatcher $eventDispatcher
     * @param BusDispatcher $busDispatcher
     * @param SendPasswordResetEmail $sendPasswordResetEmail
     *
     * @return PasswordReset
     */
    public function handle(
        UserRepository $userRepository,
        EntityManagerInterface $em,
        EventDispatcher $eventDispatcher,
        BusDispatcher $busDispatcher,
        SendPasswordResetEmail $sendPasswordResetEmail
    ) {
        $passwordReset = new PasswordReset();

        /** @var User $user */
        $user = $userRepository->find($this->userId);

        if (empty($user)) {
            throw new ResourceNotFound("No user found with id '$this->userId'.'");
        }

        $passwordReset->setUser($user);
        $passwordReset->setCode($passwordReset->generateCode());

        /** @var DoctrineRepository $pwResetRepo */
        $pwResetRepo = $em->getRepository(PasswordReset::class);
        $pwResetRepo->store($passwordReset);

        $eventDispatcher->fire(new PasswordResetWasCreated($passwordReset));

        $this->sendEmail($passwordReset, $busDispatcher, $sendPasswordResetEmail);
    }

    /**
     * @param PasswordReset $passwordReset
     * @param BusDispatcher $busDispatcher
     * @param SendPasswordResetEmail $sendPasswordResetEmail
     *
     * @return mixed
     */
    private function sendEmail(
        PasswordReset $passwordReset,
        BusDispatcher $busDispatcher,
        SendPasswordResetEmail $sendPasswordResetEmail
    ) {
        $sendPasswordResetEmail->setPasswordReset($passwordReset);

        $sendEmail = $busDispatcher->dispatch($sendPasswordResetEmail);

        return $sendEmail;
    }
}
