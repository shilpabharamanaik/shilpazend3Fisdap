<?php namespace Fisdap\Api\Http\Middleware;

use Closure;
use Fisdap\Logging\DebugMonologProcessor;
use Fisdap\Logging\ProcessesDebugLogs;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;

/**
 * Facilitates adding debugging info to all responses
 *
 * @package Fisdap\Api\Http\Middleware
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class DebugResponse
{
    /**
     * @var Container
     */
    private $container;


    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return \Illuminate\Http\Response
     */
    public function handle($request, Closure $next)
    {
        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        // optionally add log messages
        if ($this->container->bound(DebugMonologProcessor::class) and in_array(
            'application/json',
                $request->getAcceptableContentTypes()
        )
        ) {
            /** @var DebugMonologProcessor $logCollector */
            $logCollector = $this->container->make(ProcessesDebugLogs::class);

            $json = json_decode($response->getContent());
            $json->_log = $logCollector->getMessages();

            $response->setContent(json_encode($json));
        }

        return $response;
    }
}
