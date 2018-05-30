<?php namespace Fisdap\Data\Asset;

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineAssetLegacyRepository
 *
 * @package Fisdap\Legacy\Asset
 */
class DoctrineAssetLegacyRepository extends DoctrineRepository implements AssetLegacyRepository
{
    public function getAllChildAssetsByParentName($parentName)
    {
        // Get the parent record from the DB...
        $parent = $this->findOneBy(array('parent_id' => -1, 'name' => $parentName));
        
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select('a');
        $qb->from('\Fisdap\Entity\AssetLegacy', 'a');
        $qb->andWhere('a.parent_id != -1');
        $qb->andWhere("a.tree_id LIKE '{$parent->id}.%'");
        
        $results = $qb->getQuery()->getResult();
        
        $parentNameLookup = array();
        $nestedResults = array();
        
        foreach ($results as $r) {
            if ($r->parent_id == $parent->id) {
                $parentNameLookup[$r->id] = $r->name;
                $nestedResults[$r->name][] = $r;
            } else {
                $splitTree = explode('.', $r->tree_id);
                if (count($splitTree) > 1) {
                    $nestedResults[$parentNameLookup[$splitTree[1]]][] = $r;
                }
            }
        }
        
        return $nestedResults;
    }
}
