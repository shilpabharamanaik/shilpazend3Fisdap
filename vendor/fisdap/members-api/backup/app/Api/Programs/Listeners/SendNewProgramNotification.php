<?php namespace Fisdap\Api\Programs\Listeners;

use Fisdap\Api\Programs\Events\DemoStudentWasCreated;
use Fisdap\Data\Program\ProgramLegacyRepository;
use Fisdap\Logging\Events\EventLogging;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Message;


/**
 * Class SendNewProgramNotification
 *
 * @package Fisdap\Api\Programs\Listeners\Pipes
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class SendNewProgramNotification
{
    use EventLogging;
    
    
    /**
     * @var ProgramLegacyRepository
     */
    private $programLegacyRepository;

    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var Mailer|\Illuminate\Mail\Mailer
     */
    private $mailer;


    /**
     * SendNewProgramNotification constructor.
     *
     * @param ProgramLegacyRepository $programLegacyRepository
     * @param Config                  $config
     * @param Mailer                  $mailer
     */
    public function __construct(ProgramLegacyRepository $programLegacyRepository, Config $config, Mailer $mailer)
    {
        $this->programLegacyRepository = $programLegacyRepository;
        $this->config = $config;
        $this->mailer = $mailer;
    }


    /**
     * @param DemoStudentWasCreated $event
     */
    public function handle(DemoStudentWasCreated $event)
    {
        $this->mailer->send(
            'emails.new_program',
            [
                'program' => $this->programLegacyRepository->getOneById($event->getProgramId()),
                'demoUsername' => $event->getUsername(),
                'demoPassword' => $event->getPassword()
            ],
            function ($message) {

                /** @var Message $message */
                $message->subject("A new program has been created in Fisdap");
                $message->to($this->config->get('mail.support.address'), $this->config->get('mail.support.name'));
            }
        );
    }
}