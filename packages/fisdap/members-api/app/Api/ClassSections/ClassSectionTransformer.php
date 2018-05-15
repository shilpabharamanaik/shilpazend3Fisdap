<?php namespace Fisdap\Api\ClassSections;

use Fisdap\Fractal\Transformer;

/**
 * Prepares class section data for JSON output
 *
 * @package Fisdap\Api\ClassSections
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ClassSectionTransformer extends Transformer
{
    /**
     * @param array $classSection
     *
     * @return array
     */
    public function transform(array $classSection)
    {
        $classSection['start_date'] = $this->formatDateTimeAsString($classSection['start_date']);
        $classSection['end_date'] = $this->formatDateTimeAsString($classSection['end_date']);

        return $classSection;
    }
}
