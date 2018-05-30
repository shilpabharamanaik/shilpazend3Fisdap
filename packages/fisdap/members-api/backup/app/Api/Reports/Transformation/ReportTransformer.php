<?php namespace Fisdap\Api\Reports\Transformation;

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
 * Prepares report data for JSON output
 *
 * @package Fisdap\Api\Reports
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ReportTransformer extends Transformer
{
    public function transform($report)
    {
        // Process headers
        if (isset($report['headers'])) {
        }

        $transformed = [

        ];

        return $report;
    }
}
