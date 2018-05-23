<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;
use Fisdap\MoodleUtils;

/**
 * Entity class for the legacy ScheduledTests table.
 *
 * @Entity(repositoryClass="Fisdap\Data\ScheduledTest\DoctrineScheduledTestsLegacyRepository")
 * @HasLifecycleCallbacks
 * @Table(name="ScheduledTests")
 */
class ScheduledTestsLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="ScheduledTest_id", type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="MoodleTestDataLegacy")
     * @JoinColumn(name="Test_id", referencedColumnName="MoodleQuiz_id")
     */
    protected $test;

    /**
     * @Column(name="StartDate", type="date")
     */
    protected $start_date;

    /**
     * @Column(name="EndDate", type="date")
     */
    protected $end_date;

    /**
     * @Column(name="ContactName", type="string")
     */
    protected $contact_name;
    /**
     * @Column(name="ContactPhone", type="string")
     */
    protected $contact_phone;
    /**
     * @Column(name="ContactEmail", type="string")
     */
    protected $contact_email;

    /**
     * @Column(name="TestNotes", type="string")
     */
    protected $test_notes;

    /**
     * @Column(name="ScheduledStudents", type="string")
     * IMPORTANT! This column is legacy only. Superceded by doctrine relationship on $students
     * REPEAT: this column is DEPRECATED
     */
    protected $scheduled_students;

    /**
     * @ManyToMany(targetEntity="StudentLegacy")
     * @JoinTable(name="fisdap2_test_scheduled_students",
     *  joinColumns={@JoinColumn(name="test_scheduled_id", referencedColumnName="ScheduledTest_id")},
     *  inverseJoinColumns={@JoinColumn(name="student_id",referencedColumnName="Student_id")})
     */
    protected $students;

    /**
     * @Column(name="Program_id", type="integer")
     */
    //protected $program_id;

    /**
     * @Column(name="Program_id", type="integer")
     */
    protected $programId;

    /**
     * @Column(name="ClassYear", type="integer")
     */
    protected $class_year = -1;

    /**
     * @Column(name="ClassMonth", type="string")
     */
    protected $class_month = -1;

    /**
     * @Column(name="ClassSectionYear", type="integer")
     */
    protected $class_section_year = -1;

    /**
     * @Column(name="ClassSection", type="string")
     */
    protected $class_section = -1;

    /**
     * @Column(name="CertLevel", type="string")
     */
    protected $cert_level = '';

    /**
     * @Column(name="Active", type="integer")
     */
    protected $active = 1;

    /**
     * @Column(name="PublishScores", type="integer")
     */
    protected $publish_scores = 0;

    /**
     * @Column(name="AgreedToPilot", type="integer")
     */
    protected $agreed_to_pilot = -1;

    /**
     * @Column(name="PilotAgreedBy", type="string")
     */
    protected $pilot_agreed_by = '';

    /**
     * @Column(name="PilotAgreedOn", type="datetime")
     */
    protected $pilot_agreed_on;


    // this is a stub function for returning an array of student IDs from the legacy column scheduled_students
    // instead you should use the relationship on $this->students
    public function get_scheduled_students()
    {
        $studentIds = unserialize($this->scheduled_students);

        return $studentIds;
    }

    // USE THIS FUNCTION instead of above
    public function get_students_array($fields = array('first_name', 'last_name', 'id'))
    {
        $students = array();
        foreach ($this->students as $student) {
            $students[$student->id] = array();
            foreach ($fields as $fieldname) {
                if ($student->{$fieldname}) {
                    $students[$student->id][$fieldname] = $student->{$fieldname};
                }
            }
        }

        return $students;
    }


    // Get the passwords assigned for the dates of this quiz
    public function get_passwords()
    {
        $moodleQuizId = $this->test->moodle_quiz_id;
        if ($moodleQuizId) {
            $em = EntityUtils::getEntityManager();
            $qb = $em->createQueryBuilder();

            $qb->select('tp.password, tp.date')
               ->from('\Fisdap\Entity\TestPasswordDataLegacy', 'tp')
               ->innerJoin('tp.test', 'test')
               ->where('test.moodle_quiz_id = ?1')
               ->andWhere('tp.date >= ?2')
               ->setParameter(1, $moodleQuizId)
               ->setParameter(2, $this->start_date->format('Y-m-d'));

            if ($this->end_date->format('Y-m-d') != '0000-00-00') {
                $qb->andWhere('tp.date <= ?3')->setParameter(3, $this->end_date->format('Y-m-d'));
            }

            $r = $qb->getQuery()->getResult();

            $passwords = array();
            foreach ($r as $key => $result) {
                if (!array_key_exists($result['date']->format("Y-m-d"), $passwords)) {
                    $passwords[$result['date']->format("Y-m-d")] = $result['password'];
                }
            }
            return $passwords;
        } else {
            return array();
        }
    }


    /*
     * Lifecycle Callbacks
     */

    /**
     * @PrePersist
     */
    public function checkPasswordsOnPrePersist()
    {
        /*
         * So the TestPasswordData table is weird: not directly tied to a ScheduledTest, because passwords are one-per-day-per-moodle-quiz, not one-per-scheduled-test-per-day
         * So we need to make sure passwords have been created, if not already existing.
         */

        // how many passwords do we need?
        $interval = $this->start_date->diff($this->end_date);

        // check for existing passwords on the date range
        $passwords = $this->get_passwords();

        // create a working object for the date that we can increment
        $date = clone $this->start_date;
        while ($date <= $this->end_date) {
            if (!isset($passwords[$date->format('Y-m-d')])) {
                // password for this date does not yet exist!
                // let's create it. first select a password base from the database (status quo logic - I know it's weak)
                $randId = rand(1, 2450);
                $fetchedPassword = EntityUtils::getEntity('TestPasswordTableLegacy', $randId);

                // create password doctrine entity
                $newPassword = new TestPasswordDataLegacy();
                $newPassword->test = $this->test;
                $newPassword->date = clone $date;
                $newPassword->password = $fetchedPassword->password;
                $newPassword->save(false);
            }

            // increment the working date
            $date->modify('+1 day');
        }
    }

    /*
     * Get information about the number of attempts remaining/used for students currently scheduled for this test
     */
    public function getStudentAttemptInfo()
    {
        $users = array();
        foreach ($this->students as $student) {
            $users[] = $student->user;
        }

        $attemptInfo =  MoodleUtils::getUsersQuizAttemptLimitInfo($users, $this->test);

        return $attemptInfo;
    }


    /**
     * Send an email to the contact person on this test, confirming the test and its information
     */
    public function sendInstructorNotification()
    {
        // compile email basics
        $subject = 'Fisdap Test Administration Confirmed';

        // compile passwords text
        $passwords = $this->get_passwords();

        // Compile test name / date text
        $testName = $this->test->test_name;
        $testDate = $this->start_date->format('m/d/Y');
        $testDate .= ($this->end_date && $this->end_date->format('m/d/Y') != $testDate) ? ' - ' . $this->end_date->format('m/d/Y') : '';

        // Send templated e-mail message
        $mail = new \Fisdap_TemplateMailer();
        $mail->addTo($this->contact_email)
             ->setSubject($subject)
             ->setViewParam('passwords', $passwords)
             ->setViewParam('testName', $testName)
             ->setViewParam('testDate', $testDate)
             ->setViewParam('numStudents', count($this->students))
             ->setViewParam('detailsUrl', \Util_HandyServerUtils::getCurrentServerRoot() . 'learning-center/test/details/stid/' . $this->id)
             ->sendHtmlTemplate('scheduled-test-notification.phtml');
    }
}
