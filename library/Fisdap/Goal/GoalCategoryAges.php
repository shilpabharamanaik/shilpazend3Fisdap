<?php

namespace Fisdap\Goal;

/**
 *	Calculates student goals for Ages category
 *	@autor Maciej
 */
class GoalCategoryAges extends GoalCategoryBase
{
    // links to goalDefs
    protected $ageGoals = array(
        'pediatric' => 15,
        'newborn' => 14,
        'infant' => 13,
        'toddler' => 12,
        'preschooler' => 11,
        'school_age' => 10,
        'adolescent' => 9,
        'adult' => 8,
        'geriatric' => 7,
        'other' => 90,	// unknown age
        'all' => 91,
    );

    const TOTAL_ASSESSMENTS = 133;

    protected function forEachPatient(&$patient)
    {
        // all ages
        //$mem = memory_get_usage();
        $this->add($this->ageGoals['all'], true, $patient);
        //$memDeltas[1] = round(($mem - memory_get_usage()) / 1000);
        //$memDelta = round((memory_get_usage() - $mem) / 1000);

        //$this->logger->debug('Mem delta inside forEachPatient: ' . $memDelta . ' KBs'); //' . print_r($memDeltas, TRUE) );

        // all except pediatric
        //$mem = memory_get_usage();
        $ageGroup = $this->goalSet->ages->getAgeGroupForAge($patient['age'], $patient['months']);
        //$memDeltas[2] = round(($mem - memory_get_usage()) / 1000);

        //$mem = memory_get_usage();
        $this->add($this->ageGoals[$ageGroup], true, $patient);
        //$memDeltas[3] = round(($mem - memory_get_usage()) / 1000);

        // pediatric
        //$mem = memory_get_usage();
        $this->add(
            $this->ageGoals['pediatric'],
            true,
            $patient,
            $this->goalSet->ages->isPediatricAge($patient['age'], $patient['months'])
        );
        //$memDeltas[4] = round(($mem - memory_get_usage()) / 1000);

        //add to total assessment count
        $this->add(self::TOTAL_ASSESSMENTS, true, $patient);
    }
}
