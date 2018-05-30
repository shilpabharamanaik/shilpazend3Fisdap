<?php namespace Fisdap\Api\Users\UserContexts\Listeners;

use Fisdap\Api\Users\UserContexts\Events\UserContextWasCreated;
use Fisdap\Data\Instructor\InstructorLegacyRepository;
use Fisdap\Data\User\UserContext\UserContextRepository;
use Fisdap\Entity\InstructorLegacy;
use Fisdap\Entity\StudentLegacy;
use Fisdap\Entity\UserContext;
use Fisdap\Logging\Events\EventLogging;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Message;

/**
 * A queued event listener for sending instructors e-mail notifications of a new student,
 * when their user context (UserContext Entity) was created
 *
 * @package Fisdap\Api\Users\UserContexts\Listeners
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class SendInstructorNewStudentInfo implements ShouldQueue
{
    use EventLogging;


    /**
     * @var UserContextRepository
     */
    private $userContextRepository;

    /**
     * @var InstructorLegacyRepository
     */
    private $instructorLegacyRepository;

    /**
     * @var Mailer|\Illuminate\Mail\Mailer
     */
    private $mailer;


    /**
     * SendInstructorNewStudentInfo constructor.
     *
     * @param UserContextRepository      $userContextRepository
     * @param InstructorLegacyRepository $instructorLegacyRepository
     * @param Mailer                     $mailer
     */
    public function __construct(
        UserContextRepository $userContextRepository,
        InstructorLegacyRepository $instructorLegacyRepository,
        Mailer $mailer
    ) {
        $this->userContextRepository = $userContextRepository;
        $this->instructorLegacyRepository = $instructorLegacyRepository;
        $this->mailer = $mailer;
    }


    /**
     * @param UserContextWasCreated $event
     */
    public function handle(UserContextWasCreated $event)
    {
        if ($event->getRoleName() !== 'student') {
            return;
        }

        /** @var UserContext $userContext */
        $userContext = $this->userContextRepository->getOneById($event->getId());

        $studentId = $userContext->getRoleData()->getId();
        $gradDate = $userContext->getEndDate()->format('F Y');
        $firstName = $userContext->getUser()->getFirstName();
        $lastName = $userContext->getUser()->getLastName();
        $homePhone = $userContext->getUser()->getHomePhone();
        $email = $userContext->getUser()->getEmail();

        $subject = 'You have a new student!';

        /** @noinspection PhpParamsInspection */
        $instructors = $this->getInstructors($event->getProgramId(), $userContext->getRoleData());

        // e-mail instructors
        foreach ($instructors as $instructor) {
            $userContextEmail = $userContext->getEmail();
            $instructorEmail = isset($userContextEmail) ? $userContextEmail : $instructor->getUserContext()->getUser()->getEmail();
            $instructorFullName = $instructor->getUserContext()->getUser()->getFullName();

            $this->mailer->queue(
                'emails.new_student',
                compact('studentId', 'gradDate', 'firstName', 'lastName', 'homePhone', 'email'),
                function ($message) use ($subject, $instructorEmail, $instructorFullName) {

                    /** @var Message $message */
                    $message->subject($subject);
                    $message->to($instructorEmail, $instructorFullName);
                }
            );
        }
    }


    /**
     * Get all instructors that should know about this student.
     *
     * Find all the students groups this student is a members of and check if those groups limit emails to only
     * instructors in that group. If there are no limitations, get all instructors in the program.
     *
     * @param int           $programId
     * @param StudentLegacy $student
     *
     * @return InstructorLegacy[]
     */
    private function getInstructors($programId, StudentLegacy $student)
    {
        $instructors = [];
        $includeAllInstructors = true;

        // Get any instructors for this students class sections who have the appropriate flags set...
        foreach ($student->classSectionStudent as $studentClassSection) {
            if ($studentClassSection->section->generate_emails) {
                $includeAllInstructors = false;

                foreach ($studentClassSection->section->section_instructor_associations as $sectionInstructor) {
                    if ($sectionInstructor->instructor->email_event_flag) {
                        $instructors[] = $sectionInstructor->instructor;
                    }
                }
            }
        }

        // If the student belongs to no class sections that limit emails, get all instructors
        if ($includeAllInstructors === true) {
            return $this->instructorLegacyRepository->findBy(['program' => $programId, 'email_event_flag' => true]);
        }

        return $instructors;
    }
}
