<?php namespace Fisdap\Api\Client;

use JsonMapper as NetresearchJsonMapper;

/**
 * Class JsonMapper
 *
 * Overrides namespace handling in JsonMapper to work around issues with nested namespaces
 *
 * @package Fisdap\Api\Client
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @todo fork the package and make a pull request instead
 */
class JsonMapper extends NetresearchJsonMapper
{
    /**
     * Convert a type name to a fully namespaced type name.
     *
     * @param string $type  Type name (simple type or class name)
     * @param string $strNs Base namespace that gets prepended to the type name
     *
     * @return string Fully-qualified type name with namespace
     */
    protected function getFullNamespace($type, $strNs)
    {
        if (!strstr($type, '\\')) {
            if ($type !== '' && $type{0} != '\\') {
                //create a full qualified namespace
                if ($strNs != '') {
                    $type = '\\' . $strNs . '\\' . $type;
                }
            }
        }

        return $type;
    }
}
