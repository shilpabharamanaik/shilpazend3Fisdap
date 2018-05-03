<?php namespace Fisdap\Members\Scheduler\Jobs;

use Fisdap\Data\ScheduleEmail\ScheduleEmailRepository;
use Fisdap\Entity\ScheduleEmail;
use Fisdap\Entity\User;
use Fisdap\Members\Logging\MailLogger;
use Fisdap\Members\Queue\JobHandlers\JobHandlerLoggingHelpers;
use Fisdap\Members\Scheduler\SchedulerHelper;
use Fisdap\Service\DataExport\PdfGenerator;
use Fisdap_Auth_Adapter_Db;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Zend_Layout;
use Zend_Registry;
use Zend_View_Interface;


/**
 * Class SendScheduleEmail
 *
 * @package Fisdap\Members\Scheduler\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class SendScheduleEmail implements SelfHandling, ShouldQueue
{
    use JobHandlerLoggingHelpers, Queueable, InteractsWithQueue;


    /**
     * @var int
     */
    private $scheduleEmailId;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var ScheduleEmailRepository
     */
    private $scheduleEmailRepository;

    /**
     * @var PdfGenerator
     */
    private $pdfGenerator;

    /**
     * @var Zend_View_Interface
     */
    private $view;


    /**
     * @param int       $scheduleEmailId
     * @param \DateTime $date
     */
    public function __construct($scheduleEmailId, \DateTime $date)
    {
        $this->scheduleEmailId = $scheduleEmailId;
        $this->date = $date;

        // set up the view
        $this->view = Zend_Layout::getMvcInstance()->getView();
        $this->view->setScriptPath(APPLICATION_PATH . "/modules/scheduler/views/scripts");
        $this->view->pdf = true;
    }


    /**
     * @param MailLogger              $logger
     * @param ScheduleEmailRepository $scheduleEmailRepository
     * @param PdfGenerator            $pdfGenerator
     */
    public function handle(MailLogger $logger, ScheduleEmailRepository $scheduleEmailRepository, PdfGenerator $pdfGenerator)
    {
        $this->logger = $logger;
        $this->scheduleEmailRepository = $scheduleEmailRepository;
        $this->pdfGenerator = $pdfGenerator;

        $schedulerHelper = new SchedulerHelper();

        $schedulerHelper->addExtraFiles($this->view);
        $schedulerHelper->addPdfStyles($this->view);

        $this->logStart($this->job);

        /** @var ScheduleEmail $scheduleEmail */
        $scheduleEmail = $this->scheduleEmailRepository->getOneById($this->scheduleEmailId);

        // first, make sure the filters are 'up to date'
        $scheduleEmail->updateDependentIDsFromFilters();

        /** @var User $user */
        $user = $scheduleEmail->instructor->user; // use the view point of the person who made this pdf

        $this->log(
            $this->job,
            "Preparing schedule e-mail ID '{$scheduleEmail->id}' for instructor ID '{$scheduleEmail->instructor->id}'..."
        );
        $view_type = $scheduleEmail->filter->view_type->name;

        // mask as the user associated with the e-mail, and register in Zend_Registry
        Fisdap_Auth_Adapter_Db::masquerade($user);
        Zend_Registry::set('LoggedInUser', $user);
        $this->log($this->job, "Masquerading as " . User::getLoggedInUser()->username);

        // get the html
        $this->log($this->job, "Getting HTML for schedule e-mail ID '{$scheduleEmail->id}'...");

        $headerHtml = $schedulerHelper->getHeader($this->view, $scheduleEmail->color);
        $header = $this->formatForPdf($headerHtml);

        $htmlOptions = [];
        $htmlOptions['name'] = $scheduleEmail->title;
        $htmlOptions['legend_switch'] = $scheduleEmail->legend;
        $htmlOptions['bw_switch'] = !($scheduleEmail->color);
        $htmlOptions['pdf'] = true;
        $htmlOptions['start_date'] = $scheduleEmail->getStartDate($this->date);
        $htmlOptions['end_date'] = $scheduleEmail->getEndDate($htmlOptions['start_date']);
        $scheduleHtml = $schedulerHelper->getScheduleHtml(
                $view_type,
                $htmlOptions,
                $scheduleEmail->filter->filters,
                $this->view,
                $user->getProgramId(),
                $scheduleEmail->filter
            ) .
            "</div>";
        $pdfContents = $this->formatForPdf($scheduleHtml);

        // generate the pdf
        $this->log($this->job, "Generating PDF for schedule e-mail ID '{$scheduleEmail->id}'...");

        $this->pdfGenerator->setFilename($scheduleEmail->title . ".pdf");
        $this->pdfGenerator->setOrientation($scheduleEmail->orientation);
        $this->pdfGenerator->generatePdfFromHtmlString($pdfContents, false, $header);
        $pdf = $this->pdfGenerator->getPdfContent();

        // send the pdf
        $this->log($this->job, "Sending PDF for schedule e-mail ID '{$scheduleEmail->id}'...");

        $emailOptions = [];
        $emailOptions['contentEncoded'] = false;
        $emailOptions['pdfName'] = $scheduleEmail->title . ".pdf";
        $emailOptions['subject'] = $scheduleEmail->email_subject;
        $emailOptions['recipients'] = $scheduleEmail->email_list;
        $emailOptions['template'] = 'auto-schedule.phtml';
        $emailOptions['note'] = $scheduleEmail->email_note;
        $schedulerHelper->emailPdf($pdf, $user, $emailOptions);

        // unmask and unregister the user associated with this e-mail
        Fisdap_Auth_Adapter_Db::unmask();
        Zend_Registry::set('LoggedInUser', null);

        $this->logSuccess($this->job);

        $this->delete();
    }


    /**
     * take the html and make it good for pdf
     *
     * @param $html
     *
     * @return string
     */
    private function formatForPdf(&$html)
    {
        $file_path = APPLICATION_PATH . "/../public";

        // get the absolute paths for the css and image files and turn links into spans
        return str_replace(
            [
                "/css/",
                "/images/",
                "<a ",
                "</a>"
            ],
            [
                $file_path . "/css/",
                $file_path . "/images/",
                "<span ",
                "</span>"
            ],
            $html
        );
    }
}