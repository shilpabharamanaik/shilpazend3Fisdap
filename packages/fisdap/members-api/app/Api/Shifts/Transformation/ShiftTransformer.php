<?php namespace Fisdap\Api\Shifts\Transformation;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Programs\Sites\Bases\BaseTransformer;
use Fisdap\Api\Programs\Sites\SiteTransformer;
use Fisdap\Api\Shifts\Attachments\ShiftAttachmentTransformer;
use Fisdap\Api\Shifts\Patients\Transformation\PatientsTransformer;
use Fisdap\Api\Shifts\PracticeItems\PracticeItemTransformer;
use Fisdap\Api\Shifts\PreceptorSignoffs\Transformation\PreceptorSignoffsTransformer;
use Fisdap\Entity\PreceptorSignoff;
use Fisdap\Entity\ShiftLegacy;
use Fisdap\Fractal\Transformer;
use Illuminate\Container\Container;

/**
 * Prepares shift data for JSON output
 *
 * @package Fisdap\Api\Shifts
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ShiftTransformer extends Transformer
{
    /**
     * @var string[]
     */
    protected $availableIncludes = [
        'site',
        'base',
        'attachments',
        'signoff', //todo
        'verification', //todo
        'attendence', // todo - fix spelling in entity/table
        'creator',
        'slot_assignment', //todo
        'patients',
        'practice_items',
        'meds', //todo
        'ivs', //todo
        'airways', //todo
        'vitals', //todo
        'cardiac_interventions', //todo
        'other_interventions' //todo
    ];

    /**
     * @var string[]
     * @todo?
     */
    protected $defaultIncludes = [
        //'signoff',
        //'verification',
        //'creator',
        //'slot_assignment'
    ];


    /**
     * @var Container
     */
    private $container;

    /**
     * @var Container
     */
    private $em;


    public function __construct(Container $container, EntityManagerInterface $em = null)
    {
        $this->container = $container;
        $this->em = $em;
    }


    /**
     * @param array $shift
     *
     * @return array
     */
    public function transform($shift)
    {
        if ($shift instanceof ShiftLegacy) {
            $shift = $shift->toArray();
        }

        $signoff = null;
        if ($this->em !== null) {
            $signoff = $this->em->getRepository(PreceptorSignoff::class)->findOneBy(['shift' => $shift['id']]);
            $transform = new PreceptorSignoffsTransformer;
            $signoff = $transform->transform($signoff);
            $verificationId = $this->getIdFromAssociation('verification', $signoff);
        }

        $signoffId = $signoff ? $signoff['id'] : null;
        $verificationId = isset($verificationId) ? $verificationId : null;


        $transformedShift = [
            'id'             => $shift['id'],
            'hours'          => $shift['hours'],
            'signoffId'      => $signoffId,
            'verificationId' => $verificationId,
            'entryTime'     => $this->formatDateTimeAsString($shift['entry_time']),
            'locked'         => $shift['locked'],
            'type'           => $shift['type'],
            'eventId'       => $shift['event_id'],
            'audited'        => $shift['audited'],
            'firstLocked'   => $this->formatDateTimeAsString($shift['first_locked']),
            'late'           => $shift['late'],
            'softDeleted'   => $shift['soft_deleted'],
            'startDatetime' => $this->formatDateTimeAsString($shift['start_datetime']),
            'endDatetime'   => $this->formatDateTimeAsString($shift['end_datetime']),
            'studentId'     => $this->getIdFromAssociation('student', $shift),
            'siteId'        => $this->getIdFromAssociation('site', $shift),
            'baseId'        => $this->getIdFromAssociation('base', $shift),
            // todo correct spelling on attendence
            'attendanceId'  => $this->getIdFromAssociation('attendence', $shift),
            'patientIds'    => $this->getIdsFromAssociation('patients', $shift),
            'attachmentIds' => $this->getIdsFromAssociation('attachments', $shift),
            'created'        => $this->formatDateTimeAsString($shift['created']),
            'updated'        => $this->formatDateTimeAsString($shift['updated'])
        ];

        $transformedShift = self::appendIfExists($transformedShift, 'medications', (isset($shift['medications']) ? $shift['medications'] : []));
        $transformedShift = self::appendIfExists($transformedShift, 'otherInterventions', (isset($shift['otherInterventions']) ? $shift['otherInterventions'] : []));
        $transformedShift = self::appendIfExists($transformedShift, 'cardiacInterventions', (isset($shift['cardiacInterventions']) ? $shift['cardiacInterventions'] : []));
        $transformedShift = self::appendIfExists($transformedShift, 'airways', (isset($shift['airways']) ? $shift['airways'] : []));
        $transformedShift = self::appendIfExists($transformedShift, 'ivs', (isset($shift['ivs']) ? $shift['ivs'] : []));
        $transformedShift = self::appendIfExists($transformedShift, 'vitals', (isset($shift['vitals']) ? $shift['vitals'] : []));

        if (isset($signoff)) {
            $transformedShift = self::appendIfExists($transformedShift, 'signoff', $signoff);
        }

        $this->addCommonTimestamps($transformedShift, $shift);

        return $transformedShift;
    }

    private static function appendIfExists($transformArray, $key, $value)
    {
        if (!empty($value)) {
            $transformArray[$key] = $value;
        }

        // This looks pointless, I know, but mobile was complaining that the API was returning 0 when
        // size wasn't sent. So I'm explicitly forcing a null value instead of the default integer for
        // and empty value.
        if (isset($transformArray['size']) && $transformArray['size'] === null) {
            $transformArray['size'] = null;
        }

        return $transformArray;
    }


    /*
     * Includes
     */

    /**
     * @param array $shift
     *
     * @return \League\Fractal\Resource\Item
     * @codeCoverageIgnore
     */
    public function includeSite($shift)
    {
        if ($shift instanceof ShiftLegacy) {
            $shift = $shift->toArray();
        }
        $site = $shift['site'];

        return $this->item($site, new SiteTransformer);
    }


    /**
     * @param array $shift
     *
     * @return \League\Fractal\Resource\Item
     * @codeCoverageIgnore
     */
    public function includeBase($shift)
    {
        if ($shift instanceof ShiftLegacy) {
            $shift = $shift->toArray();
        }
        $base = $shift['base'];

        return $this->item($base, new BaseTransformer);
    }


    /**
     * @param array $shift
     *
     * @return \League\Fractal\Resource\Collection
     * @codeCoverageIgnore
     */
    public function includeAttachments($shift)
    {
        if ($shift instanceof ShiftLegacy) {
            $shift = $shift->toArray();
        }
        $attachments = $shift['attachments'];

        return $this->collection($attachments, $this->container->make(ShiftAttachmentTransformer::class));
    }


    /**
     * @todo correct spelling
     *
     * @param array $shift
     *
     * @return \League\Fractal\Resource\Item
     * @codeCoverageIgnore
     */
    public function includeAttendence($shift)
    {
        if ($shift instanceof ShiftLegacy) {
            $shift = $shift->toArray();
        }
        $attendance = $shift['attendence'];

        return $this->item($attendance, new ShiftAttendanceTransformer);
    }


    /**
     * @param array $shift
     *
     * @return \League\Fractal\Resource\Collection
     * @codeCoverageIgnore
     */
    public function includePatients($shift)
    {
        if ($shift instanceof ShiftLegacy) {
            $shift = $shift->toArray();
        }
        $patients = $shift['patients'];

        return $this->collection($patients, new PatientsTransformer);
    }


    /**
     * @param array $shift
     *
     * @return \League\Fractal\Resource\Collection
     * @codeCoverageIgnore
     */
    public function includePracticeItems($shift)
    {
        if ($shift instanceof ShiftLegacy) {
            $shift = $shift->toArray();
        }
        $practiceItems = $shift['practice_items'];

        return $this->collection($practiceItems, new PracticeItemTransformer);
    }
}
