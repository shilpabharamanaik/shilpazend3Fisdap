<?php namespace Fisdap\BuildMetadata;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Handles HTTP transport for build metadata
 *
 * @package Fisdap\BuildMetadata
 * @author  Ben Getsug
 */
class BuildMetadataController extends Controller
{
    /**
     * @var Request
     */
    private $request;


    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    public function build()
    {
        $build = new BuildMetadata();
        $build->load();

        if ($this->request->get('format') == 'json' or $this->request->header('Accept') == 'application/json') {
            return new JsonResponse((array) $build);
        } else {
            return view('build-metadata::build', (array) $build);
        }
    }
}
