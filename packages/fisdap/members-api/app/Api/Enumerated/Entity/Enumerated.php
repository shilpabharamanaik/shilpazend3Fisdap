<?php namespace Fisdap\Api\Enumerated\Entity;

use Swagger\Annotations as SWG;

/**
 * Class Enumerated
 * @package Fisdap\Api\Enumerated\Entity
 * @author  Isaac White <isaac.white@ascendlearning.com>
 *
 * @SWG\Definition(
 *     definition="Enumerated",
 *     required={ "id", "name" }
 * )
 */
class Enumerated
{
    /**
     * @var integer
     * @SWG\Property(type="integer", default=1)
     */
    public $id;

    /**
     * @var string
     * @SWG\Property(type="string", default="name")
     */
    public $name;
}
