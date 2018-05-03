<?php namespace Fisdap\Api\Programs\Transformation;

use Fisdap\Api\Programs\Settings\Transformation\ProgramAirwayProcedureTransformer;
use Fisdap\Api\Programs\Settings\Transformation\ProgramCardiacProcedureTransformer;
use Fisdap\Api\Programs\Settings\Transformation\ProgramIvProcedureTransformer;
use Fisdap\Api\Programs\Settings\Transformation\ProgramLabAssessmentTransformer;
use Fisdap\Api\Programs\Settings\Transformation\ProgramMedTypeTransformer;
use Fisdap\Api\Programs\Settings\Transformation\ProgramOtherProcedureTransformer;
use Fisdap\Api\Programs\Settings\Transformation\ProgramSettingsTransformer;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Fractal\Transformer;

/**
 * Prepares program data for JSON output
 *
 * @package Fisdap\Api\Programs\Transformation
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ProgramTransformer extends Transformer
{
    /**
     * @var array
     */
    protected $availableIncludes = [
        'airway_procedures',
        'cardiac_procedures',
        'iv_procedures',
        'lab_assessments',
        'med_types',
        'other_procedures'
    ];

    /**
     * @var array
     */
    protected $defaultIncludes = [
        'program_settings'
    ];
    
    
    /**
     * @param ProgramLegacy|array $program
     *
     * @return array
     */
    public function transform($program)
    {
        if ($program instanceof ProgramLegacy) {
            $program = $program->toArray();
        } else {
            // I'm sorry. This is really not pretty and I'm sure there's a better way to do this, but I don't know how.
            //
            // The flags for these three permissions are stored BACKWARDS in the database (I know, I don't know why either).
            // This isn't an issue when the toArray() function is called on the entity, but when program data is nested
            // as an include on other endpoints, it comes into this transformer as an array. Hence this terrible hack
            // of flipping the values if $program is not in its object form.
            //
            // - nkarnick 7/12/17

            if (isset($program['can_students_create_field'])) {
                $program['can_students_create_field'] = !$program['can_students_create_field'];
            }

            if (isset($program['can_students_create_lab'])) {
                $program['can_students_create_lab'] = !$program['can_students_create_lab'];
            }

            if (isset($program['can_students_create_clinical'])) {
                $program['can_students_create_clinical'] = !$program['can_students_create_clinical'];
            }
        }

        if (isset($program['created'])) {
            $program['created'] = $this->formatDateTimeAsString($program['created']);
        }

        if (!isset($program['uuid'])) {
            $this->removeFields(["uuid"], $program);
        }

        // remove deprecated fields
        $this->removeFields([
            'program_type',
            'use_scheduler',
            'field_trade_code',
            'field_drop_code',
            'class_start_dates',
            'clinical_trade_code',
            'clinical_drop_code',
            'lab_trade_code',
            'lab_drop_code',
            'requirements',
            'use_beta',
            'scheduler_beta'
        ], $program);

        return $program;
    }


    /**
     * @param ProgramLegacy|array $program
     *
     * @return void|\League\Fractal\Resource\Item
     * @codeCoverageIgnore
     */
    public function includeProgramSettings($program)
    {
        if ($program instanceof ProgramLegacy) {
            $programSettings = $program->getProgramSettings();
        } else {
            $programSettings = isset($program['program_settings']) ? $program['program_settings'] : null;
        }
        
        if (is_null($programSettings)) {
            return null;
        }

        return $this->item($programSettings, new ProgramSettingsTransformer);
    }

    /**
     * @param ProgramLegacy|array $program
     *
     * @return \League\Fractal\Resource\Collection|void
     */
    public function includeAirwayProcedures($program)
    {
        if ($program instanceof ProgramLegacy) {
            $airwayProcedures = $program->getAirwayProcedures();
        } else {
            $airwayProcedures = isset($program['airway_procedures']) ? $program['airway_procedures'] : null;
        }

        if (is_null($airwayProcedures)) {
            return null;
        }

        return $this->collection($airwayProcedures, new ProgramAirwayProcedureTransformer);
    }

    /**
     * @param ProgramLegacy|array $program
     *
     * @return \League\Fractal\Resource\Collection|null
     */
    public function includeCardiacProcedures($program)
    {
        if ($program instanceof ProgramLegacy) {
            $cardiacProcedures = $program->getCardiacProcedures();
        } else {
            $cardiacProcedures = isset($program['cardiac_procedures']) ? $program['cardiac_procedures'] : null;
        }

        if (is_null($cardiacProcedures)) {
            return null;
        }

        return $this->collection($cardiacProcedures, new ProgramCardiacProcedureTransformer);
    }

    /**
     * @param $program
     * @return \League\Fractal\Resource\Collection|null
     */
    public function includeIvProcedures($program)
    {
        if ($program instanceof ProgramLegacy) {
            $ivProcedures = $program->getIvProcedures();
        } else {
            $ivProcedures = isset($program['iv_procedures']) ? $program['iv_procedures'] : null;
        }

        if (is_null($ivProcedures)) {
            return null;
        }

        return $this->collection($ivProcedures, new ProgramIvProcedureTransformer);
    }

    /**
     * @param ProgramLegacy|array $program
     *
     * @return \League\Fractal\Resource\Collection|null
     */
    public function includeLabAssessments($program)
    {
        if ($program instanceof ProgramLegacy) {
            $labAssessments = $program->getLabAssessments();
        } else {
            $labAssessments = isset($program['lab_assessments']) ? $program['lab_assessments'] : null;
        }

        if (is_null($labAssessments)) {
            return null;
        }

        return $this->collection($labAssessments, new ProgramLabAssessmentTransformer);
    }

    /**
     * @param ProgramLegacy|array $program
     *
     * @return \League\Fractal\Resource\Collection|null
     */
    public function includeMedTypes($program)
    {
        if ($program instanceof ProgramLegacy) {
            $medTypes = $program->getMedTypes();
        } else {
            $medTypes = isset($program['med_types']) ? $program['med_types'] : null;
        }

        if (is_null($medTypes)) {
            return null;
        }

        return $this->collection($medTypes, new ProgramMedTypeTransformer);
    }

    /**
     * @param ProgramLegacy|array $program
     *
     * @return \League\Fractal\Resource\Collection|null
     */
    public function includeOtherProcedures($program)
    {
        if ($program instanceof ProgramLegacy) {
            $otherProcedures = $program->getOtherProcedures();
        } else {
            $otherProcedures = isset($program['other_procedures']) ? $program['other_procedures'] : null;
        }

        if (is_null($otherProcedures)) {
            return null;
        }

        return $this->collection($otherProcedures, new ProgramOtherProcedureTransformer);
    }
}
