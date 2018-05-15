<?php namespace Fisdap\Data\Preceptor;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;
use Illuminate\Auth\AuthManager;

/**
 * Class DoctrinePreceptorLegacyRepository
 *
 * @package Fisdap\Data\Preceptor
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrinePreceptorLegacyRepository extends DoctrineRepository implements PreceptorLegacyRepository
{
    public function getAllStudentsByProgram($programID)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select('st, usr')
           ->from('\Fisdap\Entity\StudentLegacy', 'st')
           ->leftJoin('st.user', 'usr')
           ->where('st.program = ?1')
           ->orderBy('usr.last_name', 'ASC')
           ->orderBy('usr.first_name', 'ASC')
           ->setParameter(1, $programID);
        
        return $qb->getQuery()->getResult();
    }
    
    public function getPreceptors($programId, $siteId, $active = true, $active_matters = true)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('pre')
           ->from('\Fisdap\Entity\PreceptorLegacy', 'pre')
           ->innerJoin('pre.site', 'site')
           ->leftJoin('pre.program_preceptor_associations', 'a')
           ->leftJoin('a.program', 'program')
           ->where('program.id = ?1')
           ->andWhere('site.id = ?2')
           ->orderBy('pre.last_name, pre.first_name', 'ASC')
           ->setParameter(1, $programId)
           ->setParameter(2, $siteId);
        
        if ($active_matters) {
            if ($active) {
                $qb->andWhere('a.active = 1');
            } else {
                $qb->andWhere('a.active = 0');
            }
        }
        
        return $qb->getQuery()->getResult();
    }

    public function getPreceptorsBySite($siteId, AuthManager $auth)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('preceptor')
            ->from('\Fisdap\Entity\PreceptorLegacy', 'preceptor')
            ->leftJoin('preceptor.program_preceptor_associations', 'ppa')
            ->andWhere('preceptor.site = ?1')
            ->andWhere('ppa.program = ?2')
            ->orderBy('preceptor.last_name, preceptor.first_name', 'ASC')
            ->setParameter(1, $siteId)
            ->setParameter(2, $auth->guard()->user()->context()->getProgram()->getId());

        $logger = \Zend_Registry::get('logger');
        $logger->debug($qb->getQuery()->getSQL());


        return $qb->getQuery()->getResult();
    }
    
    public function getPreceptorsOptimized($programId, $active = true, $siteId = null, $active_matters = true, $site_active = false)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select('partial pre.{id,first_name,last_name,home_phone,work_phone,pager,email}, partial a.{id,active}, partial program.{id}, partial s.{id,name}')
           ->from('\Fisdap\Entity\PreceptorLegacy', 'pre')
           ->leftJoin('pre.site', 's')
           ->leftJoin('pre.program_preceptor_associations', 'a')
           ->leftJoin('a.program', 'program')
           ->where('program.id = ?1')
           ->orderBy('pre.last_name, pre.first_name', 'ASC')
           ->setParameter(1, $programId);
        
        if ($active_matters) {
            if ($active) {
                $qb->andWhere('a.active = 1');
            } else {
                $qb->andWhere('a.active = 0');
            }
        }
        
        if (!is_null($siteId)) {
            $qb->andWhere('s.id = ?2');
            $qb->setParameter(2, $siteId);
        }
        //only return preceptor if site is active for the given $programId
        if ($site_active) {
            $qb->leftJoin('s.program_site_associations', 'psa')
               ->andWhere('psa.active = 1')
               ->andWhere('psa.program = ?1');
        }
        
        $qb->orderBy('s.name, pre.last_name, pre.first_name');
        
        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Get an array of nicely formatted preceptor data for use in the preceptor accordion in site admin
     * @param $program_id int
     * @param $site_id int
     * @return array
     */
    public function getAccordionData($program_id, $site_id)
    {
        $data = $this->getPreceptorsOptimized($program_id, null, $site_id, false);
        $return_data = array();

        if ($data) {
            foreach ($data as $preceptor) {
                $name = $preceptor['first_name'] . " " . $preceptor['last_name'];
                $order_name = $preceptor['last_name'] . " " . $preceptor['first_name'];
                $unformatted_name = strtolower(preg_replace('/[^\w]+/', '', $order_name));
                $active =  $preceptor['program_preceptor_associations'][0]['active'];
                $active_order = ($active) ? "a" : "b";
                $key = $active_order . "_" . $unformatted_name . "_" . $preceptor['id'];
                
                $return_data[$key] = array("active" => $active,
                                                "id" => $preceptor['id'],
                                                "name" => $name,
                                                "home_phone" => $preceptor['home_phone'],
                                                "work_phone" => $preceptor['work_phone'],
                                                "pager" => $preceptor['pager'],
                                                "email" => $preceptor['email']);
            }
        }
        
        return $return_data;
    }
    
    public function getPreceptorFormOptions($programId, $siteId = null, $active = true)
    {
        $preceptors = $this->getPreceptorsOptimized($programId, $active, $siteId, true, true);
        $options = array();
        foreach ($preceptors as $preceptor) {
            if (is_null($siteId)) {
                if (!$options[$preceptor['site']['name']]) {
                    $options[$preceptor['site']['name']] = array();
                }
                $options[$preceptor['site']['name']][$preceptor['id']] = $preceptor['first_name'] . " " . $preceptor['last_name'];
            } else {
                $options[$preceptor['id']] = $preceptor['first_name'] . " " . $preceptor['last_name'];
            }
        }
        return $options;
    }
    
    public function getFormOptionsByIds($preceptor_ids)
    {
        $qb = $this->_em->createQueryBuilder();
        $raw_preceptors = array();
        
        $qb->select('partial pre.{id,first_name,last_name}, partial s.{id,name}')
            ->from('\Fisdap\Entity\PreceptorLegacy', 'pre')
            ->leftJoin('pre.site', 's')
            ->add('where', $qb->expr()->in('pre.id', '?1'))
            ->setParameter(1, $preceptor_ids)
            ->orderBy('pre.last_name, pre.first_name');

        $raw_preceptors = $qb->getQuery()->getArrayResult();
        
        $preceptors = array();
        foreach ($raw_preceptors as $raw_preceptor) {
            $preceptors[$raw_preceptor['site']['name']][$raw_preceptor['id']] =  $raw_preceptor['first_name'] . " " . $raw_preceptor['last_name'];
        }

        return $preceptors;
    }
    
    public function getPreceptorPatients($preceptorId)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select('patient')
           ->from('\Fisdap\Entity\Patient', 'patient')
           ->where('patient.preceptor = ?1')
           ->setParameter(1, $preceptorId);

        return $qb->getQuery()->getResult();
    }
    
    public function mergeEventPreceptors($new, $old)
    {
        $sql = "UPDATE EventPreceptorData SET Preceptor_id = " . $new . " WHERE Preceptor_id = " . $old . "";
        $conn = $this->_em->getConnection();
        return $conn->query($sql);
    }
}
