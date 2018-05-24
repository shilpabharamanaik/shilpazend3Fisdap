<?php namespace Fisdap\Fractal;

use Request;


/**
 * Enables handling of common request input parameters
 *
 * @package Fisdap\Fractal
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait CommonInputParameters
{
    /**
     * @return string[]|null
     */
    protected function initAndGetIncludes()
    {
        $includes = null;

        if ( ! Request::has('includes')) return $includes;

        $includes = Request::get('includes');

        // enable Fractal includes
        $this->fractal->parseIncludes($includes);

        // convert to array
        $includes = explode(',', $includes);

        // inform instances of Fisdap\Fractal\Transformer of includes
        if ($this->transformer instanceof Transformer) {
            $this->transformer->setIncludes($includes);
        }

        return $includes;
    }


    /**
     * @return string[]|null
     */
    protected function getIncludeIds()
    {
        return Request::has('includeIds') ? explode(',', Request::get('includeIds')) : null;
    }


    /**
     * @return int|null
     */
    protected function getFirstResult()
    {
        return Request::get('firstResult');
    }


    /**
     * @return int|null
     */
    protected function getMaxResults()
    {
        return Request::get('maxResults');
    }
}