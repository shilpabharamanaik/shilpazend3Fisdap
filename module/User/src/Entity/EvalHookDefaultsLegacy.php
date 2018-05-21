<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * Legacy Entity class for Eval Hook Defaults (mapping default evals to hooks).
 *
 * @Entity
 * @Table(name="Eval_Hook_Defaults")
 */
class EvalHookDefaultsLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="EvalHookDefault_id", type="integer")
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
}
