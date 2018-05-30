<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * Legacy Entity class for Eval definitions.
 *
 * @Entity(repositoryClass="Fisdap\Data\Evals\DoctrineEvalDefLegacyRepository")
 * @Table(name="Eval_def")
 */
class EvalDefLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="EvalDef_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @Column(name="EvalTitle", type="string")
     */
    protected $eval_title;
    
    /**
     * @Column(name="CutScore", type="integer")
     */
    protected $cut_score;
    
    /**
     * @Column(name="Approved", type="integer")
     */
    protected $approved;
    
    /**
     * @Column(name="DateApproved", type="string")
     */
    protected $date_approved;
    
    /**
     * @Column(name="FirstBlock", type="integer")
     */
    protected $first_block;
    
    /**
     * @Column(name="ResultSetable", type="integer")
     */
    protected $result_setable;
    
    /**
     * @Column(name="ShowPassFail", type="integer")
     */
    protected $show_pass_fail;
    
    /**
     * @Column(name="EvalType", type="integer")
     */
    protected $eval_type;
    
    /**
     * @Column(name="Predecessor_id", type="integer")
     */
    protected $predecessor_id;
    
    /**
     * @Column(name="SkillType", type="integer")
     */
    protected $skill_type;
    
    /**
     * @Column(name="SkillID", type="integer")
     */
    protected $skill_id;
    
    /**
     * @Column(name="Retired", type="integer")
     */
    protected $retired;
    
    /**
     * @Column(name="is_skillsheet", type="boolean")
     */
    protected $is_skillsheet;
    
    /**
     * @Column(name="is_affective_evaluation", type="boolean")
     */
    protected $is_affective_evaluation;
}
