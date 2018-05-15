<?php namespace Fisdap\Data\MoodleTest;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineMoodleTestDataLegacyRepository
 *
 * @package   Fisdap\Data\MoodleTest
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineMoodleTestDataLegacyRepository extends DoctrineRepository implements MoodleTestDataLegacyRepository
{
    /*
     * Gets a list of all currently available Moodle tests (MoodleTestDataLegacy)
     * as an array of moodle_quiz_id => test_name
     * criteria array can include:
     *   'active' => integer (set to 1 by default) or array of integers
     *   'context' => secure_testing | study_tools | pilot_testing
     *   'extraGroups' => Array containing the following strings:
     *   	'pilot_tests' | 'retired' | 'chum_bucket'
     * returnType = 'array' | 'entity' | 'productArray'
     * sort = array('field' => 'ASC|DESC')
     */
    public function getMoodleTestList(
        $criteria = array('active' => 1, 'extraGroups' => array('pilot_tests', 'retired')),
        $returnType = 'array',
        $sort = array()
    ) {
        if (!is_array($criteria['extraGroups'])) {
            $criteria['extraGroups'] = array();
        }

        $qb = $this->_em->createQueryBuilder();

        $qb->select('m')
            ->from('\Fisdap\Entity\MoodleTestDataLegacy', 'm')
            ->orderBy('m.test_name');

        // add Active criteria
        if (is_numeric($criteria['active'])) {
            $qb->andWhere('m.active = ?1');
            $qb->setParameter(1, $criteria['active']);
        } elseif (is_array($criteria['active'])) {
            $qb->andWhere('m.active IN (?1)');
            $qb->setParameter(1, $criteria['active']);
        }

        // add Context criteria
        $contextsToDatabase = array(
            'secure_testing' => 'fis_moodle',
            'study_tools'    => 'review_moodle',
            'pilot_testing'  => 'pt_moodle',
        );
        if (isset($criteria['context']) && ($database = $contextsToDatabase[$criteria['context']])) {
            $qb->andWhere('m.moodle_database = ?2');
            $qb->setParameter(2, $database);
        }

        // Sort if requested
        foreach ($sort as $field => $direction) {
            $qb->orderBy('m.' . $field, $direction);
        }

        $results = $qb->getQuery()->getResult();

        if ($returnType == 'array') {
            // return a flat array of id => test name
            foreach ($results as $r) {
                $returnData[$r->moodle_quiz_id] = $r->test_name;
            }

            return $returnData;
        } else {
            if ($returnType == 'entity') {
                // return an unkeyed array of entities
                return $results;
            } else {
                if ($returnType == 'productArray') {
                    return $this->sortTestsByProduct($results, $criteria);
                } else {
                    if ($returnType == 'productArrayWithInfo') {
                        $productArray = $this->sortTestsByProduct($results, $criteria);

                        $infoArray = array();
                        foreach ($results as $test) {
                            $infoArray[$test->moodle_quiz_id] = $test;
                        }

                        return array(
                            'product' => $productArray,
                            'info'    => $infoArray,
                        );
                    }
                }
            }
        }
    }

    /**
     * This function takes an array of MoodleTestDatas and organizes them into a 2d array
     * of product names => array(moodle id => test name).  Can also account for extra groups
     * that can be passed in through the criteria array.  See the documentation for
     * MoodleTestDataLegacyRepository::getMoodleTestList() for more details.
     *
     * @param array $tests Array containing MoodleTestDataLegacy objects.
     */
    public function sortTestsByProduct($tests, $criteria)
    {
        $retiredTests = array();
        $pilotTests = array();
        $chumBucket = array();

        foreach ($tests as $test) {
            if (count($test->products) > 0) {
                foreach ($test->products as $product) {
                    $returnData[$product->name][$test->moodle_quiz_id] = $test->test_name;
                }

                // This should be only pilot tests...
            } elseif (isset($criteria['extraGroups']) && in_array(
                    'pilot_tests',
                    $criteria['extraGroups']
                ) && $test->active == 1
            ) {
                $pilotTests[$test->moodle_quiz_id] = $test->test_name;
            } elseif ($test->active == 3) {
                $retiredTests[$test->moodle_quiz_id] = $test->test_name;
            } else {
                $chumBucket[$test->moodle_quiz_id] = $test->test_name;
            }
        }

        if ($returnData) {
            asort($returnData);
        }

        if (count($pilotTests) > 0 && (isset($criteria['extraGroups']) && in_array(
                    'pilot_tests',
                    $criteria['extraGroups']
                ))
        ) {
            $returnData['Pilot Exams'] = $pilotTests;
        }

        if (count($retiredTests) > 0 && (isset($criteria['extraGroups']) && in_array(
                    'retired',
                    $criteria['extraGroups']
                ))
        ) {
            $returnData['Retired Exams'] = $retiredTests;
        }

        // Keeping this around- useful if you need to see what tests haven't matched any of the above groupings.
        if (count($chumBucket) > 0 && (isset($criteria['extraGroups']) && in_array(
                    'chum_bucket',
                    $criteria['extraGroups']
                ))
        ) {
            $returnData['Other'] = $chumBucket;
        }

        return $returnData;
    }

    /*
     * This function takes a moodle quiz ID and returns an array of moodle question IDs
     * for that quiz, along with matching test blueprint ids
     */
    public function getItemIDSectionMapArray($moodleQuizId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select(
            'imm.moodle_id, imm.item_id as fisdap_item_id, tbs.id, tbs.name, tbs.score_display, tbs.grade_scale, tbc.name as column'
        )
            ->from('\Fisdap\Entity\MoodleTestDataLegacy', 'mtd')
            ->innerJoin('mtd.blueprint', 'tbp')
            ->innerJoin('tbp.items', 'tbi')
            ->innerJoin('tbi.column', 'tbc')
            ->innerJoin('tbi.moodle_map', 'imm')
            ->innerJoin('tbi.topic', 'tbt')
            ->innerJoin('tbt.section', 'tbs')
            ->where('mtd.moodle_quiz_id = ?1')
            ->andWhere('(imm.moodle_quiz_id + mtd.moodle_id_modifier) = mtd.moodle_quiz_id')
            ->groupBy('tbi.id')
//		->groupBy('imm.moodle_id')
            ->orderBy('imm.moodle_id');

        $qb->setParameter(1, $moodleQuizId);

        $sql = $qb->getQuery()->getSQL();
        $results = $qb->getQuery()->getResult();

        $resArray = array();

        foreach ($results as $res) {
            // These two columns shouldn't ever show up in score retrieval mode.
            if (!($res['name'] == 'Affective' || $res['name'] == 'Affective Domain' || $res['name'] == 'Demographics' || $res['name'] == "Empathy")) {
                $resArray['questions'][$res['moodle_id']] = $res['name'];
                $resArray['questions_kap'][$res['moodle_id']] = $res['column'];

                $resArray['moodle_blueprint_map'][$res['moodle_id']] = $res['fisdap_item_id'];

                $resArray['sections'][$res['name']]++;
                $resArray['columns'][$res['column']]++;
                $resArray['sectionOptions'][$res['name']] = array(
                    'score_display' => $res['score_display'],
                    'grade_scale'   => $res['grade_scale']
                );
            }
        }

        return $resArray;
    }

    public function getQuizSections($moodleQuizId)
    {
        $resArray = $this->getItemIDSectionMapArray($moodleQuizId);

        $sectionInfo = array(
            'sections' => $resArray['sections'],
            'displayOptions' => $resArray['sectionOptions'],
        );

        return $sectionInfo;
    }

    // Get the passwords assigned for the dates of this quiz
    public function get_passwords($test_id, $start_date, $end_date, $format = "Y-m-d")
    {
        $em = \Fisdap\EntityUtils::getEntityManager();
        $qb = $em->createQueryBuilder();

        $start = new \DateTime($start_date);
        $end = new \DateTime($end_date);

        $qb->select('tp.password, tp.date')
            ->from('\Fisdap\Entity\TestPasswordDataLegacy', 'tp')
            ->where('tp.test = ?1')
            ->andWhere('tp.date >= ?2')
            ->andWhere('tp.date <= ?3')
            ->setParameter(1, $test_id)
            ->setParameter(2, $start->format('Y-m-d'))
            ->setParameter(3, $end->format('Y-m-d'));

        $r = $qb->getQuery()->getResult();

        $passwords = array();
        foreach ($r as $key => $result) {
            if (!array_key_exists($result['date']->format("Y-m-d"), $passwords)) {
                $passwords[$result['date']->format($format)] = $result['password'];
            }
        }

        return $passwords;
    }
}
