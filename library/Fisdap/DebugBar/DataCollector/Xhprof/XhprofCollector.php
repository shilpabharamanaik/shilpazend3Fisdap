<?php
/**
 * Created by PhpStorm.
 * User: jmortenson
 * Date: 11/12/14
 * Time: 4:26 PM
 */

namespace Fisdap\DebugBar\DataCollector\Xhprof;

/**
 * Collects info about memory usage
 */
class XhprofCollector extends \DebugBar\DataCollector\DataCollector implements \DebugBar\DataCollector\Renderable, \DebugBar\DataCollector\AssetProvider
{
    protected $xhprofURL = '';

    /**
     * Sets the Xhprof Profile URL
     */
    public function setUrl($url)
    {
        return $this->xhprofURL = $url;
    }

    public function getAssets() {
        return array(
            'js' => '/js/library/Fisdap/DebugBar/DataCollector/LinkIndicator.js'
        );
    }

    public function collect()
    {
        return array(
            'profile_link_text' => 'Xhprof',
        );
    }

    public function getName()
    {
        return 'xhprof';
    }

    public function getWidgets()
    {
        return array(
            "xhprof" => array(
                "icon" => "cogs",
                "tooltip" => "Link to Xhprof profile for this request",
                "map" => "xhprof.profile_link_text",
                "href" => $this->xhprofURL,
                "default" => "''",
                "indicator" => "LinkIndicator",
            )
        );
    }
}
