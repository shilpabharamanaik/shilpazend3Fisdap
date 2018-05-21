<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * Evaluator Type
 *
 * @Entity
 * @Table(name="fisdap2_evaluator_type")
 */
class EvaluatorType extends Enumerated
{
    /**
     * @var string
     * @Column(type="string")
     */
    protected $entity_name;
}
