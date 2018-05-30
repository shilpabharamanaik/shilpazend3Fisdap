<?php namespace Fisdap\Data\Scenario;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineScenarioRepository
 *
 * @package Fisdap\Data\Scenario
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineScenarioRepository extends DoctrineRepository implements ScenarioRepository
{
    public function getAllScenarioAuthors()
    {
        $conn = $this->_em->getConnection();
        
        $query = "SELECT u.id, u.first_name, u.last_name FROM fisdap2_scenarios s INNER JOIN fisdap2_users u ON u.id = s.user_id GROUP BY u.id ORDER BY u.last_name, u.first_name";
        
        $res = $conn->query($query);
        
        $authors = array();
        
        while ($row = $res->fetch()) {
            $authors[$row['id']] = $row['last_name'] . ", " . $row['first_name'];
        }
        
        return $authors;
    }
}
