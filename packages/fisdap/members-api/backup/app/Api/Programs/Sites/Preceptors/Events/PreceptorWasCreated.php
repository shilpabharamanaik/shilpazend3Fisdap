<?php namespace Fisdap\Api\Programs\Sites\Preceptors\Events;

use Fisdap\Api\Events\Event;

/**
 * Class PreceptorWasCreated
 *
 * @package Fisdap\Api\Programs\Sites\Preceptors\Events
 * @author  Isaac White <iwhite@fisdap.net>
 * @codeCoverageIgnore
 */
final class PreceptorWasCreated extends Event
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * PreceptorWasCreated constructor.
     * @param $id
     * @param $name
     */
    public function __construct($id, $name)
    {
        $this->id   = $id;
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
