<?php namespace Fisdap\Queries\Specifications;

/**
 * Class HandlesAssociations
 *
 * @package Fisdap\Queries\Specifications
 */
trait HandlesAssociations
{
    /**
     * @var array|\string[]
     */
    private $associations;


    private function ensureParentAssociationsAreIncluded()
    {
        $parsed = [];

        foreach ($this->associations as $association) {
            $nested = explode('.', $association);
            $part = array_shift($nested);
            $parsed[] = $part;

            while (count($nested) > 0) {
                $part .= '.'.array_shift($nested);
                $parsed[] = $part;
            }
        }

        $this->associations = array_values(array_unique($parsed));
    }
}