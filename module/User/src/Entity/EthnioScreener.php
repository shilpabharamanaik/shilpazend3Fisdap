<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * Class EthnioScreener
 *
 * @package Fisdap\Entity
 * @author  Sam Tape <stape@fisdap.net>
 *
 * @Entity
 * @Table(name="fisdap2_ethnio_screeners")
 */
class EthnioScreener extends EntityBaseClass
{
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $name;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $screener_id;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $url;

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $active = true;
}
