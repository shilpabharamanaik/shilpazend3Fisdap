<?php

namespace Fisdap\Goal;

/**
 *	Calculates student goals for Ohio specific goals
 *	@author smcintyre
 */
class GoalCategoryOhio extends GoalCategoryBase
{
    const MED_ADMIN_IV = 127;
    const MED_ADMIN_SUBCUT = 128;
    const MED_ADMIN_IM = 129;
    const MED_ADMIN_BRONCH = 130;
    const MED_ADMIN_ORAL = 131;
    const TOTAL_MED_ADMIN = 132;
    const MED_ADMIN_SUBLING = 134;
    const MED_ADMIN_BOLUS = 136;

    protected function forEachShift(&$shift)
    {
        $meds = \Fisdap\Entity\Med::getAllByShiftSQL($shift['Shift_id']);
        $airways = \Fisdap\Entity\Airway::getAllByShiftSQL($shift['Shift_id']);

        if ($airways) {
            foreach ($airways as $airway) {
                if (!\Fisdap\Entity\Airway::countsTowardGoalSQL($airway, $this->dataReq)) {
                    continue;
                }

                $patient = \Fisdap\Entity\Patient::getExamInterviewTeamLeadArray($airway['patient_id']);
                
                //if this is a nebulizer treatment, add to bronchodilator goal
                if ($airway['procedure_id'] == 17) {
                    $this->add(self::MED_ADMIN_BRONCH, $airway['performed_by'], $patient);
                }
            }
        }

        if ($meds) {
            foreach ($meds as $med) {
                if (!\Fisdap\Entity\Med::countsTowardGoalSQL($med, $this->dataReq)) {
                    continue;
                }

                $patient = \Fisdap\Entity\Patient::getExamInterviewTeamLeadArray($med['patient_id']);
                $this->add(self::TOTAL_MED_ADMIN, $med['performed_by'], $patient);
                                

                //if this is Albuterol, add to bronchodilator goal
                if ($med['route_id'] == 30) {
                    $this->add(self::MED_ADMIN_BRONCH, $med['performed_by'], $patient);
                }

                //IV count
                if ($med['route_id'] == 13 || $med['route_id'] == 17 || $med['route_id'] == 32) {
                    $this->add(self::MED_ADMIN_IV, $med['performed_by'], $patient);
                }

                //Subcutaneous
                if ($med['route_id'] == 2) {
                    $this->add(self::MED_ADMIN_SUBCUT, $med['performed_by'], $patient);
                }

                //Intramuscular
                if ($med['route_id'] == 1) {
                    $this->add(self::MED_ADMIN_IM, $med['performed_by'], $patient);
                }

                //Oral
                if ($med['route_id'] == 5) {
                    $this->add(self::MED_ADMIN_ORAL, $med['performed_by'], $patient);
                }

                //Sublingual
                if ($med['route_id'] == 3) {
                    $this->add(self::MED_ADMIN_SUBLING, $med['performed_by'], $patient);
                }

                //IV Bolus/Push
                if ($med['route_id'] == 17 || $med['route_id'] == 32) {
                    $this->add(self::MED_ADMIN_BOLUS, $med['performed_by'], $patient);
                }
            }
        }
    }
}
