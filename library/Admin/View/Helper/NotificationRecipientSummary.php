<?php

/****************************************************************************
 *
 *         Copyright (C) 1996-2011.  This is an unpublished work of
 *                          Headwaters Software, Inc.
 *                             ALL RIGHTS RESERVED
 *         This program is a trade secret of Headwaters Software, Inc.
 *         and it is not to be copied, distributed, reproduced, published,
 *         or adapted without prior authorization
 *         of Headwaters Software, Inc.
 *
 ****************************************************************************/

/**
 * This is a view helper for displaying a summary of the recipients
 */
class Admin_View_Helper_NotificationRecipientSummary extends Zend_View_Helper_Abstract
{
    /**
     * @var bool
     */
    protected static $initialized = false;

    /**
     * @var array
     */
    protected static $professionOptions;

    /**
     * @var array
     */
    protected static $certOptions;

    /**
     * @var array
     */
    protected static $permissionOptions;

    /**
     * Default entry point for this class.
     *
     * @param array $params
     *
     * @return string HTML of the summary
     */
    public function notificationRecipientSummary(array $params, array $viewData, $separator = "<br />")
    {
        //If this is the first time we're running thru this view helper, initialize the arrays of info
        if (self::$initialized === false) {
            self::$initialized = true;
            self::$professionOptions = \Fisdap\EntityUtils::getRepository('Profession')->getFormOptions();
            self::$certOptions = \Fisdap\EntityUtils::getRepository('CertificationLevel')->getSortedFormOptions();
            self::$permissionOptions = \Fisdap\EntityUtils::getRepository('Permission')->getFormOptions("bit_value", "name");
        }

        //Display professions
        $summary = "<span class='recipient-heading'>Professions:</span> " . (!empty($params['professions']) ? $this->generateSummary($params['professions'], self::$professionOptions) : "All") . $separator;

        //Display students
        $summary .= "<span class='recipient-heading'>Students:</span> ";
        if ($params['students']) {
            $summary .= (!empty($params['cert_levels']) ? $this->generateSummary($params['cert_levels'], self::$certOptions, true) : "All") . " with "
                      . ($params['products'] == 0 ? "all products" : \Fisdap\Entity\Product::getProductSummary($params['products'], 1, ', ', ' & '));
        } else {
            $summary .= "None";
        }
        $summary .= $separator;

        //Display instructors
        $summary .= "<span class='recipient-heading'>Instructors:</span> ";
        if ($params['instructors']) {
            $summary .= (!empty($params['permissions']) ? $this->generatePermissionSummary($params['permissions'], self::$permissionOptions, true) : "All");
        } else {
            $summary .= "None";
        }
        $summary .= $separator;

        //Display preceptors
        $summary .= "<span class='recipient-heading'>Preceptors:</span> ";
        $summary .= $params['preceptors'] ? "All" : "None";
        $summary .= $separator;

        // display view data table
        $summary .= "<table class='fisdap-table view-data-table'>";
        $summary .= "<tr class='recipient-heading'>".
            "<td>Sent to</td>".
            "<td>Closed by</td>".
            "<td>Still open</td>".
            "</tr>";
        $summary .= "<tr>".
            "<td>".($viewData['open'] + $viewData['closed'])."</td>".
            "<td>".$viewData['closed']."</td>".
            "<td>".$viewData['open']."</td>".
            "</tr>";
        $summary .= "</table>";

        return $summary;
    }

    /**
     * @param array $ids
     * @param array $options
     * @param bool  $useAmpersands
     *
     * @return string
     *
     * @todo maybe make this a helper for other classes one day
     */
    private function generateSummary(array $ids, array $options, $useAmpersands = false)
    {
        $summary = "";

        $idsLength = count($ids);

        //If there's only one item in the list, just return it
        if ($idsLength == 1) {
            return $options[array_pop($ids)];
        }

        for ($i=0;$i<$idsLength;$i++) {
            $summary .= $options[$ids[$i]];

            if ($useAmpersands && ($i == $idsLength - 2)) {
                $summary .= " & ";
            } elseif ($i < ($idsLength - 1)) {
                $summary .= ", ";
            }
        }

        return $summary;
    }

    /**
     * Because permissions are slightly different than the other params, using bit values and all
     *
     * @param int   $configuration
     * @param array $options
     *
     * @return string
     */
    private function generatePermissionSummary($configuration, array $options)
    {
        $permissions = [];
        foreach ($options as $bit_value => $name) {
            if ($configuration & $bit_value) {
                $permissions[] = $name;
            }
        }

        return implode(", ", $permissions);
    }
}
