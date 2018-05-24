<?php namespace Fisdap\Api\Shifts\PracticeItems;

use League\Fractal\TransformerAbstract as Transformer;


/**
 * Class PracticeItemTransformer
 *
 * @package Fisdap\Api\Shifts\PracticeItems
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class PracticeItemTransformer extends Transformer
{
    /**
     * @var array
     */
    protected $availableIncludes = [
        'student',
        'shift',
        'practice_definition',
        'eval_session',
        'patient_type',
        'evaluator_type',
        'airway_management',
        'meds',
        'ivs',
        'airways',
        'vitals',
        'cardiac_interventions',
        'other_interventions'
    ];


    /**
     * @param array $practiceItem
     *
     * @return array
     */
    public function transform(array $practiceItem)
    {
        return [
            'id' => $practiceItem['id'],
            'passed' => $practiceItem['passed'],
            'confirmed' => $practiceItem['confirmed'],
            'time' => $practiceItem['time']->format('H:i:s'),
            'evaluator_id' => $practiceItem['evaluator_id']
        ];
    }
}