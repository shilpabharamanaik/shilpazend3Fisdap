<?php namespace Fisdap\Api\ClassSections;

use Fisdap\Fractal\Transformer;


/**
 * Prepares class section student data for JSON output
 *
 * @package Fisdap\Api\ClassSections
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ClassSectionStudentTransformer extends Transformer
{
    /**
     * @var string[]
     */
    protected $availableIncludes = [
        'section'
    ];

    /**
     * @var string[]
     */
    protected $defaultIncludes = [
        'section'
    ];


    /**
     * @param array $classSectionStudent
     *
     * @return array
     */
    public function transform(array $classSectionStudent)
    {
        return $classSectionStudent;
    }


    /**
     * @param array $classSectionStudent
     *
     * @return \League\Fractal\Resource\Item
     * @codeCoverageIgnore
     */
    public function includeSection(array $classSectionStudent)
    {
        $classSection = $classSectionStudent['section'];

        return $this->item($classSection, new ClassSectionTransformer);
    }
}