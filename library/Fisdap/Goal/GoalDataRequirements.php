<?php

namespace Fisdap\Goal;

/**
 *	Data parameters for goals report.
 *	@autor Maciej
 */
class GoalDataRequirements
{
    public $startDate;
    public $endDate;
    public $subjectTypes;
    public $shiftSites; // will accept site types OR site ids
    public $auditedOrAll;
    public $alsType;
    
    public $defaults = null;
    
    public function __construct($options)
    {
        if (is_null($this->defaults)) {
            $this->defaults = array(
                'startDate' => new \DateTime('15 years ago'),
                'endDate' => new \DateTime(),
                'subjectTypes' => array(1, 2, 3, 4, 5, 6),
                'shiftSites' => array('field', 'clinical', 'lab'),
                'auditedOrAll' => 0,
                'alsType' => 'fisdap'
            );
        }
        $this->setOptionsUsingDefaults($options);
    }
    
    /**
     *	Sets all options overwriting current ones, using defaults when not provided
     */
    protected function setOptionsUsingDefaults($options)
    {
        foreach ($this->defaults as $option => $defaultVal) {
            $this->$option = (isset($options[$option])) ?
                $options[$option] : $defaultVal;
        }
    }

    public function getAlsType()
    {
        return $this->alsType;
    }
}
