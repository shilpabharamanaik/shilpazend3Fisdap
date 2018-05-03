<?php namespace Fisdap\Api\Programs\Sites\Queries;

use Fisdap\Api\Queries\Parameters\CommonQueryParameters;
use Fisdap\Api\Queries\Parameters\IdentifiedByPrograms;


/**
 * Encapsulates query parameter data for sites
 *
 * @package Fisdap\Api\Programs\Sites\Queries
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @todo extract interface and mark final
 */
class SiteQueryParameters extends CommonQueryParameters
{
    use IdentifiedByPrograms;
}