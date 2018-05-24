<?php

use Fisdap\Data\Instructor\InstructorLegacyRepository;
use Fisdap\Data\ScheduleEmail\ScheduleEmailRepository;
use Fisdap\Entity\ScheduleEmail;
use Fisdap\Entity\SchedulerFilterSet;
use Fisdap\Members\Scheduler\Http\LimitFilters;
use Fisdap\Members\Scheduler\SchedulerHelper;


class Scheduler_PdfExportController extends Fisdap_Controller_Private
{
    use LimitFilters;


    /**
     * @var Zend_Session_Namespace
     */
    private $session;


    public function init()
    {
        parent::init();

        $this->session = new \Zend_Session_Namespace("Scheduler");
    }


    public function generateAction()
    {
        $email = $this->_getParam('email_id');
        $form = new Scheduler_Form_PdfExportModal($email);
        $this->_helper->json($form->__toString());
    }


    public function processAction(
        InstructorLegacyRepository $instructorLegacyRepository,
        ScheduleEmailRepository $scheduleEmailRepository
    ) {
        $view_type = $this->_getParam('type');

        // first process the modal form to make sure we didn't have any validation errors
        $modalFormValues = $this->_getParam('modal-form');
        $modalFormValues['view_type'] = $view_type;
        $modalFormValues['bw_switch'] = $modalFormValues['pdf_color_type'] == 'color' ? 0 : 1;
        $modalFormValues['pdf'] = true;
        $modalFormValues['email_recipients'] = implode(
            ',', Util_Array::getCleanArray($modalFormValues['email_recipients'])
        );

        $email_id = $modalFormValues['email_id'];
        $form = new Scheduler_Form_PdfExportModal($email_id);
        $valid = $form->process($modalFormValues);

        // if we didn't pass validation, bail
        if ($valid !== true) {
            $this->_helper->json($valid);
        }

        // make sure the filters are limited for students with different permissions
        $filters = $this->getParam("filters");
        $filters = $this->limitFilters($filters);

        // figure out what kind of export this is
        $export_type = $modalFormValues['pdf_export_type'];

        switch ($export_type) {
            case "recurring":

                $isInstructor = $this->user->isInstructor();
                $isStaff = $this->user->isStaff();

                /*
                 * Handle the case of the form being submitted by a staff member who explicitly provided an
                 * instructor id. This makes sure that staff members create the email/subscription as a different
                 * instructor than themselves.
                 */
                if ($isInstructor && $isStaff && $modalFormValues['scheduler_export_instructor']) {
                    $instructorRoleData = $instructorLegacyRepository->getOneById(
                        $modalFormValues['scheduler_export_instructor']
                    );
                } else {
                    $instructorRoleData = $this->user->getCurrentRoleData();
                }

                if ($email_id) { // existing schedule email
                    $scheduleEmail = $scheduleEmailRepository->getOneById($email_id);

                    // only set instructor on updates, if user is staff
                    if ($isInstructor && $isStaff) {
                        $scheduleEmail->instructor = $instructorRoleData;
                    }
                } else { // new schedule email
                    $scheduleEmail = new ScheduleEmail();
                    $scheduleEmail->instructor = $instructorRoleData;
                }

                $scheduleEmail->title = $modalFormValues['name'];
                $scheduleEmail->program = $this->user->getCurrentProgram();
                $scheduleEmail->email_list = $modalFormValues['email_recipients'];
                $scheduleEmail->recurring_type = $modalFormValues['pdf_recurring_type'];
                $scheduleEmail->offset_number = $modalFormValues['email_frequency_offset'];
                $scheduleEmail->offset_type = substr($modalFormValues['pdf_email_frequency_offset_type'], 5);
                $scheduleEmail->email_subject = $modalFormValues['email_subject'];
                $scheduleEmail->email_note = $modalFormValues['email_note'];
                $scheduleEmail->orientation = $modalFormValues['pdf_orientation_type'];
                $scheduleEmail->legend = $modalFormValues['legend_switch'];
                $scheduleEmail->color = $modalFormValues['pdf_color_type'] == 'color' ? 1 : 0;

                // if this is a new recurring email, create the filter set
                if ( ! $email_id) {
                    $filterSet = new SchedulerFilterSet();
                    $filterSet->user_context = null; // so we don't override the user's session filters
                    $filterSet->setViewTypeByName($view_type);
                    $filterSet->filters = $filters;

                    // get display options from the filter set in the current session
                    /** @var SchedulerFilterSet $currentFilterSet */
                    $currentFilterSet = \Fisdap\EntityUtils::getEntity("SchedulerFilterSet", $this->session->filterSet);

                    $filterSet->show_student_names    = $currentFilterSet->show_student_names;
                    $filterSet->show_instructor_names = $currentFilterSet->show_instructor_names;
                    $filterSet->show_preceptor_names  = $currentFilterSet->show_preceptor_names;
                    $filterSet->show_weebles          = $currentFilterSet->show_weebles;
                    $filterSet->show_totals           = $currentFilterSet->show_totals;
                    $filterSet->show_site_names       = $currentFilterSet->show_site_names;
                    $filterSet->show_base_names       = $currentFilterSet->show_base_names;

                    $scheduleEmail->filter = $filterSet;
                }

                $scheduleEmailRepository->store($scheduleEmail);

                $this->_helper->json(true);
                break;

            case "email":
            case "pdf":
            default:
                $pdfHelper = new SchedulerHelper();
                $this->view->pdf = true;
                $html = $pdfHelper->getScheduleHtml(
                    $view_type, $modalFormValues, $filters, $this->view, $this->user->getCurrentProgram()->id
                );

                // flip it back to false, so we don't screw up the html view
                $this->view->pdf = false;

                $this->_helper->json($html);
                break;
        }
    }
}