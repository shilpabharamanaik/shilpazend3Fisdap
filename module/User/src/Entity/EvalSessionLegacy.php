<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Legacy Entity class for Eval Sessions (filled out evals).
 *
 * @Entity
 * @Table(name="Eval_Session")
 */
class EvalSessionLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="EvalSession_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @Column(name="Evaluator", type="string")
     */
    protected $evaluator_id;
    
    /**
     * @Column(name="Subject", type="string")
     */
    protected $subject_id;
    
    /**
     * @Column(name="Date", type="string")
     */
    protected $date;
    
    /**
     * @Column(name="Signature", type="text", nullable=true)
     */
    protected $signature;
    
    /**
     * @Column(name="StartTime", type="string")
     */
    protected $start_time;
    
    /**
     * @Column(name="EndTime", type="string")
     */
    protected $end_time;
    
    /**
     * @Column(name="EvalDef_id", type="integer")
     */
    protected $eval_def_id;
    
    /**
     * @ManyToOne(targetEntity="EvalDefLegacy")
     * @JoinColumn(name="EvalDef_id", referencedColumnName="EvalDef_id")
     */
    protected $eval_def;
    
    /**
     * @Column(name="Passed", type="integer")
     */
    protected $passed;
    
    /**
     * @Column(name="EvalHookDef_id", type="integer")
     */
    protected $eval_hook_def_id;

    /**
     * @ManyToOne(targetEntity="ShiftLegacy", inversedBy="eval_sessions")
     * @JoinColumn(name="shift_id", referencedColumnName="Shift_id")
     */
    protected $shift;

    /**
     * Use the above association instead of this direct integer ID
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="Shift_id", type="integer")
     */
    protected $shift_id;
    
    /**
     * @Column(name="Confirmed", type="integer")
     */
    protected $confirmed;
    
    /**
     * @Column(name="EvaluatorType", type="integer")
     */
    protected $evaluator_type;
    
    /**
     * @Column(name="SubjectType", type="integer")
     */
    protected $subject_type;
    
    /**
     * @Column(name="Program_id", type="integer")
     */
    protected $program_id;
    
    /*
     * Add quick_evals flag here
     * Add generic critical criteria fail here
     */
    
    /**
     * This function is used to get a listing of the available evals for the
     * currently logged in user.
     *
     * @param int $shiftID
     * @param int $hookID
     * @param int $userID
     * @param array $evalDefIds
     */
    public static function getUsersCompletedEvals($shiftID=null, $hookID=null, $userID=null, $evalDefIds=array())
    {
        $completedEvals = array();
        
        // go through incoming args and set actual criteria values based on them
        $args = array();
                
        if ($shiftID != null) {
            $args['shift_id'] = $shiftID;
        }
        
        if ($hookID != null) {
            $args['eval_hook_def_id'] = $hookID;
        }
        
        $user = null;
        
        if ($userID == null) {
            if ($shiftID != null) {
                $shift = EntityUtils::getEntity('ShiftLegacy', $shiftID);
                $user = $shift->student->user;
            } else {
                $user = User::getLoggedInUser();
            }
        } else {
            $user = EntityUtils::getEntity('User', $userID);
        }
        
        if ($evalDefIds != null && !empty($evalDefIds)) {
            $args['eval_def_id'] = $evalDefIds; // array()
        }
                
        // From the incoming args, we need to generate WHERE clauses and DQL params
        $whereClauses = array();
        $dqlParams = array();
        
        // go through the automatically processed args to add to the query string and parameters
        foreach ($args as $field => $value) {
            // assuming any array implies OR logic between array members
            if (is_array($value)) {
                $placeholders = array();
                foreach ($value as $key => $multValue) {
                    $placeholders[] = ':' . $field . $key;
                    $dqlParams[$field . $key] = $multValue;
                }
                $whereClauses[] = 'e.' . $field . ' IN (' . implode(', ', $placeholders) . ')';
            } else {
                $whereClauses[] = 'e.' . $field . ' = :' . $field;
                $dqlParams[$field] = $value;
            }
        }
        
        // special case for subject_id and evaluator_id (OR between those fields)
        $whereClauses[] = "(e.subject_id = :subject_id OR e.evaluator_id = :evaluator_id)";
        $dqlParams['subject_id'] = $user->getCurrentRoleData()->id;
        $dqlParams['evaluator_id'] = $user->getCurrentRoleData()->id;
        
        // get entity manager
        $em = EntityUtils::getEntityManager();

        // assemble the query
        $dqlQuery = 'SELECT e FROM \Fisdap\Entity\EvalSessionLegacy e WHERE ' . implode(' AND ', $whereClauses) . ' ORDER BY e.date DESC';
        $query = $em->createQuery($dqlQuery);
        $query->setParameters($dqlParams);
        
        $completedEvals= $query->getResult();
        
        return $completedEvals;
    }

    /**
     * Determine whether the given user context has permission to edit/delete this eval
     * @param $userContext
     * @return bool
     */
    public function userCanEdit($userContext)
    {
        // only instructors can edit evals
        if (!$userContext->isInstructor()) {
            return false;
        }

        // you need permission to edit evals
        if (!$userContext->hasPermission("Edit Evals")) {
            return false;
        }

        // the eval has to belong to your program
        if ($userContext->program->id != $this->program_id) {
            return false;
        }

        return true;
    }
}
