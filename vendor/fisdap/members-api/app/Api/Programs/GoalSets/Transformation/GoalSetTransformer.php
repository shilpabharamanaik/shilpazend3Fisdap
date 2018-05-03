<?php namespace Fisdap\Api\Programs\GoalSets\Transformation;

use Fisdap\Entity\GoalSet;
use Fisdap\Fractal\Transformer;

/**
 * Prepares program data for JSON output
 *
 * @package Fisdap\Api\Programs\GoalSets\Transformation
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class GoalSetTransformer extends Transformer
{
    /**
     * @param GoalSet|array $goalSet
     *
     * @return array
     */
    public function transform($goalSet)
    {
        if ($goalSet instanceof GoalSet) {
            $goalSet = $goalSet->toArray();
        }

        $transformed = [
            'id'                    => $goalSet['id'],
            'name'                  => $goalSet['name'],
            'accountType'           => $goalSet['account_type'],
            'infantStartAge'        => $goalSet['infant_start_age'],
            'toddlerStartAge'       => $goalSet['toddler_start_age'],
            'preschoolerStartAge'   => $goalSet['preschooler_start_age'],
            'schoolAgeStartAge'     => $goalSet['school_age_start_age'],
            'adolescentStartAge'    => $goalSet['adolescent_start_age'],
            'adultStartAge'         => $goalSet['adult_start_age'],
            'geriatricStartAge'     => $goalSet['geriatric_start_age']
        ];

        return $transformed;
    }
}
