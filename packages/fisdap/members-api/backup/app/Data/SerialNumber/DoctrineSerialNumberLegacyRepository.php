<?php namespace Fisdap\Data\SerialNumber;

use Fisdap\Data\Repository\DoctrineRepository;
use Fisdap\Entity\SerialNumberLegacy;

/**
 * Class DoctrineSerialNumberLegacyRepository
 *
 * @package Fisdap\Data\SerialNumber
 */
class DoctrineSerialNumberLegacyRepository extends DoctrineRepository implements SerialNumberLegacyRepository
{
    public $activationCodes;


    /**
     * @param string $number
     *
     * @return SerialNumberLegacy|null
     */
    public function getOneByNumber($number)
    {
        return $this->findOneBy(['number' => $number]);
    }


    public function getSerialNumbers($filters = array())
    {
        //return $this->getStatusQuery($filters['status']);
        
        $qb = $this->_em->createQueryBuilder();
        
        // searching for a specific code will trump all other filters
        $this->activationCodes = ($filters['codes']) ? $filters['codes']: false;

        // including preceptor training will allow users to find 'instructor' certification levels (just behind the scences)
        if (($filters['productConfig'] & 64)) {
            $filters['certLevels'][] = 'instructor';
        }
        
        $programId = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();
        $program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $programId);
        $qb->select('sn.number, sn.configuration, sn.account_type, sn.order_date, o.id, sn.activation_date, c.description, sn.graduation_date, g.name, sn.distribution_date, sn.distribution_email, u.first_name, u.last_name')
            ->from('\Fisdap\Entity\SerialNumberLegacy', 'sn')
            ->leftJoin('sn.certification_level', 'c')
            ->leftJoin('sn.group', 'g')
            ->leftJoin('sn.user', 'u')
            ->leftJoin('sn.order', 'o')
            ->where('sn.program = ?1')
            ->andWhere('sn.order IS NOT NULL')
            ->andWhere('o.individual_purchase != 1')
            ->andWhere($this->getActivationCodeQuery($filters['code']))
            ->andWhere($this->getOrderDateQuery($filters['dateBegin'], $filters['dateEnd']))
            ->andWhere($this->getGradDateQuery($filters['gradYear'], $filters['gradMonth']))
            ->andWhere($this->getGroupQuery($filters['section']))
            ->andWhere($this->getCertLevelsQuery($filters['certLevels']))
            ->andWhere($this->getStatusQuery($filters['status']))
            ->setParameter(1, $program);
        return $qb->getQuery()->getResult();
    }
    
    private function getActivationCodeQuery($code)
    {
        if ($this->activationCodes) {
            $codesQuery = "sn.number = ";
            $codeCounter = 0;
            foreach ($this->activationCodes as $code) {
                if ($codeCounter != 0) {
                    $codesQuery .= " OR sn.number = ";
                }
                $codesQuery .= "'" . $code . "'";
                $codeCounter++;
            }
            return $codesQuery;
        } else {
            return null;
        }
    }
    
    private function getOrderDateQuery($dateBegin, $dateEnd)
    {
        if (!$this->activationCodes) {
            $beginSqlDate = $this->getSqlDate($dateBegin);
            $endSqlDate = $this->getSqlDate($dateEnd);
            return "sn.order_date >= '" . $beginSqlDate . "' AND sn.order_date <= '" . $endSqlDate . "'";
        } else {
            return null;
        }
    }
    
    private function getSqlDate($date)
    {
        $year = substr($date, -4, 4);
        $month = substr($date, 0, 2);
        $day = substr($date, 3, 2);
        
        return $year . "-" . $month . "-" . $day . " 00:00:00";
    }
    
    private function getGradDateQuery($year, $month)
    {
        $gradQuery = "";

        if (!$this->activationCodes) {
            if ($year) {
                if (!$month) {
                    $afterDate = ((int)$year + 1) . '-01-01';
                    $beforeDate = ((int)$year - 1) . '-12-31';
                } else {
                    if ($month == 12) {
                        $monthAfter = 1;
                        $yearAfter = ((int)$year)+1;
                    } else {
                        $monthAfter = $month+1;
                        $yearAfter = $year;
                    }
                    
                    if ($month == 1) {
                        $monthBefore = 12;
                        $yearBefore = ((int)$year)-1;
                    } else {
                        $monthBefore = $month-1;
                        $yearBefore = $year;
                    }
                    
                    $afterDate = $yearAfter . '-' . ((int)$monthAfter) . '-01';
                    $beforeDate = $yearBefore . '-' . ((int)$monthBefore) . '-31';
                }
                
                $gradQuery = "sn.graduation_date < '" . $afterDate . "' AND sn.graduation_date > '" . $beforeDate . "'";
            }
        }
        
        return ($gradQuery == "") ? null : $gradQuery;
    }
    
    private function getCertLevelsQuery($levels)
    {
        if (!$this->activationCodes) {
            $certLevels = "c.name =";
            $count = 0;
            if ($levels) {
                foreach ($levels as $level) {
                    if ($count == 0) {
                        $certLevels .= " '" . $level . "'";
                    } else {
                        $certLevels .= " OR c.name = '" . $level . "'";
                    }
                    $count++;
                }
            } else {
                $certLevels .= "'NONE'";
            }
            
            // for the sake of legacy data
            if ($levels) {
                foreach ($levels as $level) {
                    $certLevels .= " OR sn.account_type = '" . $level . "'";
                }
            }
            
            return $certLevels;
        } else {
            return null;
        }
    }
    
    private function getGroupQuery($group)
    {
        if (!$this->activationCodes) {
            return ($group) ? 'g.id = ' . $group : null;
        } else {
            return null;
        }
    }
    
    private function getStatusQuery($filter)
    {
        if (!$this->activationCodes) {
            $statusQueries = array();
            
            // loop through the filters and add the appropriate queries
            foreach ($filter as $type => $checked) {
                // add the query if the filter is checked
                $activated = "(sn.activation_date is not null OR sn.user is not null)";
                if ($checked == 1) {
                    switch ($type) {
                        case "distributed":
                            $statusQueries[] = "(sn.distribution_date is not null ".
                                "AND NOT $activated)";
                            break;
                        case "activated":
                            $statusQueries[] = $activated;
                            break;
                        case "available":
                            $statusQueries[] =
                                "(sn.distribution_date is null ".
                                "AND NOT $activated)";
                            break;
                    }
                }
            }
        }

        if (count($statusQueries) == 0) {
            return null;
        }
        
        return "(" . implode(' OR ', $statusQueries) . ")";
    }
    
    private function getDistributedQuery($status)
    {
        $distributed = "";
        if (!$this->activationCodes) {
            $distributed .= 'sn.distribution_date is';
            $distributed .= ($status != 0) ? ' not null': " null";
        }
        return ($distributed == "") ? null : $distributed;
    }
    
    private function getActivatedQuery($status)
    {
        $activated = "";
        if (!$this->activationCodes) {
            $activated .= 'sn.activation_date ';
            $activated .= ($status != 0) ? ' !': " ";
            $activated .= "= '0000-00-00 00:00:00'";
        }
        return ($activated == "") ? null : $activated;
    }
    
    public function getByStudent($studentId)
    {
        $qb = $this->_em->createQueryBuilder();

        $student = \Fisdap\EntityUtils::getEntity("StudentLegacy", $studentId);
        
        $qb->select('sn')
           ->from('\Fisdap\Entity\SerialNumberLegacy', 'sn')
           ->where('sn.student_id = ?1')
           ->setParameter(1, $student);
        
        return $qb->getQuery()->getResult();
    }
}
