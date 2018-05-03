<?php namespace Fisdap\Api\Programs\Settings;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Programs\Settings\Jobs\UpdateProgramSettings;
use Fisdap\Api\Programs\Transformation\NarrativeDefinitionsTransformer;
use Fisdap\Api\Programs\Settings\Transformation\ProgramSettingsTransformer;
use Fisdap\Entity\NarrativeSectionDefinition;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Http\Response as HttpResponse;

/**
 * Handles HTTP transport and data transformation for program settings related routes
 *
 * @package Fisdap\Api\Programs\Http
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ProgramSettingsController extends Controller
{
    use ResponseHelpers;


    /**
     * @var EntityManagerInterface
     */
    private $em;


    /**
     * @param Manager $fractal
     * @param EntityManagerInterface $em
     */
    public function __construct(
        Manager $fractal,
        EntityManagerInterface $em
    ) {
        $this->fractal      = $fractal;
        $this->em           = $em;
    }


    /**
     * @param int $programId
     * @param NarrativeDefinitionsTransformer $narrativeDefTransformer
     *
     * @return JsonResponse
     *
     * @SWG\Get(
     *     tags={"Programs"},
     *     path="/programs/{programId}/narrative-definitions",
     *     summary="Get narrative section definitions for the given program ID",
     *     description="Get narrative section definitions for the given program ID",
     *     @SWG\Parameter(name="programId", in="path", required=true, type="integer"),
     *     @SWG\Response(response="200", description="Narrative section definitions")
     * )
     */
    public function getNarrativeDefinitions($programId, NarrativeDefinitionsTransformer $narrativeDefTransformer)
    {
        return $this->respondWithCollection(
            $this->em->getRepository(NarrativeSectionDefinition::class)->findBy(["program_id" => $programId]),
            $narrativeDefTransformer
        );
    }

    /**
     * @param int $programId
     * @param UpdateProgramSettings $updateProgramSettingsJob
     * @param BusDispatcher $busDispatcher
     * @param ProgramSettingsTransformer $programSettingsTransformer
     *
     * @return JsonResponse
     *
     * @SWG\Patch(
     *     tags={"Programs"},
     *     path="/programs/{programId}/settings",
     *     summary="Updates a program's skills tracker settings",
     *     description="Updates a program's skills tracker settings",
     *     @SWG\Parameter(name="programId", in="path", required=true, type="integer"),
     *     @SWG\Parameter(
     *      name="ProgramSettings", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/ProgramSettings")
     *     ),
     *     @SWG\Response(
     *      response="201",
     *      description="This updates the skills tracker settings for a specified program ({programId}).")
     * )
     */
    public function updateProgramSettings(
        $programId,
        UpdateProgramSettings $updateProgramSettingsJob,
        BusDispatcher $busDispatcher,
        ProgramSettingsTransformer $programSettingsTransformer
    ) {
        $updateProgramSettingsJob->setProgramId($programId);
        $programSettings = $busDispatcher->dispatch($updateProgramSettingsJob);

        $this->setStatusCode(HttpResponse::HTTP_CREATED);

        return $this->respondWithItem($programSettings, $programSettingsTransformer);
    }
}
