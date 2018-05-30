<?php namespace Fisdap\Data\Base;

use Fisdap\Data\Repository\DoctrineRepository;

class DoctrineBaseLegacyRepository extends DoctrineRepository implements BaseLegacyRepository
{
    public function getBaseAssociationsByProgram($siteId, $programId, $active = true)
    {
        $qb = $this->_em->createQueryBuilder();

        // first get the associations just for this program
        $qb->select('b, p')
            ->from('\Fisdap\Entity\ProgramBaseLegacy', 'p')
            ->leftJoin('p.base', 'b')
            ->where('p.program = ?1')
            ->andWhere('b.site = ?2')
            ->setParameter(1, $programId)
            ->setParameter(2, $siteId);
        if ($active) {
            $qb->andWhere('p.active = true');
        }
        $results = $qb->getQuery()->getResult();

        if ($active) {
            return $results;
        }

        // for inactive associations, we need to consider possible shared bases, too

        // organize the results for this program
        $active = array();
        $inactive = array();
        foreach ($results as $association) {
            if ($association->active) {
                $active[$association->base->id] = $association;
            } else {
                $inactive[$association->base->id] = $association;
            }
        }

        $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $programId);
        // if this program is sharing this site, look for shared bases
        if ($program->sharesSite($siteId)) {
            $site = \Fisdap\EntityUtils::getEntity('SiteLegacy', $siteId);
            $sharing_programs = array();
            foreach ($site->site_shares as $share) {
                if ($share->approved) {
                    $sharing_programs[] = "p.program = ".$share->program->id;
                }
            }
            $program_clause = implode(" OR ", $sharing_programs);
            $qb = $this->_em->createQueryBuilder();
            $qb->select('b, p')
                ->from('\Fisdap\Entity\ProgramBaseLegacy', 'p')
                ->leftJoin('p.base', 'b')
                ->where($program_clause)
                ->andWhere('b.site = ?1')
                ->setParameter(1, $siteId);
            $shared_associations = $qb->getQuery()->getResult();

            foreach ($shared_associations as $association) {
                // if this isn't an active association for this program, add it to the inactive list
                if (!array_key_exists($association->base->id, $active)) {
                    // key by base id so we don't get repeats
                    $inactive[$association->base->id] = $association;
                }
            }
            return $inactive;
        } else {
            return $inactive;
        }
    }

    public function getBasesByProgram($programId, $active = true)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('b')
            ->from('\Fisdap\Entity\BaseLegacy', 'b')
            ->where('b.program = ?1');
    }

    public function getBaseCount($siteId, $programId)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('count(b.id)')
            ->from('\Fisdap\Entity\ProgramBaseLegacy', 'p')
            ->leftJoin('p.base', 'b')
            ->leftJoin('b.site', 's')
            ->leftJoin('s.program_site_associations', 'psa')
            ->where('p.program = ?1')
            ->andWhere('psa.program = ?1')
            ->andWhere('psa.active = 1')
            ->andWhere('p.active = 1')
            ->setParameter(1, $programId);

        if ($siteId != "all") {
            if ($siteId) {
                //Temp array to store possible site types
                $types = array();
                foreach ($siteId as $i => $site) {
                    switch ($site) {
                        case "0-Field":
                            $types[] = "field";
                            unset($siteId[$i]);
                            break;
                        case "0-Clinical":
                            $types[] = "clinical";
                            unset($siteId[$i]);
                            break;
                        case "0-Lab":
                            $types[] = "lab";
                            unset($siteId[$i]);
                            break;
                    }
                }

                if (is_array($siteId) && count($siteId) > 0) {
                    $siteAnd = $qb->expr()->in('b.site', $siteId);
                }

                if (count($types) > 0) {
                    $typeAnd = $qb->expr()->in('s.type', $types);
                }

                $qb->andWhere($qb->expr()->orX($siteAnd, $typeAnd));
            }
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getFormOptionsByProgram($programId, $active = true, $type = null, $site = null, $all = null)
    {
        $associations = $this->getBaseAssociationsByProgramOptimized($site, $programId, $active, $type, $all);
        $bases = array();
        foreach ($associations as $association) {
            $base = $association['base'];

            if (is_null($site)) {
                if (!$bases[$base['site']['id']]) {
                    $bases[$base['site']['id']] = array();
                }
                $bases[$base['site']['id']][$base['id']] = $this->convertDefaultDepartment($base['name']);
            } else {
                $bases[$base['id']] =  $this->convertDefaultDepartment($base['name']);
            }
        }

        return $bases;
    }

    public function getFormOptionsByIds($base_ids = array())
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('partial b.{id,name}, partial s.{id,name}')
            ->from('\Fisdap\Entity\BaseLegacy', 'b')
            ->leftJoin('b.site', 's')
            ->add('where', $qb->expr()->in('b.id', '?1'))
            ->setParameter(1, $base_ids)
            ->orderBy('s.name, b.name');

        $raw_bases = $qb->getQuery()->getArrayResult();

        $bases = array();
        foreach ($raw_bases as $raw_base) {
            $bases[$raw_base['site']['name']][$raw_base['id']] =  $this->convertDefaultDepartment($raw_base['name']);
        }

        return $bases;
    }

    private function getDefaults()
    {
        return array(
            "Anesthesia"	=> "Anesthesia",
            "Burn" 			=> "Burn Unit",
            "CCL" 			=> "Cardiac Cath. Lab",
            "CCU" 			=> "Cardiac Care Unit",
            "Clinic"		=> "Clinic",
            "ER" 			=> "ER",
            "ICU"			=> "ICU",
            "IVTeam"		=> "IV Team",
            "Labor"			=> "Labor & Delivery",
            "NICU"			=> "Neonatal ICU",
            "OR"			=> "OR",
            "PostOp"		=> "Post Op",
            "PreOp"			=> "Pre Op",
            "Psych"			=> "Psychiatric Unit",
            "Respiratory"	=> "Respiratory Therapy",
            "Triage"		=> "Triage",
            "Urgent"		=> "Urgent Care",
            "Pediatrics"    => "Pediatrics",
        );
    }

    public function isDefault($name)
    {
        $defaults = $this->getDefaults();
        if (in_array($name, $defaults) || array_key_exists($name, $defaults)) {
            return true;
        }

        return false;
    }

    private function convertDefaultDepartment($name, $false_if_not_found = false)
    {
        $defaults = $this->getDefaults();

        if ($false_if_not_found) {
            return ($defaults[$name]) ? $defaults[$name] : false;
        }

        return ($defaults[$name]) ? $defaults[$name] : $name;
    }

    public function mergeBases($target_base_id, $merge_base_id)
    {
        $targetBase = \Fisdap\EntityUtils::getEntity("BaseLegacy", $target_base_id);
        $oldBase = \Fisdap\EntityUtils::getEntity("BaseLegacy", $merge_base_id);

        //update the base associations for all programs associated with these bases
        foreach ($oldBase->program_base_associations as $association) {
            $program = $association->program;
            $program->removeBase($oldBase);
            $program->addBase($targetBase);
        }

        // update event data
        $this->mergeEventData($targetBase, $oldBase);
        $this->mergeRepeatInfo($targetBase, $oldBase);

        // update shift data
        $this->mergeShiftData($targetBase, $oldBase);

        // update scheduler auto emails
        $this->mergeAutoEmails($targetBase, $oldBase);

        // delete old base
        $oldBase->delete(false);
    }



    public function mergeEventData($targetBase, $oldBase)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->update('\Fisdap\Entity\EventLegacy', 'e')
            ->set('e.base', '?1')
            ->where('e.base = ?2')
            ->setParameter(1, $targetBase)
            ->setParameter(2, $oldBase->id);

        return $qb->getQuery()->execute();
    }

    public function mergeRepeatInfo($targetBase, $oldBase)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->update('\Fisdap\Entity\RepeatInfo', 'r')
            ->set('r.base', '?1')
            ->where('r.base = ?2')
            ->setParameter(1, $targetBase)
            ->setParameter(2, $oldBase->id);

        return $qb->getQuery()->execute();
    }

    public function mergeShiftData($targetBase, $oldBase)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->update('\Fisdap\Entity\ShiftLegacy', 's')
            ->set('s.base', '?1')
            ->where('s.base = ?2')
            ->setParameter(1, $targetBase)
            ->setParameter(2, $oldBase->id);

        return $qb->getQuery()->execute();
    }

    public function mergeAutoEmails($targetBase, $oldBase)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->update('\Fisdap\Entity\ScheduleEmail', 'e')
            ->set('e.base_id', '?1')
            ->where('e.base_id = ?2')
            ->setParameter(1, $targetBase->id)
            ->setParameter(2, $oldBase->id);

        return $qb->getQuery()->execute();
    }

    public function getAccordionData($site_id, $program_id, $network_program_ids)
    {
        if (!$network_program_ids) {
            $network_program_ids = $program_id;
        }

        $data = $this->getBaseAssociationsByProgramOptimized($site_id, $network_program_ids, null, null, true);

        $return_data = array();
        $return_data['current_program'] = array();
        $return_data['other_programs'] = array();
        $default_departments_found = array();
        $current_program_ids = array();
        $other_program_ids = array();

        if ($data) {
            foreach ($data as $pb) {
                $default_department = $this->convertDefaultDepartment($pb['base']['name'], true);
                $base_name = ($default_department === false) ? $pb['base']['name'] : $default_department;
                $is_default = ($default_department === false) ? false : true;

                if ($is_default) {
                    $default_departments_found[$pb['base']['name']] = $default_department;
                }

                $base_program_id = $pb['program']['id'];
                $active = ($program_id == $base_program_id) ? $pb['active'] : false;
                $key = $this->getBaseOrderKey($active, $base_name, $pb['base']['id']);

                $data_array = array("active" => $active,
                                    "id" => $pb['base']['id'],
                                    "name" => $base_name,
                                    "is_default" => $is_default,
                                    "address" => $pb['base']['address'],
                                    "city" => $pb['base']['city'],
                                    "state" => $pb['base']['state'],
                                    "zip" => $pb['base']['zip']);

                if ($program_id == $base_program_id) {
                    $current_program_ids[] = $pb['base']['id'];

                    $return_data['current_program'][$key] = $data_array;
                    unset($return_data['other_programs'][$this->getBaseOrderKey(false, $base_name, $pb['base']['id'])]);
                } elseif (!in_array($pb['base']['id'], $current_program_ids)) {
                    $other_program_ids[] = $pb['base']['id'];
                    $return_data['other_programs'][$key] = $data_array;
                }
            }
        }

        $defaults_not_used = array_diff($this->getDefaults(), $default_departments_found);
        return array("results" => $return_data, "defaults_not_found" => $defaults_not_used);
    }

    public function getBaseOrderKey($active, $name, $id)
    {
        $base_name = strtolower(preg_replace('/[^\w]+/', '', $name));
        $active_order = ($active) ? "a" : "b";
        return $active_order . "_" . $base_name . "_" . $id;
    }

    public function getBaseIdsByProgramAndSites($site_ids, $program_id, $active = true)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('partial b.{id}, partial s.{id}, partial p.{id}, partial psa.{id}, partial pro.{id}')
            ->from('\Fisdap\Entity\ProgramBaseLegacy', 'p')
            ->leftJoin('p.base', 'b')
            ->leftJoin('b.site', 's')
            ->leftJoin('s.program_site_associations', 'psa')
            ->leftJoin('p.program', 'pro')
            ->where('s.id IN (' . implode($site_ids, ",") . ')')
            ->andWhere('pro.id = ?1');

        if ($active) {
            $qb->andWhere('p.active = true');
        }


        $qb->setParameter(1, $program_id);

        $raw_bases = $qb->getQuery()->getArrayResult();

        $bases = array();
        foreach ($raw_bases as $raw_base) {
            $bases[] =  $raw_base['base']['id'];
        }

        return $bases;
    }

    public function getBaseAssociationsByProgramOptimized($siteId = null, $programId, $active = true, $type = null, $all = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('partial b.{id,name,abbreviation,address,city,state,zip,ip_address,type}, partial s.{id,name}, partial p.{id,active}, partial psa.{id}, partial pro.{id}')
            ->from('\Fisdap\Entity\ProgramBaseLegacy', 'p')
            ->leftJoin('p.base', 'b')
            ->leftJoin('b.site', 's')
            ->leftJoin('s.program_site_associations', 'psa')
            ->leftJoin('p.program', 'pro');


        if (is_array($programId)) {
            $qb->where('pro.id IN (' . implode($programId, ",") . ')');
        } else {
            $qb->where('pro.id = ?1');
            $qb->setParameter(1, $programId);
        }

        if ($siteId) {
            $qb->andWhere('b.site = ?2');
        }

        if ($type) {
            $qb->andWhere('b.type = ?3');
        }


        if ($siteId) {
            $qb->setParameter(2, $siteId);
        }
        if ($type) {
            $qb->setParameter(3, $type);
        }

        if (!$all) {
            if ($active) {
                $qb->andWhere('p.active = true');
                $qb->andWhere('psa.active = true');
            } else {
                $qb->andWhere('p.active = false');
            }
        }

        $qb->orderBy('s.name, b.name');

        return $qb->getQuery()->getArrayResult();
    }

    public function getBaseAddresses($program_id, $base_ids = [])
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select("b.id, b.address as base_address, b.state as base_state, b.city as base_city, b.zip as base_zip, s.address as site_address, s.city as site_city, s.state as site_state, s.zipcode as site_zip")
            ->from('\Fisdap\Entity\BaseLegacy', 'b')
            ->join('b.site', 's')
            ->join('b.program_base_associations', 'pba')
            ->andWhere('pba.program = ?1')
            ->setParameter(1, $program_id);

        if (!empty($base_ids)) {
            $qb->where($qb->expr()->in('b.id', $base_ids));
        }

        $baseAddresses = $qb->getQuery()->getArrayResult();

        //Now get program info
        $qb = $this->_em->createQueryBuilder();
        $qb->select("p.city as program_city, p.state as program_state, p.zip as program_zip")
            ->from('\Fisdap\Entity\ProgramLegacy', 'p')
            ->where("p.id = ?1")
            ->setParameter(1, $program_id);
        $program = $qb->getQuery()->getSingleResult();

        $addresses = array();
        foreach ($baseAddresses as $baseAddress) {
            $addressPieces = array();

            //address
            if ($baseAddress['base_address']) {
                $addressPieces[] = $baseAddress['base_address'];
            } elseif ($baseAddress['site_address']) {
                $addressPieces[] = $baseAddress['site_address'];
            }

            //city
            if ($baseAddress['base_city']) {
                $addressPieces[] = $baseAddress['base_city'];
            } elseif ($baseAddress['site_city']) {
                $addressPieces[] = $baseAddress['site_city'];
            } else {
                $addressPieces[] = $program['program_city'];
            }

            //state
            if ($baseAddress['base_state']) {
                $addressPieces[] = $baseAddress['base_state'];
            } elseif ($baseAddress['site_state']) {
                $addressPieces[] = $baseAddress['site_state'];
            } else {
                $addressPieces[] = $program['program_state'];
            }

            //zip
            if ($baseAddress['base_zip']) {
                $addressPieces[] = $baseAddress['base_zip'];
            } elseif ($baseAddress['site_zip']) {
                $addressPieces[] = $baseAddress['site_zip'];
            }

            $addresses[$baseAddress['id']] = implode(", ", $addressPieces);
        }

        return $addresses;
    }
}
