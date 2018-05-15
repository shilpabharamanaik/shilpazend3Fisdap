<?php

class Util_Browser
{

    /**
     * detects whether a browser supports svg images
     */
    public static function supportsSVG()
    {
        $supportsSVG = true;

        // Browser checking
        $device = Zend_Registry::get('device');
        $browser = $device->getBrowser();
        $version = intval($device->getBrowserVersion());

        if ($browser == 'Internet Explorer' && $version < 9) {
            $supportsSVG = false;
        }

        return $supportsSVG;
    }
}
