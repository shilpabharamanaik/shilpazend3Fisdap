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
 * This helper will display a list of the user's activation code inventory
 */

/**
 * @package Account
 */
class Account_View_Helper_InventoryList extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;

    /**
     * @param int $productConfig the configuration of the products the user wants to see
     * @param dateTime $dateBegin the date the user will search from
     * @param dateTime $dateEnd the date the user will search to
     * @param int $gradYear the graduation year associated wtih activation codes
     * @param string $section the specific section associated with wanted activation codes
     * @param array $certLevels an array of cerfication level ids
     * @param array $status an array of statuses (activated, distributed)
     *
     * @return string the shift list rendered as an html table
     */
    public function inventoryList($filters)
    {
        $activationCodes = $this->getActivationCodes($filters);
        $activationCodesCount = count($activationCodes);

        if ($activationCodesCount == 0) {
            return "<div id='noCodesFound' class='error'>We could not find any activation codes matching your search criteria.</div>";
        } else {
            if ($activationCodesCount > 0) {
                $this->showActivationCodesTable($activationCodes);
            }


            return $this->_html;
        }
    }

    protected function getActivationCodes($filters)
    {
        $em = \Fisdap\EntityUtils::getEntityManager();

        $rawCodes = $em->getRepository('Fisdap\Entity\SerialNumberLegacy')->getSerialNumbers($filters);
        $codePartials = array();

        // this is a hacky way to return no codes if no statuses have been chosen
        if (array_sum($filters['status']) == 0) {
            return $codePartials;
        }

        foreach ($rawCodes as $code) {
            $haveACode = ($filters['codes'][0]) ? true : false;

            if ($filters['gradMonth']) {
                $gradDate = ($code['graduation_date'] instanceof \DateTime) ? $code['graduation_date'] : new \DateTime($code['graduation_date']);

                if ($gradDate->format('m') == $filters['gradMonth']) {
                    $hasCorrectMonth = true;
                } else {
                    $hasCorrectMonth = false;
                }
            } else {
                $hasCorrectMonth = true;
            }

            // doctrine is dumb, do the bit comparison for selected products here
            // also need to double check the month
            if ((($haveACode) || ((int)$code['configuration'] & (int)$filters['productConfig'])) && $hasCorrectMonth) {
                $products = \Fisdap\EntityUtils::getRepository("Product")->getProducts($code['configuration'], true, true);
                $code['products'] = $products;

                $orderDate = ($code['order_date'] instanceof \DateTime) ? $code['order_date'] : new \DateTime($code['order_date']);

                if ($code['id']) {
                    $code['order_date'] = "#" . $code['id'] . " on " . $orderDate->format('n/j/y');
                } else {
                    $code['order_date'] = $orderDate->format('n/j/y');
                }

                if (!$code['description']) {
                    if (!$code['account_type']) {
                        $code['description'] = "N/A";
                    } else {
                        if (strlen($code['account_type']) > 4) {
                            $certDescription = ucfirst($code['account_type']);
                        } else {
                            $certDescription = strtoupper($code['account_type']);
                        }
                        $code['description'] = $certDescription;
                    }
                }

                $code['advanced'] = "";
                if ($code['graduation_date']) {
                    $gradDate = ($code['graduation_date'] instanceof \DateTime) ? $code['graduation_date'] : new \DateTime($code['graduation_date']);
                    $code['advanced'] = $gradDate->format('M Y');
                    if ($code['name']) {
                        $code['advanced'] .= "<br />";
                    }
                }

                if ($code['name']) {
                    $code['advanced'] .= $code['name'];
                }

                $serial = \Fisdap\Entity\SerialNumberLegacy::getBySerialNumber($code['number']);
                $activated = ($serial->user) ? true : false;
                if (!$activated) {
                    if (!$code['distribution_date']) {
                        $code['availability'] = "<img id='checkmark' src='/images/icons/checkmark.png'>";
                    } else {
                        $date = ($code['distribution_date'] instanceof \DateTime) ? $code['distribution_date'] : new \DateTime($code['distribution_date']);
                        $code['availability'] = "Distributed to<br />" . $code['distribution_email'] . "<br />" . $date->format('F j, Y');
                    }
                } else {
                    $date = ($code['activation_date'] instanceof \DateTime) ? $code['activation_date'] : new \DateTime($code['activation_date']);
                    if ((int)$date->format('Y') > 1980) {
                        $displayDate = $date->format('F j, Y');
                    } else {
                        $displayDate = "";
                    }

                    // if the code is activated but not associated with a student id or instructor id, it was used for upgrading
                    $action = ($serial->student_id > 0 || $serial->instructor_id > 0) ? "Activated by" : "Used for upgrade by";
                    $code['availability'] = "$action<br />" . $code['first_name'] . " " . $code['last_name'] . "<br />" . $displayDate;
                }

                $codePartials[] = array('code' => $code);
            }
        }


        return $codePartials;
    }

    protected function showActivationCodesTable($activationCodes)
    {
        $this->_html .= '<table id="code-table" ';

        $this->_html .= '>
							<tbody>';


        $this->_html .= $this->view->partialLoop('inventoryActivationCodeCell.phtml', $activationCodes);
        $this->_html .= '</tbody></table>';
    }
}
