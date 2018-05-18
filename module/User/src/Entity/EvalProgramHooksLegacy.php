<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;


/**
 * Legacy Entity class for Eval Program Hooks (custom eval hooking per program).
 * 
 * @Entity(repositoryClass="Fisdap\Data\Evals\DoctrineEvalProgramHooksLegacyRepository")
 * @Table(name="Eval_Program_Hooks")
 */
class EvalProgramHooksLegacy extends EntityBaseClass
{	
	/**
	 * @Id
	 * @Column(name="EvalProgramHook_id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @Column(name="EvalHookDef_id", type="integer")
	 */
	protected $hook;
	
	/**
	 * @Column(name="EvalDef_id", type="integer")
	 */
	protected $eval;
	
	/**
	 * @Column(name="Program_id", type="integer")
	 */
	protected $program;
}