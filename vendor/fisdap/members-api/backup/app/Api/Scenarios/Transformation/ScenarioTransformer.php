<?php namespace Fisdap\Api\Scenarios\Transformation;

use Fisdap\Entity\Scenario;
use Fisdap\Fractal\Transformer;

/**
 * Prepares scenario data for JSON output
 *
 * @package Fisdap\Api\Scenarios\Transformation
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ScenarioTransformer extends Transformer
{
    /**
     * @param $scenario
     * @return array
     * @internal param array|ScenarioLegacy $scenario
     *
     */
    public function transform($scenario)
    {
        if ($scenario instanceof Scenario) {
            $scenario = $scenario->toArray();
        }

        if (isset($scenario['created'])) {
            $scenario['created'] = $this->formatDateTimeAsString($scenario['created']);
        }

        if (!isset($scenario['uuid'])) {
            $this->removeFields(["uuid"], $scenario);
        }

        return $scenario;
    }
}
