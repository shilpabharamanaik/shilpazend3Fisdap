<?php

/* * **************************************************************************
 *
 *         Copyright (C) 1996-2011.  This is an unpublished work of
 *                          Headwaters Software, Inc.
 *                             ALL RIGHTS RESERVED
 *         This program is a trade secret of Headwaters Software, Inc.
 *         and it is not to be copied, distributed, reproduced, published,
 *         or adapted without prior authorization
 *         of Headwaters Software, Inc.
 *
 * ************************************************************************** */

/**
 * View helper for Notification Center Popup in the nav bar
 *
 * @author pwolpers
 */
class Fisdap_View_Helper_NotificationNavPopup extends Zend_View_Helper_Abstract
{
    public function notificationNavPopup($notifications) {

        $this->view->addScriptPath(APPLICATION_PATH . "/views/scripts/");

        $popup = " | "
            . "<a href='http://www.fisdap.net/support/system-status' target='_blank' title='Open system status in a new window.'>"
            . "<img src='/images/icons/bell-icon.png' class='notification-bell' /></a>"
            . "<div class='notification-popup-container-main'>";

        $popup .= $this->view->partialLoop("notificationPopup.phtml", $notifications);

        $popup .= "</div>";

        return $popup;
    }

}
