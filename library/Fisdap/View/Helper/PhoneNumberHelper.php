<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This file contains a view helper to render a phone
 */

/**
 * @package Fisdap
 */
class Zend_View_Helper_PhoneNumberHelper extends Zend_View_Helper_Abstract
{
    /**
     * @var string
     */
    protected $_html= "";

    /**
     * @param string $number the phone number you want to format
     * @param string $country the country that this phone number is from
     * @return string the formatted number
     */
    public function phoneNumberHelper($number = "", $country = "USA")
    {
        if ($country == "USA") {
            return $this->formatNumberForUS($number);
        } else {
            return $number;
        }
    }

    /**
     * Add the parens and dashes to make a United States phone number look pretty
     *
     * @param $number
     * @return string
     */
    private function formatNumberForUS($number)
    {
        $format = false;
        $original_number = $number;
        $number = preg_replace('[\D]', '', $number);

        if ($number) {
            if (is_numeric($number)) {
                if (strlen($number) >= 10) {
                    $format = true;
                }
            }
        }

        if ($format) {
            $formatted_number = "(";
            $formatted_number .= substr($number, 0, 3);
            $formatted_number .= ") ";
            $formatted_number .= substr($number, 3, 3);
            $formatted_number .= "-";
            $formatted_number .= substr($number, 6, 4);

            if (strlen($number) > 10) {
                $formatted_number .= " x";
                $formatted_number .= substr($number, 10, strlen($number));
            }
        } else {
            $formatted_number = $original_number;
        }

        return $formatted_number;
    }
}
