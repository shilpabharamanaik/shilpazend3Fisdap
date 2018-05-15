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
 * View Helper to display the permissions history
 * NOTE: in order for this modal to open, you'll need a trigger with an id "permissionsHistory" - see js file for details
 */

/**
 * @package Account
 */
class Account_View_Helper_PermissionsHistory extends Zend_View_Helper_Abstract
{
    protected $_html;

    public function permissionsHistory($instructorId)
    {
        $this->view->headLink()->appendStylesheet("/css/library/Account/View/Helper/permissions-history.css");
        $this->view->headScript()->appendFile("/js/library/Account/View/Helper/permissions-history.js");

        // create the dialog div
        $this->_html = '<div id="permissions-history-dialog">';

        // grab the instructor we're editing/looking at
        $instructor = \Fisdap\EntityUtils::getEntity('InstructorLegacy', $instructorId);
        $permissionsCategories = \Fisdap\EntityUtils::getRepository("Permission")->getPermissionCategories();

        // get every record of a permission change for this instructor and loop through those
        foreach ($instructor->getPermissionsHistory() as $record) {
            $this->_html .= '<div class="historyRecord">'; // open the historyRecord div

            // show the header (date/who changed it)
            $this->_html .= "<div class='recordHeader'>Set on "
                . $record->entry_time->format('F j, Y');

//            // the instructor object of the user who set this permission
//            $changingInstructor = $record->changer;
//            if ($changingInstructor) {
//                $this->_html .= " by " . $changingInstructor->user->getFullName();
//            } else {
//                $this->_html .= " by the Fisdap Robot";
//            }

            $this->_html .= " </div>";


            // step through each permission category
            foreach ($permissionsCategories as $category) {
                $addClass = ($category->id == 2) ? 'moreWidth' : '';
                $this->_html .= '<div class="permissionCat ' . $addClass . '">';
                $this->_html .= "<h4>" . $category->name . "</h4>";

                $allPermissions = \Fisdap\EntityUtils::getRepository("Permission")->getPermissionsByCategory($category);
                $permissions = \Fisdap\EntityUtils::getRepository("Permission")->getPermissions($record->permissions, true);

                // compare the permission we're looking at to the instructor's permission config
                foreach ($allPermissions as $permission) {
                    if ($permission->bit_value & $record->permissions) {
                        // display a check mark if it's there
                        $this->_html .= "<img class='check' src='/images/check.png'>" . $permission->name . "<br />";
                    } else {
                        // display the 'x' if it's not
                        $this->_html .= "<img src='/images/icons/delete.png'>" . $permission->name . "<br />";
                    }
                }

                $this->_html .= "</div>"; // close the category
            }

            $this->_html .= '<div style="clear:both;"></div>
							</div>'; // closes the history record div
        }

        $this->_html .= '<div style="clear:both;"></div>
						 </div>'; // closes the modal


        return $this->_html;
    }
}
