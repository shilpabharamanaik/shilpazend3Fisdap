<?php namespace Fisdap\Members\Scheduler;

use DateTime;
use Fisdap\Entity\SchedulerFilterSet;
use Scheduler_View_Helper_CalendarView;
use Util_Array;
use Zend_Mime;

/**
 * Class SchedulerHelper
 *
 * @package Fisdap\Members\Scheduler
 */
class SchedulerHelper
{
    /**
     * Returns the html body for a given schedule
     *
     * @param      $view_type
     * @param      $options
     * @param      $filters
     * @param      $view
     * @param      $program_id
     * @param null $filter_set
     *
     * @return string
     */
    public function getScheduleHtml($view_type, $options, $filters, $view, $program_id, $filter_set = null)
    {
        // we'll need some helpers
        $calViewHelper = new Scheduler_View_Helper_CalendarView();

        // make the end date the end of the correct period (week, month, etc.)
        $start_date = new DateTime($options["start_date"]);
        $end = new DateTime($options["end_date"]);
        $start_last = $calViewHelper->getStartDate($end, $view_type);
        $end_date = ($view_type == "list") ? $end : $calViewHelper->getEndDate($start_last, $view_type);

        // in order to repeat the views (multiple days, weeks, or months), we need to chunk it out
        // and get the html in batches
        $sections = $this->getSectionDates($view_type, $start_date, $end_date);

        // configure the page header
        $pageHeader = $this->getPageHeader($options['name'], $filters, $program_id);

        // get the legend, if necessary
        $legend_switch = $options["legend_switch"];
        if ($legend_switch == 1) {
            $legend_html = $view->partial(
                "scheduler-legend.phtml",
                array("isStudent" => $this->view->isStudent,
                                                "pdf"       => $view->pdf)
            );
        }

        // get the html
        $html = "<div class='container_12' id='pdf-view'>";

        foreach ($sections as $section) {
            // put a page break between each chunk
            $html .= "<div>";
            $html .= $pageHeader;
            $html .= "<h2 class='page-sub-title'>" . $this->getSectionHeader(
                    $view_type,
                $section['start_date'],
                $section['end_date']
                ) . "</h2>";
            $html .= $calViewHelper->getCalendarHtml(
                $view_type,
                $section['start_date'],
                $section['end_date'],
                $filters,
                $view,
                $filter_set
            );
            $html .= "<div class='clear'></div>";

            // add a legend, if necessary
            if ($legend_switch == 1) {
                $html .= $legend_html;
            }

            $html .= "</div>";
            $html .= "<div style='page-break-after:always'></div>";
        }

        $html .= "</div>";

        if ($options["bw_switch"]) {
            $html = $this->formatInBlackAndWhite($html);
        }
        if ($options["pdf"]) {
            $html = $this->formatForPdf($html);
        }

        return $html;
    }


    /**
     * adds some file to the view header
     *
     * @param $view
     */
    public function addExtraFiles($view)
    {
        $view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/modal-imports.css");
        $view->headScript()->appendFile("/js/library/Scheduler/View/Helper/student-presets.js");
        $view->headScript()->appendFile("/js/library/Scheduler/View/Helper/shift-assign-multistudent-picklist.js");
        $view->headScript()->appendFile("/js/library/Scheduler/View/Helper/multipick-cal.js");
        $view->headScript()->appendFile("/js/jquery.busyRobot.js");
        $view->headLink()->appendStylesheet("/css/jquery.busyRobot.css");
        $view->headScript()->appendFile("/js/library/Fisdap/Utils/create-pdf.js");
    }


    /**
     * adds some file to the view header
     *
     * @param $view
     */
    public function addPdfStyles($view)
    {
        $view->headLink()->appendStylesheet("/css/global.css");
        $view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/calendar-view.css");
        $view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/calendar-controls.css");
        $view->headLink()->appendStylesheet("/css/scheduler/index/index.css");
        $view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/calendar-view-pdf.css");
    }


    /**
     * get header for pdf
     *
     * @param           $view
     * @param bool|true $color
     *
     * @return string
     */
    public function getHeader($view, $color = true)
    {
        $header = "<head>\n";
        $header .= "<script type='text/javascript'>\n";
        $header .= "var NREUMQ=NREUMQ||[];NREUMQ.push(['mark','firstbyte',new Date().getTime()]);\n";
        $header .= "</script>\n";
        $header .= $view->headLink() . "\n";

        if (!$color) {
            $header .= "<link type='text/css' rel='stylesheet' media='screen' href='/css/library/Scheduler/View/Helper/calendar-view-greyscale.css'>\n";
        }

        $header .= "</head>\n";

        return $header;
    }


    /**
     * E-mails the pdf
     *
     * @param $pdf
     * @param $user
     * @param $options
     *
     * @throws \Zend_Mail_Exception
     */
    public function emailPdf($pdf, $user, $options)
    {
        // get info for the email
        if ($options['contentEncoded']) {
            $decode = true;
        } else {
            $decode = false;
        }

        $subject = ($decode) ? urldecode($options['subject']) : $options['subject'];
        if ($subject == "") {
            $subject = "Shift Schedule from " . $user->getCurrentProgram()->name;
        }

        $recipient_str = ($decode) ? urldecode($options['recipients']) : $options['recipients'];
        $recipients = explode(",", $recipient_str);
        $note = ($decode) ? urldecode($options['note']) : $options['note'];
        $template = ($decode) ? urldecode($options['template']) : $options['template'];
        $pdfName = ($decode) ? urldecode($options['pdfName']) : $options['pdfName'];

        // send the mail
        $mail = new \Fisdap_TemplateMailer();
        $mail->setSubject($subject)
            ->setViewParam("program", $user->getCurrentProgram()->name)
            ->setViewParam("sender", $user->getName())
            ->setViewParam("senderEmail", $user->email)
            ->setViewParam("note", $note);

        foreach ($recipients as $recipient) {
            $mail->addTo(trim($recipient));
        }

        if ($pdf) {
            $attachment = $mail->createAttachment($pdf);
            $attachment->type = 'application/pdf';
            $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
            $attachment->filename = $pdfName;
        } else {
            $template = substr($template, 0, -6) . "-empty.phtml";
        }

        $mail->sendHtmlTemplate($template);
    }


    /**
     * Return an html string of the page header, describing the filters used to generate the pdf
     *
     * @param $pdf_name
     * @param $filters
     * @param $program_id
     *
     * @return string
     */
    private function getPageHeader($pdf_name, $filters, $program_id)
    {
        $filterInfo = SchedulerFilterSet::getInfoFromFilters($filters, $program_id);

        $header
            = "<div class='pdf-page-header'>
						<h2 class='section-header'>$pdf_name</h2>
						<h4 class='section-header no-border'>
							<span class='label'>Location:</span> " .
            $this->singleOrDescription($filterInfo['sites'], $filterInfo['site_category']) . ", " .
            $this->singleOrDescription($filterInfo['bases'], $filterInfo['base_category']) . ", " .
            $this->singleOrDescription($filterInfo['preceptors'], $filterInfo['preceptor_category']) .
            "</h4>";

        if ($filterInfo['available']) {
            $certs = $filterInfo['all_available_certs']
                ? $filterInfo['available_certs']
                : implode(
                    "/",
                    $filterInfo['available_certs']
                );
            $header
                .= "<h4 class='section-header no-border'>
							<span class='label'>Available to:</span> $certs, " .
                $this->singleOrDescription($filterInfo['available_groups'], $filterInfo['available_groups_category']);
            if ($filterInfo['hide_invisible']) {
                $header .= " (hide invisible shifts)";
            }
            $header .= "</h4>";
        }

        if ($filterInfo['chosen']) {
            $certs = $filterInfo['all_chosen_certs']
                ? $filterInfo['chosen_certs']
                : implode(
                    "/",
                    $filterInfo['chosen_certs']
                );
            $header
                .= "<h4 class='section-header no-border'>
							<span class='label'>Chosen by/Assigned to:</span> $certs, " .
                $this->singleOrDescription($filterInfo['chosen_groups'], $filterInfo['chosen_groups_category']) . ", " .
                lcfirst($filterInfo['chosen_grad_date']) . ", " .
                $this->allOrDescription($filterInfo['all_chosen_students'], $filterInfo['chosen_students'], 'student');
            $header .= "</h4>";
        }

        $header .= "</div>";

        return $header;
    }


    /**
     * @param $info_array
     * @param $description
     *
     * @return mixed|string
     */
    private function singleOrDescription($info_array, $description)
    {
        $allDescriptions = array('Active preceptors from selected sites');
        if (in_array($description, $allDescriptions)) {
            return lcfirst($description);
        }

        // make sure the array is flat first
        $flat_array = Util_Array::flatten($info_array);

        return (count($flat_array) == 1) ? array_shift($flat_array) : lcfirst($description);
    }


    /**
     * @param $all
     * @param $description
     * @param $type
     *
     * @return string
     */
    private function allOrDescription($all, $description, $type)
    {
        if ($all) {
            return lcfirst($description);
        }

        return count($description) . " selected " . \Util_String::pluralize($type, count($description));
    }


    /**
     * return an array of start and end dates sectioned out by day/week/month for a given overall date range
     *
     * @param $type
     * @param $start_date
     * @param $end_date
     *
     * @return array
     */
    private function getSectionDates($type, $start_date, $end_date)
    {
        $sections = array();

        if ($type == "month-details") {
            $type = "month";
        }

        if ($type == 'list') {
            $sections[] = array('start_date' => $start_date, 'end_date' => $end_date);
        } else {
            $sections[] = array('start_date' => new DateTime($start_date->format('Y-m-d')), 'end_date' => null);
            $start_date->modify('+1 ' . $type);
            while ($start_date <= $end_date) {
                $sections[] = array('start_date' => new DateTime($start_date->format('Y-m-d')), 'end_date' => null);
                $start_date->modify('+1 ' . $type);
            }
        }

        return $sections;
    }


    /**
     * Return an array of start and end dates sectioned out by day/week/month for a given overall date range
     *
     * @param $type
     * @param $start_date
     * @param $end_date
     *
     * @return string
     */
    private function getSectionHeader($type, $start_date, $end_date)
    {
        switch ($type) {
            case "month":
                $header = $start_date->format('F Y');
                break;
            case "month-details":
                $header = $start_date->format('F Y');
                break;
            case "day":
                $header = $start_date->format('M j, Y');
                break;
            case "week":
            case "list":
            default:
                if (!$end_date) {
                    $calViewHelper = new Scheduler_View_Helper_CalendarView();
                    $end_date = $calViewHelper->getEndDate($start_date, $type);
                }
                $header = $start_date->format('M j, Y') . " - " . $end_date->format('M j, Y');
                break;
        }

        return $header;
    }


    /**
     * @param $html
     *
     * @return mixed
     */
    private function formatInBlackAndWhite(&$html)
    {
        // use black and white images
        return str_replace(
            [
                "SiteIconColor",
                "product-icons/product-icon-1.svg",
                "link-to-skills-tracker",
                "<td class=\"shift-action-cell\">",
                "denied.png",
                "approved.png",
                "student-weeble-red.svg"
            ],
            [
                "SiteIcon",
                "SkillsTracker_tiny_gray.png",
                "",
                "<td class='shift-action-cell'><img src='/images/SkillsTracker_tiny_gray.png' class='gray-shield'>",
                "denied_gray.png",
                "approved_gray.png",
                "student-weeble-pdf.png"
            ],
            $html
        );
    }


    /**
     * @param $html
     *
     * @return mixed
     */
    private function formatForPdf(&$html)
    {
        // replace svg weebles with png ones, since pdf doesn't render svgs neatly
        return str_replace(
            [
                'student-weeble.svg',
                'student-weeble-invisible.svg',
                'student-weeble-outline.svg',
                'student-weeble-red.svg'
            ],
            [
                'student-weeble-pdf.png',
                'student-weeble-invisible-pdf.png',
                'student-weeble-outline-pdf.png',
                'student-weeble-red-pdf.png'
            ],
            $html
        );
    }
}
