<?php namespace Fisdap\BuildMetadata;

use Closure;
use Fisdap\Entity\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use vierbergenlars\SemVer\expression;
use vierbergenlars\SemVer\version;


/**
 * Ensures version in build metadata matches SemVer range specified in header
 *
 * @package Fisdap\BuildMetadata
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class VersionRangeMiddleware
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var AuthManager
     */
    private $auth;

    /**
     * @param Config $config
     * @param AuthManager $auth
     */
    public function __construct(Config $config, AuthManager $auth)
    {
        $this->config = $config;
        $this->auth = $auth;
    }


    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $appShortName = strtolower($this->config->get('build-metadata.appShortName'));
        $headerName = "$appShortName-version-range";

        $versionRangeHeader = $request->header($headerName);

        /** @var User $user */
        $user = $this->auth->guard()->user();

        if (
            $user && (
                $user->getUsername() == "rwebber" ||
                $user->getUsername() == "appledemoinstructor"
            )
        ) {
            return $next($request);
        }

        if ($versionRangeHeader !== null) {

            try {
                $build = new BuildMetadata();
                $build->load();

                $version = new version($build->projectVersion);

                $range = new expression($versionRangeHeader);

                if ( ! $range->satisfiedBy($version)) {

                    return new JsonResponse(
                        [
                            'error' => [
                                'code'      => 'Version requirements not met',
                                'message'   => "'{$headerName}' (header value) '" . $range->getString()
                                    . "' cannot be satisfied by '$appShortName' version "
                                    . $version->getVersion()

                            ]
                        ],
                        HttpResponse::HTTP_PRECONDITION_FAILED
                    );
                }

            } catch (\RuntimeException $e) {

                return new JsonResponse(
                    [
                        'error' => [
                            'code'      => $e->getCode(),
                            'message'   => 'A ' . get_class($e) . ' occurred - ' . $e->getMessage()
                        ]
                    ],
                    HttpResponse::HTTP_BAD_REQUEST
                );
            }
        }

        return $next($request);
    }
}