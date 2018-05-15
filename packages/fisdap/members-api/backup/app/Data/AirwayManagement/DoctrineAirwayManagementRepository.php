<?php namespace Fisdap\Data\AirwayManagement;

use Fisdap\Data\Repository\DoctrineRepository;
use phpDocumentor\Reflection\Types\Boolean;
use Zend_Registry;

/**
 * Class DoctrineAirwayManagementRepository
 *
 * @package Fisdap\Data\AirwayManagement
 */
class DoctrineAirwayManagementRepository extends DoctrineRepository implements AirwayManagementRepository
{
    /*
     * Returns a bunch of raw data that's used for the Airway Management Report
     * This function probably isn't all that useful in other places, but I suppose it could be :)
     *
     * @param Array $student_ids array of studnet ids to get airway management records for
     * @param Array $site_ids array of site ids
     * @param DateTime $start_date the start date of the
     */
    public function getDataForReport($student_ids, $site_ids = null, $start_date = null, $end_date = null, $patient_types = null, $audited_only = false)
    {
        $qb = $this->_em->createQueryBuilder();

        $partials  = "partial am.{id,success,performed_by},";
        $partials .= "partial ams.{id},";
        $partials .= "partial p.{id,age,months},";
        $partials .= "partial am_airway.{id,attempts,created,success,skill_order},";
        $partials .= "partial am_airway_pro.{id,type},";
        $partials .= "partial a.{id,attempts,created,success},";
        $partials .= "partial airway_procedure.{id,type},";
        $partials .= "partial lpi.{id},";
        $partials .= "partial lpd.{id,name},";
        $partials .= "partial shift.{id,start_datetime,type,hours},";
        $partials .= "partial subject.{id,name,type},";
        $partials .= "partial student.{id,first_name,last_name},";
        $partials .= "partial site.{id,name},";
        $partials .= "partial base.{id,name}";

        $qb->select($partials)
            ->from("\\Fisdap\\Entity\\AirwayManagement", 'am')
            ->leftJoin('am.airway_management_source', 'ams')
            ->leftJoin('am.patient', 'p')
            ->leftJoin('am.airway', 'am_airway')
            ->leftJoin('am_airway.procedure', 'am_airway_pro')
            ->leftJoin('p.airways', 'a')
            ->leftJoin('a.procedure', 'airway_procedure')
            ->leftJoin('am.practice_item', 'lpi')
            ->leftJoin('lpi.practice_definition', 'lpd')
            ->leftJoin('am.shift', 'shift')
            ->leftJoin('am.subject', 'subject')
            ->leftJoin('shift.student', 'student')
            ->leftJoin('shift.site', 'site')
            ->leftJoin('shift.base', 'base')
            ->andWhere($qb->expr()->in('student.id', $student_ids));

        if ($audited_only) {
            $qb->andWhere('shift.audited = 1');
        }

        if (isset($site_ids)) {
            $qb->andWhere($qb->expr()->in('site.id', $site_ids));
        }

        if (isset($patient_types)) {
            $qb->andWhere($qb->expr()->in('subject.id', $patient_types));
        }

        $param_count = 1;

        if ($start_date) {
            $qb->andWhere("shift.start_datetime >= ?1");
            $start_datetime_object = date_create($start_date);
            $qb->setParameter(1, $start_datetime_object->format("Y-m-d"));
            $param_count++;
        }

        if ($end_date) {
            $qb->andWhere("shift.start_datetime <= ?" . $param_count);
            $end_datetime_object = date_create($end_date);
            $qb->setParameter($param_count, $end_datetime_object->format("Y-m-d 23:59:59"));
        }

        $qb->orderBy("shift.start_datetime, lpi.time");

        return $qb->getQuery()->getArrayResult();
    }

    public function getETDataForReport($student_ids, $site_ids = null, $start_date = null, $end_date = null, $patient_types = null, $audited_only = false)
    {
        $qb = $this->_em->createQueryBuilder();

        $partials = "partial a.{id,attempts,created,success,skill_order,performed_by},";
        $partials .= "partial shift.{id,start_datetime,type,hours,audited},";
        $partials .= "partial p.{id,age,months},";
        $partials .= "partial procedure.{id,name,type},";
        $partials .= "partial subject.{id,name,type},";
        $partials .= "partial student.{id,first_name,last_name},";
        $partials .= "partial site.{id,name},";
        $partials .= "partial base.{id,name}";

        $qb->select($partials)
            ->from("\\Fisdap\\Entity\\Airway", 'a')
            ->leftJoin('a.shift', 'shift')
            ->leftJoin('a.patient', 'p')
            ->leftJoin('a.procedure', 'procedure')
            ->leftJoin('a.subject', 'subject')
            ->leftJoin('shift.student', 'student')
            ->leftJoin('shift.site', 'site')
            ->leftJoin('shift.base', 'base')
            ->where("procedure.type = 'Endotracheal Intubation' OR procedure.type = 'EndotrachealIntubation'")
            ->andWhere($qb->expr()->in('student.id', $student_ids));

        if ($audited_only) {
            $qb->andWhere('shift.audited = 1');
        }

        if (isset($site_ids)) {
            $qb->andWhere($qb->expr()->in('site.id', $site_ids));
        }

        if (isset($patient_types)) {
            $qb->andWhere($qb->expr()->in('subject.id', $patient_types));
        }

        $param_count = 1;

        if ($start_date) {
            $qb->andWhere("shift.start_datetime >= ?1");
            $start_datetime_object = date_create($start_date);
            $qb->setParameter(1, $start_datetime_object->format("Y-m-d"));
            $param_count++;
        }

        if ($end_date) {
            $qb->andWhere("shift.start_datetime <= ?" . $param_count);
            $end_datetime_object = date_create($end_date);
            $qb->setParameter($param_count, $end_datetime_object->format("Y-m-d 23:59:59"));
        }

        $qb->orderBy("shift.start_datetime, a.id");

        return $qb->getQuery()->getArrayResult();
    }


    public function getETTotals($student_id, $goal_set, $include_coa_success_rate_data = false, $filters = array())
    {
        if (count($filters) > 0) {
            // format the filters in a way that our 'getDataForReport' will understand:
            $site_ids = $start_date = $end_date = $patient_types = $audited_only = null;

            // site ids
            $site_ids = (isset($filters['shiftSites'])) ? $filters['shiftSites'] : null;

            // shift start date
            $start_date = (isset($filters['startDate']) && strlen($filters['startDate'] > 0)) ? $filters['startDate']->format("Y-m-d") : null;

            // shift end date
            $end_date = (isset($filters['endDate']) && strlen($filters['endDate'] > 0)) ? $filters['endDate']->format("Y-m-d") : null;

            // patient types
            $patient_types = (isset($filters['subjectTypes'])) ? $filters['subjectTypes'] : null;

            // audited only
            $audited_only = (isset($filters['auditedOrAll']) && $filters['auditedOrAll'] instanceof Boolean) ? $filters['auditedOrAll'] : null;

            $raw_data = $this->getETDataForReport($student_id, $site_ids, $start_date, $end_date, $patient_types, $audited_only);
        } else {
            $raw_data = $this->getETDataForReport($student_id);
        }

        usort(
            $raw_data,
            function ($a, $b) {
                if ($a['shift']['start_datetime'] == $b['shift']['start_datetime']) {
                    if ($a['patient']['id'] == $b['patient']['id']) {
                        return $a['skill_order'] < $b['skill_order'];
                    } else {
                        return $a['patient']['id'] < $b['patient']['id'];
                    }
                } else {
                    return $a['shift']['start_datetime'] < $b['shift']['start_datetime'];
                }
            }
        );

        $et_total_count = 0;
        $et_success_count = 0;
        foreach ($raw_data as $et) {
            if ($et['performed_by']) {
                if ($et_total_count < 10) {
                    $et_total_count += $et['attempts'];
                    if ($et['success'] == 1) {
                        $et_success_count++;
                    }
                } else {
                    $et_total_count += $et['attempts'];
                }
            }
        }

        $return_array = [
            "window" => 10,
            "attempts" => $et_total_count,
            "success_count" => $et_success_count
        ];

        return $return_array;
    }

    public function getTotals($student_id, $goal_set, $include_coa_success_rate_data = false, $filters = array())
    {
        if (count($filters) > 0) {
            // format the filters in a way that our 'getDataForReport' will understand:
            $site_ids = $start_date = $end_date = $patient_types = $audited_only = null;

            // site ids
            $site_ids = (isset($filters['shiftSites'])) ? $filters['shiftSites'] : array();

            // shift start date
            $start_date = ($filters['startDate']) ? $filters['startDate']->format("Y-m-d") : null;

            // shift end date
            $end_date = ($filters['endDate']) ? $filters['endDate']->format("Y-m-d") : null;

            // patient types
            $patient_types = (isset($filters['subjectTypes'])) ? $filters['subjectTypes'] : array();

            // audited only
            $audited_only = $filters['auditedOrAll'];

            $raw_data = $this->getDataForReport(array($student_id), $site_ids, $start_date, $end_date, $patient_types, $audited_only);
        } else {
            $raw_data = $this->getDataForReport(array($student_id));
        }

        $total_attempts = 0;
        $success_count = 0;

        $observed_count = 0;
        $scenario_count = 0;

        $total_patients = array("Neonate" => 0, "Infant" => 0, "Pediatric" => 0, "Adult" => 0, "Unknown" => 0);
        $total_sims = array("Manikin - sim" => 0 , "Manikin - other" => 0, "Animal" => 0, "Live human" => 0, "Cadaver" => 0);

        $eureka_attempts = array();
        $eureka_dates = array();

        $et_data = array();

        foreach ($raw_data as $attempt) {
            // only include this attempt if the student performed it
            if ($attempt['performed_by']) {
                $source = $attempt['airway_management_source']['id'];

                // If this was a lab shift, add to "Simulations" chart
                if ($attempt['shift']['type'] == "lab") {

                    // if it was an LPI, figure out what type of patient it was
                    if ($source == 1) {
                        // add to our total attempts
                        $total_attempts++;

                        if ($attempt['subject']['name'] == 'Manikin') {
                            $key = $attempt['subject']['name'] . " - " . $attempt['subject']['type'];
                        } elseif ($attempt['subject']['name'] == "Animal") {
                            $key = "Animal";
                        } else {
                            $key = ($attempt['subject']['type'] == "live") ? "Live human" : "Cadaver";
                        }

                        $total_sims[$key]++;
                    } else {
                        $total_attempts++;

                        if ($attempt['subject']['name'] == 'Manikin') {
                            $key = $attempt['subject']['name'] . " - " . $attempt['subject']['type'];
                        } elseif ($attempt['subject']['name'] == "Animal") {
                            $key = "Animal";
                        } else {
                            $key = ($attempt['subject']['type'] == "live") ? "Live human" : "Cadaver";
                        }

                        $total_sims[$key]++;

                        $scenario_count++;
                    }
                } else {
                    // add to our total attempts
                    $total_attempts++;

                    // Add to our "Live Patients" chart
                    $patient = $attempt['patient'];

                    if (isset($patient['age'])) {
                        $age = ((intval($patient['age']) * 12) + (intval($patient['months'])))/12;
                        $age_group_name = $goal_set->getAgeFieldName($age, true);
                        $total_patients[$age_group_name]++;
                    } else {
                        $total_patients["Unknown"]++;
                    }
                } // end shift type if

                // add it to our eureka array
                $eureka_attempts[] = ($attempt['success'] === true) ? 1 : 0;
                $eureka_dates[] = $attempt['shift']['start_datetime'];

                if ($attempt['success'] === true) {
                    $success_count++;
                }
            } // end if performed by
            else {
                $observed_count++;
            }
        } // end for each attempt

        if ($total_sims['Live human'] == 0) {
            unset($total_sims['Live human']);
        }

        $return_array = array("total" => $total_attempts,
            "sims" => $total_sims,
            "patients" => $total_patients,
            "eureka_attempts" => $eureka_attempts,
            "eureka_dates" => $eureka_dates,
            "total_successes" => $success_count,
            "total_observed" => $observed_count,
            "total_scenarios" => $scenario_count);

        if ($include_coa_success_rate_data) {
            $window = 20;
            $attempt_count = count($eureka_attempts);
            $starting_point = $attempt_count - $window;

            $coa_attempts = array();
            $coa_dates = array();
            $coa_success_count = 0;

            if ($starting_point < 0) {
                $coa_attempts = $eureka_attempts;
                $coa_dates = $eureka_dates;
                if ($eureka_attempts) {
                    foreach ($eureka_attempts as $attempt) {
                        if ($attempt === 1) {
                            $coa_success_count++;
                        }
                    }
                }
            } else {
                for ($i = $starting_point; $i < $attempt_count; $i++) {
                    if (isset($eureka_attempts[$i])) {
                        $coa_attempts[] = $eureka_attempts[$i];
                        $coa_dates[] = $eureka_dates[$i];

                        if ($eureka_attempts[$i]) {
                            $coa_success_count++;
                        }
                    }
                }
            }

            $return_array['coa_success_rate_data'] = array("window" => $window, "attempts" => $coa_attempts, "dates" => $coa_dates, "success_count" => $coa_success_count);
        }

        return $return_array;
    } // end getTotals

    public function getIdByPatient($patient_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('am')
            ->from('\Fisdap\Entity\AirwayManagement', 'am')
            ->where('am.patient = ?1')
            ->setParameter(1, $patient_id);

        $res = $qb->getQuery()->getResult();

        $return_val = false;

        if ($res) {
            foreach ($res as $am) {
                $return_val = $am->id;
            }
        }

        return $return_val;
    }

    public function getByLabPracticeItem($practice_item_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('am')
            ->from('\Fisdap\Entity\AirwayManagement', 'am')
            ->where('am.practice_item = ?1')
            ->andWhere('am.airway_management_source = 1')
            ->setParameter(1, $practice_item_id);

        $res = $qb->getQuery()->getResult();
        $single_result = false;
        if ($res) {
            foreach ($res as $result) {
                $single_result = $result;
            }
        }

        return $single_result;
    }

    public function getByRun($run_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('am')
            ->from('\Fisdap\Entity\AirwayManagement', 'am')
            ->join('am.patient', 'p')
            ->where('p.run = ?1')
            ->setParameter(1, $run_id);

        return $qb->getQuery()->getResult();
    }

    public function getAllByShift($shift_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('am')
            ->from('\Fisdap\Entity\AirwayManagement', 'am')
            ->where('am.shift = ?1')
            ->setParameter(1, $shift_id);

        return $qb->getQuery()->getArrayResult();
    }

    public function getPracticeDefinitionsByProgram($program_id, $cert_id)
    {
        // get all of this program's lab practice definitions that have airway management credit
        $qb = $this->_em->createQueryBuilder();

        $qb->select('partial d.{id}')
            ->from('\Fisdap\Entity\PracticeDefinition', 'd')
            ->where('d.airway_management_credit = 1')
            ->andWhere('d.certification_level = ?1')
            ->andWhere('d.program = ?2')
            ->setParameter(1, $cert_id)
            ->setParameter(2, $program_id);

        $results = $qb->getQuery()->getArrayResult();
        $return_val = false;

        if ($results) {
            $return_val = array();
            foreach ($results as $id) {
                $return_val[] = $id['id'];
            }
        }

        return $return_val;
    }
}
