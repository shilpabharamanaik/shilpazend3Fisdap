<?php

namespace User\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;

/**
 * Base class for enumerated procedure tables.  Overrides the getFormOptions
 * function to filter based on program settings.
 * @MappedSuperclass
 */
class Procedure extends Enumerated
{
    /**
     * This overrides the default enumerated class' getFormOptions.  It provides
     * the ability to filter the outgoing procedures based on the current program
     * settings.
     *
     * @param Boolean $na Determines whether or not to include an "N/A" option
     * in the list. Defaults to false.
     * @param Boolean $sort Determines whether or not to sort the returning list/
     * Defaults to true.
     * @param Boolean $filter Determines whether or not to filter the list based
     * on program settings.  Defaults to true.
     *
     * @return Array containing the requested list of procedures, with the index
     * being the ID of the entity, and the value at that index the name field of
     * the entity.
     */
    public static function getFormOptions($na = false, $sort=true, $filter=true)
    {
        $results = parent::getFormOptions($na, $sort);
        
        // If no user is logged in, return the full set.  Useful for any
        // CLI script that needs to access this stuff.  Presumably.
        $user = \Fisdap\Entity\User::getLoggedInUser();
        
        // If a user is logged in, and the filter option is true, perform the
        // filter.
        if ($user && $filter) {
            $filteredProcedures = array();
            
            $programId = $user->getProgramId();
            
            $classSplit = explode("\\", get_called_class());
            $className = $classSplit[count($classSplit)-1];
            
            $procTypeEntityName = "\Fisdap\Entity\Program" . $className;
            
            foreach ($results as $id => $name) {
                if ($procTypeEntityName::programIncludesProcedure($programId, $id)) {
                    $filteredProcedures[$id] = $name;
                }
            }
            
            $results = $filteredProcedures;
        }
        
        return $results;
    }
}
