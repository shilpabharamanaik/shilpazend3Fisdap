<?php namespace Fisdap\Api\Users\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Support\FisdapUrls;
use Fisdap\Api\Users\Jobs\Models\PasswordResetEmail;
use Fisdap\Entity\PasswordReset;
use Illuminate\Mail\Mailer;
use Swagger\Annotations as SWG;

/**
 * A Job (Command) for sending a password reset email
 *
 * Object properties will be hydrated automatically using JSON from an HTTP request body
 *
 * @package Fisdap\Api\Users\Jobs
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 */
final class SendPasswordResetEmail extends Job implements RequestHydrated
{
    /**
     * @var PasswordReset
     */
    private $passwordReset;

    /**
     * @param PasswordReset $passwordReset
     */
    public function setPasswordReset(PasswordReset $passwordReset)
    {
        $this->passwordReset = $passwordReset;
    }

    /**
     * @param Mailer $mailer
     */
    public function handle(Mailer $mailer)
    {
        $to = $this->passwordReset->getUser()->getEmail();
        $title = $this->getDisplayTitle();

        $mailer->queue(
            'emails/password_reset',
            [
                'firstName'     => $this->passwordReset->getUser()->getFirstName(),
                'username'      => $this->passwordReset->getUser()->getUsername(),
                'code'          => $this->passwordReset->getCode(),
                'urlRoot'       => FisdapUrls::getMembersUrl()
            ],
            function ($m) use ($to, $title) {
                $m->to($to, $title);
                $m->from('support@fisdap.net', 'Fisdap')->subject('Password Reset');
            }
        );
    }

    /**
     * @return string
     */
    private function getDisplayTitle()
    {
        $organization = $this->passwordReset->getUser()->getFirstName() . ' ' .
                        $this->passwordReset->getUser()->getLastName();
        return $organization;
    }
}
