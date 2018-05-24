<?php namespace Fisdap\Api\Professions;

use Fisdap\Api\Professions\CertificationLevels\CertificationLevelTransformer;
use Fisdap\Entity\Profession;
use Fisdap\Fractal\Transformer;


/**
 * Prepares profession data for JSON output
 *
 * @package Fisdap\Api\Programs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ProfessionTransformer extends Transformer
{
    /**
     * @var array
     */
    protected $availableIncludes = [
        'certifications'
    ];
    
    /**
     * @var array
     */
    protected $defaultIncludes = [
        'certifications'
    ];

    
    /**
     * @param Profession|array $profession
     *
     * @return array
     */
    public function transform($profession)
    {
        if ($profession instanceof Profession) {
            $profession = $profession->toArray();
        }

        return $profession;
    }


    /**
     * @param $profession
     *
     * @return \League\Fractal\Resource\Collection|void
     * @codeCoverageIgnore
     */
    public function includeCertifications($profession)
    {
        $certificationLevels = $profession instanceof Profession ? $profession->certifications : $profession['certifications'];

        if (empty($certificationLevels)) return;

        return $this->collection($certificationLevels, new CertificationLevelTransformer);
    }
} 