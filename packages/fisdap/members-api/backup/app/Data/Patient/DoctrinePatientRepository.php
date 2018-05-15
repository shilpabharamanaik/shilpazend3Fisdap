<?php namespace Fisdap\Data\Patient;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrinePatientRepository
 *
 * @package Fisdap\Data\Patient
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrinePatientRepository extends DoctrineRepository implements PatientRepository
{
    public function getPatientsByRun($runId, $filters = null)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select('p, r')
           ->from('\Fisdap\Entity\Patient', 'p')
           ->leftJoin('p.run', 'r')
           ->where('r.id = ?1')
           ->setParameter(1, $runId);
        
        return $qb->getQuery()->getResult();
    }
    
    public function getPatientsByShift($shiftId, $filters = null)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select('p, s')
           ->from('\Fisdap\Entity\Patient', 'p')
           ->leftJoin('p.shift', 's')
           ->where('s.id = ?1')
           ->setParameter(1, $shiftId);
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Specialty function to fix a patient snafu from the IOS app
     *
     * @param $studentId Student ID of the student we want to check
     */
    public function getPatientsForExamInterviewTool($studentId)
    {
        $qb = $this->_em->createQueryBuilder();

        //very specific query to find patients matching a particular configuration created after the launch of the mobile app
        $qb->select('p, s')
            ->from('\Fisdap\Entity\Patient', 'p')
            ->leftJoin('p.shift', 's')
            ->where('p.student = ?1')
            ->andWhere("p.created >= '2017-03-01 00:00:00'")
            ->andWhere('p.age IS NOT NULL')
            ->andWhere('p.primary_impression IS NOT NULL')
            ->andWhere('p.gender IS NOT NULL')
            ->andWhere('p.exam IS NULL')
            ->andWhere('p.interview IS NULL')
            ->setParameter(1, $studentId);

        return $qb->getQuery()->getResult();
    }
    
    /**
     * The source argument is basically where the request is coming from.  If it's set to "Exchange"
     * then we need to find out the priorities and ALS/BLS states for those skills for reporting on the
     * scenarios page.
     * @param integer $patientId ID of the patient to find skills for
     * @param string $source String representing where the request for skills is coming from- defaults to "null".
     */
    public function getSkillsByPatient($patientId, $source = null)
    {
        $ivs = $this->_em->getRepository('\Fisdap\Entity\Iv')->findByPatient($patientId);
        $vitals = $this->_em->getRepository('\Fisdap\Entity\Vital')->findByPatient($patientId);
        $airways = $this->_em->getRepository('\Fisdap\Entity\Airway')->findByPatient($patientId);
        $other_interventions = $this->_em->getRepository('\Fisdap\Entity\OtherIntervention')->findByPatient($patientId);
        $cardiac_interventions = $this->_em->getRepository('\Fisdap\Entity\CardiacIntervention')->findByPatient($patientId);
        $meds = $this->_em->getRepository('\Fisdap\Entity\Med')->findByPatient($patientId);
        
        $skills = array_merge($ivs, $vitals, $airways, $other_interventions, $cardiac_interventions, $meds);
        usort($skills, array("self", "comparator"));
        
        if ($source == "Exchange") {
            // Run the currently collected skills through and add on the priority and als/bls data for those skills
            $skills = $this->getScenarioMetadata($skills);
        }
        
        return $skills;
    }
    
    private static function comparator($a, $b)
    {
        if ($a->skill_order == $b->skill_order) {
            return 0;
        } elseif ($a->skill_order > $b->skill_order) {
            return 1;
        } else {
            return -1;
        }
    }
    
    private function getScenarioMetadata($skills)
    {
        $repo = \Fisdap\EntityUtils::getRepository('ScenarioSkill');
        
        $cleanSkills = array();
        
        // Check in with the ScenarioSkills repo to see if we have any metadata we need to apply to these
        // skills for this scenario
        foreach ($skills as $skill) {
            // Grab only the non-namespaced part of the class name.
            $sp = explode('\\', get_class($skill));
            $skillClass = array_pop($sp);
            
            // These get saved down once the user changes skill state- if one isn't found, initialize it with the defaults
            $scenarioSkillData = $repo->findOneBy(array('skill_id' => $skill->id, 'skill_type' => $skillClass));
            
            if ($scenarioSkillData) {
                $skill->is_als = $scenarioSkillData->is_als;
                $skill->priority = $scenarioSkillData->priority;
            } else {
                $skill->priority = 2;
                $skill->is_als = \Fisdap\Entity\ScenarioSkill::getSkillAlsState($skillClass, $skill->id);
            }
            
            $cleanSkills[] = $skill;
        }
        
        return $cleanSkills;
    }
}
