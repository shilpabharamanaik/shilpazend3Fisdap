<?php namespace Fisdap\Data\Window;

use Fisdap\Data\Repository\DoctrineRepository;


class DoctrineWindowRepository extends DoctrineRepository implements WindowRepository
{
	
	public function getWindowsBySlot($slot)
	{
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select('w')
		   ->from('Fisdap\Entity\Window', 'w')
		   ->andWhere('w.slot >= ?1')
		   ->setParameter(1, $slot);
		   
		return $qb->getQuery()->getResult();
		
	}
	
	public function getActiveWindowsBySlot($program_id, $slot_id)
	{
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select('w')
		   ->from('Fisdap\Entity\Window', 'w')
		   ->andWhere('w.slot = ?1')
		   ->andWhere('w.program = ?2')
		   ->setParameter(1, $slot_id)
		   ->setParameter(2, $program_id);
		   
		return $qb->getQuery()->getResult();
	}
	
	
	public function getWindowConstraintsByGroupId($groupId){
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select('wc.id, wv.value, wv.description')
		->from('Fisdap\Entity\WindowConstraint', 'wc')
		->innerJoin('wc.values', 'wv')
		->andWhere('wc.constraint_type = 1')
		->andWhere('wv.value = ?1')
		->setParameter(1, $groupId);
		
		return $qb->getQuery()->getResult();
	}
	
	public function updateWindowConstraintValueDescriptions($groupId, $description){
		// Manually doing this one since DQL doesn't support joins in an update and this needs
		// to be somewhat efficient...
		
		$conn = \Fisdap\EntityUtils::getEntityManager()->getConnection();
		
		$qry = "
			UPDATE 
				fisdap2_window_constraint_values wcv
				INNER JOIN fisdap2_window_constraints wc ON wc.id = wcv.constraint_id
			SET
				wcv.description = '" . ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $description) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "'
			WHERE
				wc.constraint_type_id = 1
				AND wcv.value = " . ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $groupId) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "
		";
		
		$res = $conn->query($qry);
		
		return $res;
	}
}
