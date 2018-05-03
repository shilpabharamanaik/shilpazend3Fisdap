<?php namespace Fisdap\Api\Programs\Transformation;

use Fisdap\Entity\NarrativeSectionDefinition;
use Fisdap\Fractal\Transformer;

/**
 * Prepares program data for JSON output
 *
 * @package Fisdap\Api\Programs\Transformation
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class NarrativeDefinitionsTransformer extends Transformer
{
    /**
     * @param NarrativeSectionDefinition $narrativeDef
     * @return array
     */
    public function transform($narrativeDef)
    {
        if ($narrativeDef instanceof NarrativeSectionDefinition) {
            $narrativeDef = $narrativeDef->toArray();
        }
        return $narrativeDef;
    }
}
