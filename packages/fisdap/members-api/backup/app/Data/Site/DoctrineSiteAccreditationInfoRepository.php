<?php namespace Fisdap\Data\Site;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineSiteAccreditationInfoRepository
 *
 * @package Fisdap\Data\Site
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineSiteAccreditationInfoRepository extends DoctrineRepository implements SiteAccreditationInfoRepository
{
    public function getInfo($site_id, $program_id)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select('accreditation_info')
            ->from('\Fisdap\Entity\SiteAccreditationInfo', 'accreditation_info')
            ->join('accreditation_info.program_site_association', 'psa')
            ->where('psa.site = ?1')
            ->andWhere('psa.program = ?2')
            ->setParameter(1, $site_id)
            ->setParameter(2, $program_id);
            
        $res = $qb->getQuery()->getResult();
        
        if ($res) {
            return $res[0];
        }
        
        return false;
    }
    
    public function getStudentSupervisionTypeFormOptions($use_description = true, $clinical = false)
    {
        $types = $this->getAllStudentSupervisionTypes();
        $form_options = array();
        
        foreach ($types as $type) {
            $description = "Program personnel";
            if ($type->id == 2) {
                $description = ($clinical) ? "Hospital personnel" : "Field agency personnel";
            }
            $form_options[$type->id] = ($use_description) ? $description : $type->name;
        }
        
        krsort($form_options);
        return $form_options;
    }
    
    public function getAllStudentSupervisionTypes()
    {
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select('type')
            ->from('\Fisdap\Entity\StudentSupervisionType', 'type');
        
        return $qb->getQuery()->getResult();
    }
}
