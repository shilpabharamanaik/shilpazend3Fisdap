<?php namespace Fisdap\Api\Programs\Sites\Finder;

use Fisdap\Api\Programs\Sites\Queries\SiteQueryParameters;
use Fisdap\Api\Programs\Sites\Queries\Specifications\DistinctStudentShiftSites;
use Fisdap\Api\Programs\Sites\Queries\Specifications\ProgramSites;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\ResourceFinder\ResourceFinder;
use Fisdap\Data\Base\BaseLegacyRepository;
use Fisdap\Data\Base\DoctrineBaseLegacyRepository;
use Fisdap\Data\Site\SiteLegacyRepository;
use Fisdap\Entity\SiteLegacy;
use Happyr\DoctrineSpecification\Spec;

/**
 * Service for retrieving sites
 *
 * @package Fisdap\Api\Programs\Sites\Finder
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class SitesFinder extends ResourceFinder implements FindsSites
{
    /**
     * @var SiteLegacyRepository
     */
    protected $repository;

    /**
     * @var BaseLegacyRepository
     */
    protected $baseRepo;


    /**
     * @param SiteLegacyRepository $repository
     * @param BaseLegacyRepository $baseLegacyRepository
     */
    public function __construct(SiteLegacyRepository $repository, BaseLegacyRepository $baseLegacyRepository)
    {
        $this->repository = $repository;
        $this->baseRepo = $baseLegacyRepository;
    }


    /**
     * @inheritdoc
     */
    public function findProgramSites(SiteQueryParameters $queryParams)
    {
        $programSites = $this->repository->match(
            new ProgramSites($queryParams),
            Spec::asArray(),
            $queryParams->getFirstResult(),
            $queryParams->getMaxResults()
        );

        foreach ($programSites as &$site) {
            if (isset($site['id'])) {
                /** @var SiteLegacy $siteObj */
                $siteObj = $this->repository->find($site['id']);
                $site['active'] = $siteObj->isSiteActive($queryParams->getProgramIds()[0]);
            }
        }

        if ($queryParams->getAssociations() && in_array('bases', $queryParams->getAssociations())) {
            foreach ($programSites as &$site) {
                $bases = $this->baseRepo->getBaseAssociationsByProgramOptimized($site['id'], $queryParams->getProgramIds()[0], null, null, true);

                $cleanBases = array();

                // Need to clean up/change the structure of the bases returned by the above function
                foreach ($bases as &$base) {
                    unset($base['base']['site']);
                    $base['base']['active'] = $base['active'];
                    $cleanBases[] = $base['base'];
                }

                $site['bases'] = $cleanBases;
            }
        }

        if (empty($programSites)) {
            throw new ResourceNotFound("No sites found for program");
        }

        return $programSites;
    }


    /**
     * @inheritdoc
     */
    public function findDistinctStudentShiftSites($studentId)
    {
        $distinctStudentShiftsSites = $this->repository->match(
            new DistinctStudentShiftSites($studentId),
            Spec::asArray()
        );

        if (empty($distinctStudentShiftsSites)) {
            throw new ResourceNotFound("No distinct shift sites found for student with id '$studentId'");
        }

        return $distinctStudentShiftsSites;
    }
}
