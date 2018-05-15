<?php
namespace Fisdap\Data\Site;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineSiteLegacyRepository
 *
 * @package Fisdap\Data\Site
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineSiteLegacyRepository extends DoctrineRepository implements SiteLegacyRepository
{
    public function getAdminSites($program_id)
    {
        $sql = "
			SELECT s.Site_id as id
			FROM   ProgramSiteAssoc s
			JOIN   ProgramData p
			ON     s.Program_id = p.Program_id
			WHERE  Main = 1
			AND    Approved = 1
			AND    p.Program_id = " . $program_id;
        
        $conn = $this->_em->getConnection();
        $arr = array();
        $res = $conn->query($sql);

        while ($row = $res->fetch()) {
            $arr[] = $row['id'];
        }

        return $arr;
    }


    public function getUserContextsAttendingSites($site_ids, $program_id)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select("partial ur.{id}, partial sa.{id}, partial a.{id,archived}, partial r.{id}, partial s.{id}, partial e.{id}")
           ->from('\Fisdap\Entity\UserContext', 'ur')
           ->leftJoin('ur.slot_assignments', 'sa')
           ->leftJoin('ur.requirement_attachments', 'a')
           ->leftJoin('a.requirement', 'r')
           ->leftJoin('sa.slot', 's')
           ->leftJoin('s.event', 'e')
           ->where($qb->expr()->in('e.site', $site_ids))
           ->andWhere('e.start_datetime >= CURRENT_TIMESTAMP()')
           ->andWhere('ur.program = ?1')
           ->setParameter(1, $program_id);
        
        return $qb->getQuery()->getResult();
    }


    public function getUserContextsAttendingSharedSite($site_id)
    {
        $site = \Fisdap\EntityUtils::getEntity('SiteLegacy', $site_id);
        $program_ids = array_keys($site->getSharedPrograms());
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select("partial ur.{id}, partial sa.{id}, partial a.{id,archived}, partial r.{id}, partial s.{id}, partial e.{id}")
           ->from('\Fisdap\Entity\UserContext', 'ur')
           ->leftJoin('ur.slot_assignments', 'sa')
           ->leftJoin('ur.requirement_attachments', 'a')
           ->leftJoin('a.requirement', 'r')
           ->leftJoin('sa.slot', 's')
           ->leftJoin('s.event', 'e')
           ->where($qb->expr()->in('ur.program', $program_ids))
           ->andWhere('e.start_datetime >= CURRENT_TIMESTAMP()')
           ->andWhere('e.site = ?1')
           ->setParameter(1, $site_id);
        
        return $qb->getQuery()->getResult();
    }
    
    public function getSharedSites($program_id)
    {
        $sql = "
			SELECT s.Site_id as id
			FROM   ProgramSiteAssoc s
			JOIN   ProgramData p
			ON     s.Program_id = p.Program_id
			WHERE  Approved = 1
			AND    p.Program_id = " . $program_id;
        
        $conn = $this->_em->getConnection();
        $arr = array();
        $res = $conn->query($sql);

        while ($row = $res->fetch()) {
            $arr[] = $row['id'];
        }

        return $arr;
    }
    
    public function getScheduledSitesByUserContext($userContextId)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $today = new \DateTime();
        
        $qb->select("distinct s.id")
           ->from('\Fisdap\Entity\SlotAssignment', 'sa')
           ->leftJoin('sa.slot', 'slot')
           ->leftJoin('slot.event', 'e')
           ->leftJoin('e.site', 's')
           ->where('sa.user_context = ?1')
           ->andWhere('e.start_datetime >= ' . $today->format("'Y-m-d 59:59:59'"))
           ->setParameter(1, $userContextId);
        
        return $qb->getQuery()->getArrayResult();
    }
    
    public function getSiteAssociationsByProgram($programId, $active = true)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("s, p")
           ->from('\Fisdap\Entity\ProgramSiteLegacy', 'p')
           ->leftJoin('p.site', 's')
           ->where('p.program = ?1')
           ->setParameter(1, $programId)
           ->orderBy('s.name');
           
        if ($active) {
            $qb->andWhere('p.active = true');
        }
        
        return $qb->getQuery()->getResult();
    }

    
    public function getAvailableSitesByProgram($programId, $state)
    {
        $program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $programId);
        $country = $program->country;
        
        $sql = "
			SELECT
				s.AmbServ_id as id,
				s.AmbServName as name,
				s.Address as address,
				s.city as city,
				s.PostalCode as postalcode,
				s.Region as state,
				s.ContactName as contact,
				s.ContactTitle as title,
				s.type as type
			FROM
				AmbulanceServices s
			WHERE
				s.AmbServ_id NOT IN (
					SELECT DISTINCT(s.AmbServ_id)
					FROM
						AmbulanceServices s
					LEFT JOIN
						ProgramSiteData psl
					ON
						s.AmbServ_id = psl.AmbServ_id
					WHERE
						psl.Program_id = " . $programId . " "
                . ")
			AND s.Region = '$state'
			AND s.Country = '$country'
			ORDER BY name
		";

        $conn = $this->_em->getConnection();
        $result = $conn->query($sql);
        return $result;
    }
    
    public function getSiteCountByProgram($programId)
    {
        $sql = "
			SELECT
				count(s.AmbServ_id) as count
			FROM
				AmbulanceServices s
			LEFT JOIN
				ProgramSiteData psl
			ON
				s.AmbServ_id = psl.AmbServ_id
			WHERE
				psl.Program_id = " . $programId;
    
        $conn = $this->_em->getConnection();
        $rawSites = $conn->query($sql);
        

        foreach ($rawSites as $site) {
            return $site['count'];
        }
    }
    
    public function getSitesByProgram($programId, $type = null, $sort = null, $orderDirection = null, $active = null)
    {
        if ($type) {
            if (!is_array($type)) {
                $type = array($type);
            }
            
            $typeFilter = "AND (";

            foreach ($type as $typeItem) {
                $typeFilter .= "s.type = '" . lcfirst($typeItem) . "'";
                if (end($type) != $typeItem) {
                    $typeFilter .= " OR ";
                } else {
                    $typeFilter .= ")";
                }
            }
        }
        
        if ($sort) {
            if ($sort != "active" && $sort != "shared") {
                if ($sort == "state") {
                    $sort = "Region";
                } elseif ($sort == "name") {
                    $sort = "AmbServName";
                }
                
                $orderBy = "ORDER BY s." . $sort;
            } else {
                $orderBy = "ORDER BY s.AmbServName";
            }
        } else {
            $sort = "name";
            $orderBy = "ORDER BY s.AmbServName";
        }

        if ($orderDirection == "desc") {
            $orderBy .= " DESC";
        } else {
            $orderBy .= " ASC";
        }
        
        if ($active) {
            $activeFilter = "AND psl.Active = 1";
        } else {
            if (is_null($active)) {
            } else {
                $activeFilter = "AND psl.Active = 0";
            }
        }
    
        $sql = "
			SELECT
				s.AmbServ_id as id,
				s.AmbServName as name,
				s.city as city,
				s.PostalCode as postalcode,
				s.Region as state,
				s.type as type
			FROM
				AmbulanceServices s
			LEFT JOIN
				ProgramSiteData psl
			ON
				s.AmbServ_id = psl.AmbServ_id
			WHERE
				psl.Program_id = " . $programId . " "
                . $activeFilter . " "
                . $typeFilter . " "
                . $orderBy . "
		";

        $conn = $this->_em->getConnection();
        $result = $conn->query($sql);

        return $result;
    }

    // returns a nice array all ready for form usage
    public function getFormOptionsByProgram($programId, $type = null, $sort = null, $orderDirection = null, $active = null)
    {
        $form_options = array();
        
        $sites = $this->getSitesByProgram($programId, $type, $sort, $orderDirection, $active);
        $site_types = \Fisdap\Entity\SiteType::getFormOptions();

        if (!$type) {
            foreach ($site_types as $site_type) {
                $form_options[ucfirst($site_type)] = array();
            }
        }
        
        foreach ($sites as $site) {
            if ($type) {
                $id = (string)$site['id'];
                $form_options[$id] = $site['name'];
            } else {
                $id = (string)$site['id'];
                $form_options[ucfirst($site['type'])][$id] = $site['name'];
            }
        }
        
        return $form_options;
    }
        
    // returns a nice array all ready for form usage, with the site type if requested (breaking this into a new function because I'm wary of breaking other things.
    public function getFormOptionsByProgramWithSiteType($programId, $type = null, $sort = null, $orderDirection = null, $active = null, $showTypes = null)
    {
        $form_options = array();
        
        $sites = $this->getSitesByProgram($programId, $type, $sort, $orderDirection, $active);
        $site_types = \Fisdap\Entity\SiteType::getFormOptions();

        if (!$type) {
            foreach ($site_types as $site_type) {
                $form_options[ucfirst($site_type)] = array();
            }
        }
        
        foreach ($sites as $site) {
            if ($type) {
                if ($showTypes) {
                    $id = (string)$site['id'];
                    $form_options[$id]['name'] = $site['name'];
                    $form_options[$id]['type'] = $site['type'];
                } else {
                    $id = (string)$site['id'];
                    $form_options[$id] = $site['name'];
                }
            } else {
                $id = (string)$site['id'];
                $form_options[ucfirst($site['type'])][$id] = $site['name'];
            }
        }
        
        return $form_options;
    }
    
    public function getFormOptionsByIds($site_ids)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('partial s.{id,name,type}')
            ->from('\Fisdap\Entity\SiteLegacy', 's')
            ->add('where', $qb->expr()->in('s.id', '?1'))
            ->setParameter(1, $site_ids)
            ->orderBy('s.type, s.name');

        $raw_sites = $qb->getQuery()->getArrayResult();
        
        $sites = array();
        foreach ($raw_sites as $raw_site) {
            $sites[ucfirst($raw_site['type'])][$raw_site['id']] =  $raw_site['name'];
        }

        return $sites;
    }
    
    public function getProductivityInfo($sites, $program, $filters = array())
    {
        $info = array();
        $siteIdStr = implode(",", $sites);
        $db = \Zend_Registry::get('db');
        
        if ($filters['startDate']) {
            $startDate = date_create($filters['startDate'])->format("Y-m-d 00:00:00");
        } else {
            $startDate = "1970-01-01";
        }
        
        if ($filters['endDate']) {
            $endDate = date_create($filters['endDate'])->format("Y-m-d 00:00:00");
        } else {
            $endDate = date_create("+10 years")->format("Y-m-d 23:59:59");
        }
        
        if (!empty($filters['certLevel'])) {
            $certLevelStr = implode(",", $filters['certLevel']);
        } else {
            $certLevelStr = implode(",", array_keys(\Fisdap\EntityUtils::getRepository('CertificationLevel')->getSortedFormOptions(\Fisdap\Entity\ProgramLegacy::getCurrentProgram()->profession->id)));
        }
        
        //Start by grabbing shift information grouped by base and store them in arrays
        $query = "
		SELECT 
			count(s.Shift_id) as shifts_chosen,
			sum(s.Completed = 1) as shifts_locked,
			sum(s.hours) as hours_chosen,
			sum(IF(s.completed = 0, 0, s.hours)) as hours_locked,
			count(distinct (s.student_id)) as unique_students,
			s.AmbServ_id,
			AmbServName as site_name,
			BaseName as base_name,
			StartBase_id
		FROM
			ShiftData s
				INNER JOIN
			StudentData sd ON sd.Student_id = s.Student_id
				INNER JOIN
			fisdap2_user_roles ur ON ur.id = sd.user_role_id
				INNER JOIN
			AmbulanceServices a ON s.AmbServ_id = a.AmbServ_id
				INNER JOIN
			AmbServ_Bases b ON s.StartBase_id = b.Base_id
		WHERE
			sd.Program_id = $program
				AND s.start_datetime BETWEEN '$startDate' AND '$endDate'
				AND s.AmbServ_id IN ($siteIdStr)
				AND ur.certification_level_id IN ($certLevelStr)
				AND s.attendence_id IN (1,2)
		GROUP BY StartBase_id";
        
        $results = $db->query($query)->fetchAll();
        
        foreach ($results as $result) {
            $info[$result['AmbServ_id']]['bases'][$result['StartBase_id']] = $result;
        }
        
        //now get patient specific info
        $patientQuery = "
		SELECT 
			count(p.id) as patients_count,
			sum(p.team_lead = 1) as team_leads_count,
			AmbServ_id,
			StartBase_id
		FROM
			fisdap2_patients p
				INNER JOIN
			ShiftData s ON p.shift_id = s.Shift_id
				INNER JOIN
			StudentData sd ON sd.Student_id = s.Student_id
				INNER JOIN
			fisdap2_user_roles ur ON ur.id = sd.user_role_id
		WHERE
			sd.Program_id = $program
				AND s.start_datetime BETWEEN '$startDate' AND '$endDate'
				AND s.AmbServ_id IN ($siteIdStr)
				AND ur.certification_level_id IN ($certLevelStr)
				AND s.attendence_id IN (1,2)
		GROUP BY StartBase_id";
        
        $results = $db->query($patientQuery)->fetchAll();
        
        foreach ($results as $result) {
            if (is_array($info[$result['AmbServ_id']]['bases'][$result['StartBase_id']])) {
                $info[$result['AmbServ_id']]['bases'][$result['StartBase_id']] = array_merge($info[$result['AmbServ_id']]['bases'][$result['StartBase_id']], $result);
            } else {
                $info[$result['AmbServ_id']]['bases'][$result['StartBase_id']] = $result;
            }
        }
        
        //now on to skill specific info
        $skillsQuery = "
		SELECT 
			count(distinct (i.id)) as iv_count,
			count(distinct (a.id)) as airway_count,
			count(distinct (m.id)) as meds_count,
			s.AmbServ_id,
			s.StartBase_id
		FROM
			ShiftData s
				INNER JOIN
			StudentData sd ON sd.Student_id = s.Student_id
				INNER JOIN
			fisdap2_user_roles ur ON ur.id = sd.user_role_id
				LEFT JOIN
			fisdap2_airways a ON (a.shift_id = s.Shift_id AND a.performed_by = 1 AND a.procedure_id IN (1,3,5,6,9,10,11,14,15,17,18,19,20,21,22,23,25))
				LEFT JOIN
			fisdap2_ivs i ON (i.shift_id = s.Shift_id AND i.performed_by = 1 AND i.procedure_id IN (1,2,8))
				LEFT JOIN
			fisdap2_meds m ON (m.shift_id = s.Shift_id AND m.performed_by = 1)
		WHERE
			sd.Program_id = $program
				AND s.start_datetime BETWEEN '$startDate' AND '$endDate'
				AND s.AmbServ_id IN ($siteIdStr)
				AND ur.certification_level_id IN ($certLevelStr)
				AND s.attendence_id IN (1,2)
		GROUP BY StartBase_id";
        
        $results = $db->query($skillsQuery)->fetchAll();
        
        foreach ($results as $result) {
            if (is_array($info[$result['AmbServ_id']]['bases'][$result['StartBase_id']])) {
                $info[$result['AmbServ_id']]['bases'][$result['StartBase_id']] = array_merge($info[$result['AmbServ_id']]['bases'][$result['StartBase_id']], $result);
            } else {
                $info[$result['AmbServ_id']]['bases'][$result['StartBase_id']] = $result;
            }
        }
        
        //Finally grab scheduler data
        $availableHoursQuery = "
		SELECT distinct
			(e.Event_id),
			sum(e.Hours) as hours_available,
			site_id,
			StartBase_id
		FROM
			EventData e
				INNER JOIN
			fisdap2_slots s ON s.event_id = e.Event_id
				INNER JOIN
			fisdap2_windows w ON w.slot_id = s.id
				LEFT JOIN
			fisdap2_window_constraints wc ON (wc.window_id = w.id AND wc.constraint_type_id = 2)
				LEFT JOIN
			fisdap2_window_constraint_values wcv ON wcv.constraint_id = wc.id
		WHERE
			w.Program_id = $program
				AND e.start_datetime BETWEEN '$startDate' AND '$endDate'
				AND s.slot_type_id = 1
				AND (wcv.id IS NULL OR wcv.value IN ($certLevelStr))
				AND site_id IN ($siteIdStr)
		GROUP BY StartBase_id";
        
        $results = $db->query($availableHoursQuery)->fetchAll();
        
        foreach ($results as $result) {
            if (is_array($info[$result['site_id']]['bases'][$result['StartBase_id']])) {
                $info[$result['site_id']]['bases'][$result['StartBase_id']] = array_merge($info[$result['site_id']]['bases'][$result['StartBase_id']], $result);
            } else {
                $info[$result['site_id']]['bases'][$result['StartBase_id']] = $result;
            }
        }
        
        //Preceptor Data now
        $preceptorQuery = "
		SELECT 
			pd.FirstName, pd.LastName, s.AmbServ_id, p.preceptor_id
		FROM
			ShiftData s
				INNER JOIN
			StudentData sd ON sd.Student_id = s.Student_id
				INNER JOIN
			fisdap2_user_roles ur ON ur.id = sd.user_role_id
				INNER JOIN
			fisdap2_patients p ON p.shift_id = s.Shift_id
				INNER JOIN
			AmbulanceServices a ON s.AmbServ_id = a.AmbServ_id
				INNER JOIN
			PreceptorData pd ON pd.Preceptor_id = p.preceptor_id
		WHERE
			sd.Program_id = $program
				AND s.start_datetime BETWEEN '$startDate' AND '$endDate'
				AND s.AmbServ_id IN ($siteIdStr)
				AND p.preceptor_id IS NOT NULL
				AND ur.certification_level_id IN ($certLevelStr)
				AND s.attendence_id IN (1,2)
		GROUP BY p.preceptor_id";
        
        $results = $db->query($preceptorQuery)->fetchAll();
        
        foreach ($results as $result) {
            $info[$result['AmbServ_id']]['preceptors'][$result['preceptor_id']] = $result;
        }
        
        //Patient data: patients_count, team_leads_count
        $preceptorPatientsQuery = "
		SELECT 
			count(p.id) as patients_count,
			sum(p.team_lead = 1) as team_leads_count,
			AmbServ_id,
			p.preceptor_id
		FROM
			fisdap2_patients p
				INNER JOIN
			ShiftData s ON p.shift_id = s.Shift_id
				INNER JOIN
			StudentData sd ON sd.Student_id = s.Student_id
				INNER JOIN
			fisdap2_user_roles ur ON ur.id = sd.user_role_id
		WHERE
			sd.Program_id = $program
				AND s.start_datetime BETWEEN '$startDate' AND '$endDate'
				AND s.AmbServ_id IN ($siteIdStr)
				AND p.preceptor_id IS NOT NULL
				AND ur.certification_level_id IN ($certLevelStr)
				AND s.attendence_id IN (1,2)
		GROUP BY p.preceptor_id";
        
        $results = $db->query($preceptorPatientsQuery)->fetchAll();
        
        foreach ($results as $result) {
            if (is_array($info[$result['AmbServ_id']]['preceptors'][$result['preceptor_id']])) {
                $info[$result['AmbServ_id']]['preceptors'][$result['preceptor_id']] = array_merge($info[$result['AmbServ_id']]['preceptors'][$result['preceptor_id']], $result);
            } else {
                $info[$result['AmbServ_id']]['preceptors'][$result['preceptor_id']] = $result;
            }
        }
        
        //Skills data: iv_count, airway_count, meds_count
        $preceptorSkillsQuery = "
		SELECT 
			count(distinct (i.id)) as iv_count,
			count(distinct (a.id)) as airway_count,
			count(distinct (m.id)) as meds_count,
			s.AmbServ_id,
			p.preceptor_id
		FROM
			ShiftData s
				INNER JOIN
			StudentData sd ON sd.Student_id = s.Student_id
				INNER JOIN
			fisdap2_user_roles ur ON ur.id = sd.user_role_id
				INNER JOIN
			fisdap2_patients p on p.shift_id = s.Shift_id
				LEFT JOIN
			fisdap2_airways a ON (a.patient_id = p.id
				AND a.performed_by = 1
				AND a.procedure_id IN (1,3,5,6,9,10,11,14,15,17,18,19,20,21,22,23,25))
				LEFT JOIN
			fisdap2_ivs i ON (i.patient_id = p.id
				AND i.performed_by = 1
				AND i.procedure_id IN (1,2,8))
				LEFT JOIN
			fisdap2_meds m ON (m.patient_id = p.id
				AND m.performed_by = 1)
		WHERE
			sd.Program_id = $program
				AND s.start_datetime BETWEEN '$startDate' AND '$endDate'
				AND s.AmbServ_id IN ($siteIdStr)
				AND p.preceptor_id IS NOT NULL
				AND ur.certification_level_id IN ($certLevelStr)
				AND s.attendence_id IN (1,2)
		GROUP BY p.preceptor_id";
        
        $results = $db->query($preceptorSkillsQuery)->fetchAll();
        
        foreach ($results as $result) {
            if (is_array($info[$result['AmbServ_id']]['preceptors'][$result['preceptor_id']])) {
                $info[$result['AmbServ_id']]['preceptors'][$result['preceptor_id']] = array_merge($info[$result['AmbServ_id']]['preceptors'][$result['preceptor_id']], $result);
            } else {
                $info[$result['AmbServ_id']]['preceptors'][$result['preceptor_id']] = $result;
            }
        }
        
        //Count skills tracker hours for preceptors: shifts_chosen, shifts_locked, hours_chosen, hours_locked, unique_students, first_shift_date, hours_available
        foreach ($info as $siteId => $siteInfo) {
            //Skip this site if we don't have any preceptors
            if (!$siteInfo['preceptors']) {
                continue;
            }
            
            foreach ($siteInfo['preceptors'] as $preceptorId => $preceptorData) {
                $shiftQuery = "
				SELECT
				    distinct(s.Shift_id)
				FROM
					ShiftData s
						INNER JOIN
					StudentData sd ON sd.Student_id = s.Student_id
						INNER JOIN
					fisdap2_user_roles ur ON ur.id = sd.user_role_id
						INNER JOIN
					fisdap2_patients p ON p.shift_id = s.Shift_id
				WHERE
					sd.Program_id = $program
						AND s.start_datetime BETWEEN '$startDate' and '$endDate'
						AND p.preceptor_id = $preceptorId
						AND ur.certification_level_id IN ($certLevelStr)
						AND s.attendence_id in (1,2)";

                $shiftResult = $db->query($shiftQuery)->fetchAll();

                $shiftids = array();

                foreach ($shiftResult as $shift) {
                    $shiftids[] = $shift['Shift_id'];
                }

                $shiftids = implode(",", $shiftids);

                $dataQuery = "
				SELECT
					count(s.Shift_id) as shifts_chosen,
					sum(s.Completed = 1) as shifts_locked,
					sum(s.hours) as hours_chosen,
					sum(IF(s.Completed = 0, 0, s.hours)) as hours_locked,
					count(distinct (s.student_id)) as unique_students,
					min(s.start_datetime) as first_shift_date,
					s.AmbServ_id
				FROM
					ShiftData s
					    INNER JOIN
					AmbulanceServices a ON s.AmbServ_id = a.AmbServ_id
						INNER JOIN
					AmbServ_Bases b ON s.StartBase_id = b.Base_id
				WHERE
				    s.Shift_id in ($shiftids)";

                $result = $db->query($dataQuery)->fetch();
                $info[$result['AmbServ_id']]['preceptors'][$preceptorId]["shifts_chosen"] = $result['shifts_chosen'];
                $info[$result['AmbServ_id']]['preceptors'][$preceptorId]["shifts_locked"] = $result['shifts_locked'];
                $info[$result['AmbServ_id']]['preceptors'][$preceptorId]["hours_chosen"] = $result['hours_chosen'];
                $info[$result['AmbServ_id']]['preceptors'][$preceptorId]["hours_locked"] = $result['hours_locked'];
                $info[$result['AmbServ_id']]['preceptors'][$preceptorId]["unique_students"] = $result['unique_students'];
                $info[$result['AmbServ_id']]['preceptors'][$preceptorId]["first_shift_date"] = date_create($result['first_shift_date'])->format("n/d/Y");
                
                //Now get the hours_available from the Scheduler
                $query = "
				SELECT distinct
					(e.Event_id),
					sum(e.Hours) as hours_available,
					epd.Preceptor_id
				FROM
					EventData e
						INNER JOIN
					EventPreceptorData epd ON epd.Event_id = e.Event_id
						INNER JOIN
					fisdap2_slots s ON s.event_id = e.Event_id
						INNER JOIN
					fisdap2_windows w ON w.slot_id = s.id
						LEFT JOIN
					fisdap2_window_constraints wc ON (wc.window_id = w.id AND wc.constraint_type_id = 2)
						LEFT JOIN
					fisdap2_window_constraint_values wcv ON wcv.constraint_id = wc.id
				WHERE
					w.Program_id = 259
						AND e.start_datetime BETWEEN '$startDate' AND '$endDate'
						AND s.slot_type_id = 1
						AND (wcv.id IS NULL OR wcv.value IN ($certLevelStr))
						AND epd.Preceptor_id = $preceptorId";
                
                $result = $db->query($query)->fetch();
                $info[$siteId]['preceptors'][$preceptorId]["hours_available"] = $result['hours_available'];
            }
        }
        
        return $info;
    }
    
    public function parseSelectedSites($chosen, $sortedByType = false)
    {
        $program_id = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();
        $site_ids = array();
        
        // no sites selected means give them all sites
        if (empty($chosen) || $chosen == "all") {
            $all_sites = $this->getSitesByProgram($program_id, null, null, null, null);
            foreach ($all_sites as $site) {
                if ($sortedByType) {
                    $site_ids[$site['type']][] = $site['id'];
                } else {
                    $site_ids[] = $site['id'];
                }
            }
            return $site_ids;
        }
        
        // if we're here, something was selected
        foreach ($chosen as $site_id) {
            // just add regular ids to the list
            if (is_numeric($site_id)) {
                if ($sortedByType) {
                    $site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);
                    $site_ids[$site->type][] = $site_id;
                } else {
                    $site_ids[] = $site_id;
                }
            }
            
            // for the "all" options, get ALL sites (even inactive ones) by program and type
            else {
                $option = explode("-", $site_id);
                $type = $option[1];
                $all_sites = $this->getSitesByProgram($program_id, $type, null, null, null);
                foreach ($all_sites as $site) {
                    if ($sortedByType) {
                        $site_ids[$site['type']][] = $site['id'];
                    } else {
                        $site_ids[] = $site['id'];
                    }
                }
            }
        }
        return $site_ids;
    }
}
