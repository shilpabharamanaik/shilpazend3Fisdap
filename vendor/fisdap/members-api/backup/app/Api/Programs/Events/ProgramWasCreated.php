<?php namespace Fisdap\Api\Programs\Events;

use Fisdap\Api\Events\Event;
use Fisdap\Api\Programs\Settings\Jobs\Models\Settings;

/**
 * Class ProgramWasCreated
 *
 * @package Fisdap\Api\Programs\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class ProgramWasCreated extends Event
{
    /**
     * @var int
     */
    private $id;
    
    /**
     * @var string
     */
    private $name;
    
    /**
     * @var Settings|null
     */
    private $settings = null;


    /**
     * ProgramWasCreated constructor.
     *
     * @param int           $id
     * @param string        $name
     * @param Settings|null $settings
     */
    public function __construct($id, $name, Settings $settings = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->settings = $settings;
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


    /**
     * @return Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
