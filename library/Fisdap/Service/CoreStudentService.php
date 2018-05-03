<?php
/**
 * Created by PhpStorm.
 * User: jmortenson
 * Date: 6/18/14
 * Time: 6:25 PM
 */

namespace Fisdap\Service;


use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\User;

class CoreStudentService implements StudentService
{
    const ANONYMOUS_NAME_TEXT = "Anonymous";

    /**
     * Shuffle and anonymize an array of students
     *
     * @param User $user The user for whom we're creating a list
     * @param array $students array of either student IDs or student data arrays (must contain an 'id' key)
     * @return array Array keyed by student ID with anonymous names
     */
    public function shuffleAndAnonymizeStudents(User $user, array $students)
    {
        $shuffledStudents = array();

        // If displaying for a student, include that student's name at the top of the array
        if ($user->isInstructor()) {
            // we need to add student ID into the values of each array element
            // otherwise it gets lost when shuffle() is called
            foreach($students as $key => $studentId) {
                $students[$key] = array('id' => $studentId);
            }
        } else if (!$user->isInstructor()) {
            $this_student = $user->getCurrentRoleData();
            // put this student at the top of the keyed array
            $shuffledStudents[$this_student->id] = array(
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'first_last_combined' => $this_student->getFullname(),
            );

            // Set the rest of of the students to this students classmates (don't want to repeat current student)
            // this data should include the ['id'] key
            $students = $this_student->getClassmates();
        }

        // randomize the order of any remaining students and anonymize and number their names
        shuffle($students);
        $counter = 1;
        foreach ($students as $id => $student) {
            $studentId = (isset($student['id'])) ? $student['id'] : $id;
            $shuffledStudents[$studentId] = array(
                'first_name' => self::ANONYMOUS_NAME_TEXT ." ($counter)",
                'last_name' => '',
                'first_last_combined' => self::ANONYMOUS_NAME_TEXT ." ($counter)",
            );
            $counter++;
        }

        return $shuffledStudents;
    }


    /**
     * Retrieve student data for a list of student_ids and transform into literal or anonymized list of student names
     * to return a clean array of student names (anonymized per request) keyed with student id
     *
     * @param User $user
     * @param UserRepository $repository
     * @param array $student_ids
     * @param bool $anon
     *
     * @return array
     */
    public function transformStudentIds(User $user, UserRepository $repository, array $student_ids, $anon = FALSE)
    {
        $keyed_students = array();

        // if this is anonymous, shuffle the students
        if ($anon) {
            $keyed_students = self::shuffleAndAnonymizeStudents($user, $student_ids);
        } else {
            // otherwise, get the students' names
            $student_data = $repository->getStudentNames($student_ids);
            foreach ($student_data as $id => $student) {
                $keyed_students[$student['id']] = array(
                    'first_last_combined' => $student['first_name'] . ' ' . $student['last_name'],
                    'first_name' => $student['first_name'],
                    'last_name' => $student['last_name']
                );
            }
        }

        return $keyed_students;
    }
} 