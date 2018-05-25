<?php namespace Fisdap\Members\Scheduler\Console;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Fisdap\Data\ScheduleEmail\ScheduleEmailRepository;
use Fisdap\Entity\ScheduleEmail;
use Fisdap\Entity\User;
use Fisdap\Members\Logging\MailLogger;
use Fisdap\Members\Scheduler\Jobs\SendScheduleEmail;
use Fisdap_TemplateMailer;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Psr\Log\LoggerInterface;


/**
 * Class SendScheduleEmailsCommand
 *
 * @package Fisdap\Members\Scheduler\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class SendScheduleEmailsCommand extends Command
{
    const SUPPORT_EMAIL = 'support@fisdap.net';


    protected $signature = 'scheduler:send-schedule-emails
                            {date? : Send for a particular date (YYYY-MM-DD) instead of today}
                            {--x|deactivate-if-invalid : Deactivate schedule e-mails with invalid users. Ignored with -s option.}
                            {--s|send-now-id= : ID of a ScheduleEmail entity to send now, instead of all active e-mails }';

    protected $description = 'Send schedule e-mails';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ScheduleEmailRepository
     */
    private $scheduleEmailRepository;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    /**
     * @var Fisdap_TemplateMailer
     */
    private $mailer;


    /**
     * @param MailLogger              $logger
     * @param ScheduleEmailRepository $scheduleEmailRepository
     * @param EntityManager           $entityManager
     * @param Dispatcher              $dispatcher
     * @param Fisdap_TemplateMailer   $mailer
     */
    public function __construct(
        MailLogger $logger,
        ScheduleEmailRepository $scheduleEmailRepository,
        EntityManager $entityManager,
        Dispatcher $dispatcher,
        Fisdap_TemplateMailer $mailer
    ) {
        parent::__construct();

        $this->logger = $logger;
        $this->scheduleEmailRepository = $scheduleEmailRepository;
        $this->em = $entityManager;
        $this->dispatcher = $dispatcher;
        $this->mailer = $mailer;
    }


    public function fire()
    {
        $date = $this->argument('date');

        if (isset($date)) {
            $date = new \DateTime($date);
            $this->logger->info('Pretending today is ' . $date->format('Y-m-d'));
        } else {
            $date = new \DateTime;
        }

        if ( ! is_null($this->option('send-now-id'))) {
            $job = (new SendScheduleEmail((int) $this->option('send-now-id'), $date));
            $this->dispatcher->dispatch($job);
        } else {
            $this->sendCollection($date);
        }
    }


    private function sendCollection($date)
    {
        $scheduleEmails = $this->scheduleEmailRepository->getActiveScheduleEmails();

        $count = 0;
        $queuedCount = 0;

        foreach ($scheduleEmails as $row) {
            $count++;
            $this->logger->notice("Evaluating schedule e-mail config #$count...");

            /** @var ScheduleEmail $scheduleEmail */
            $scheduleEmail = $row[0];

            /** @var User $user */
            $user = $scheduleEmail->instructor->user; // use the view point of the person who made this pdf

            // if this user has an invalid program ID, skip this e-mail
            try {
                $user->getProgramId();
            } catch (EntityNotFoundException $e) {
                $this->logger->warning(
                    "User has an invalid program ID, skipping schedule e-mail ID '{$scheduleEmail->id}'"
                );
                $this->setInactive($scheduleEmail, 'User has an invalid program ID');
                continue;
            }

            // if this user has been deleted, skip this e-mail
            if ($user->deleted) {
                $this->logger->warning("User deleted, skipping schedule e-mail ID '{$scheduleEmail->id}'");
                $this->setInactive($scheduleEmail, 'User deleted');
                continue;
            }


            // check if e-mail should be sent today
            if ($scheduleEmail->sendToday($date) === false) {
                $this->em->detach($scheduleEmail);
                $this->logger->info(
                    "Skipping schedule e-mail ID '{$scheduleEmail->id}' because it should not be sent today"
                );
                continue;
            }

            // dispatch SendScheduleEmail job
            $job = (new SendScheduleEmail($scheduleEmail->id, $date));
            $this->dispatcher->dispatch($job);
            $queuedCount++;

            $this->em->detach($scheduleEmail);
        }

        $this->logger->notice("$queuedCount schedule e-mails have been queued");
    }


    /**
     * @param ScheduleEmail $scheduleEmail
     * @param string        $reason
     */
    private function setInactive(ScheduleEmail $scheduleEmail, $reason)
    {
        if ($this->option('deactivate-if-invalid')) {
            $this->logger->notice("Deactivating schedule e-mail ID '{$scheduleEmail->id}' due to previous warning...");
            $scheduleEmail->active = false;
            $this->em->flush();
            $this->em->detach($scheduleEmail);

            // notify support
            $this->notifySupport($scheduleEmail, $reason);
        }
    }


    /**
     * @param ScheduleEmail $disabledEmail
     * @param string        $reason
     *
     * @throws \Zend_Mail_Exception
     */
    private function notifySupport(ScheduleEmail $disabledEmail, $reason)
    {
        $this->mailer->setViewParams(
            [
                'program' => $disabledEmail->program->name,
                'scheduleEmailId' => $disabledEmail->id,
                'scheduleEmailTitle' => $disabledEmail->title,
                'instructorName' => "{$disabledEmail->instructor->user->first_name} {$disabledEmail->instructor->user->last_name}",
                'instructorUsername' => $disabledEmail->instructor->user->username,
                'reason' => $reason
            ]
        )->addTo(self::SUPPORT_EMAIL)
            ->setSubject(
                "Schedule e-mail '{$disabledEmail->title}' from '{$disabledEmail->program->name}' has been disabled"
            )->sendTextTemplate('email-schedule-disabled.phtml');
        $this->mailer->clearRecipients()->clearSubject();
    }
}