<?php namespace Fisdap\Data\Objective;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineObjectiveLegacyRepository
 *
 * @package Fisdap\Data\Objective
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineObjectiveLegacyRepository extends DoctrineRepository implements ObjectiveLegacyRepository
{
    public function getFormOptions($na = false, $sort=true)
    {
        $options = array();
        $results = $this->findAll();
    
        foreach ($results as $result) {
            if ($result->id != -1) {
                $tempOptions[$result->id] = $result->description;
            }
        }
    
        if ($sort) {
            asort($tempOptions);
        }
    
        if ($na) {
            $options[0] = "N/A";
            foreach ($tempOptions as $id => $name) {
                $options[$id] = $name;
            }
        } else {
            $options = $tempOptions;
        }
    
        return $options;
    }
}
