<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Card Course Certifications.
 *
 * @Entity
 * @Table(name="fisdap2_course_certification")
 */
class CourseCertification extends Enumerated
{
    /**
     * adjusts the date to include day if not present
     * @param date the date to be formated
     * @return returns the formatted date
     *
     */
    public static function formatDate($date)
    {
        
        // keep track of slashes, if there's only one we need to format it
        $slashPos = strpos($date, "/");
        $slashPos2 = strpos($date, "/", $slashPos + 1);
        
        if ($slashPos2 === false) {
            if (substr($date, 1, 1) == "/") {
                $month = substr($date, 0, 1);
            } else {
                $month = substr($date, 0, 2);
            }
            
            if (substr($date, -3, 1) == "/") {
                $year = substr($date, -2, 2);
            } else {
                $year = substr($date, -4, 4);
            }
            $actualDate = $month . "/01/" . $year;
            return $actualDate;
        } else {
            return $date;
        }
    }
}
