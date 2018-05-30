<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * TestType Legacy entity
 *
 * The name/label for an NREMT test (or part of the test)
 * Mostly decprecated - these days we are only really using
 * "National Registry Written" (paramedic) and "National Registry Written Basic" (emt)
 *
 * @Entity
 * @Table(name="TestTypes")
 */
class TestTypeLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="TestType_id", type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(name="TestName", type="string")
     */
    protected $test_name;
    
    /**
     * @Column(name="CertificationLevel", type="string")
     */
    protected $certification_level;
}
