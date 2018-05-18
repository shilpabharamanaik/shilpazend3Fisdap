<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for AirwayManagementSource.
 * 
 * @Entity
 * @Table(name="fisdap2_airway_management_source")
 */
class AirwayManagementSource extends Enumerated {}