<?php
/**
 * Created by PhpStorm.
 * User: jmortenson
 * Date: 6/18/14
 * Time: 6:25 PM
 */

namespace Fisdap\Service;

class CoreSubjectService implements SubjectService
{
    public function makeSubjectIdsArray(\Doctrine\ORM\EntityRepository $repository, $subjectIds)
    {
        $type_ids = array();

        // no type selected means give them all types
        if (is_null($subjectIds)) {
            $patientTypes = $repository->findAll();
            foreach ($patientTypes as $type) {
                $type_ids[] = $type->id;
            }
            return $type_ids;
        }

        // if we're here, something was selected
        foreach ($subjectIds as $type_id) {
            // just add regular ids to the list
            if (is_numeric($type_id)) {
                $type_ids[] = $type_id;
            }
        }

        return $type_ids;
    }
}
