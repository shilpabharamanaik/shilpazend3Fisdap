<?php

class MyFisdap_WidgetAjaxController extends Fisdap_Controller_Private
{
    public function preDispatch()
    {
        parent::preDispatch();

        // I'm guessing there will be some overarching logic here at some point
        // to test to make sure the user can edit the widget.
    }

    /**
     * This method tries to return an appropriate user entity.  Can override the
     * logged in user if passing in a uid param with a different user ID.
     */
    private function getUserEntity()
    {
        $user = \Fisdap\Entity\User::getLoggedInUser();

        // Override with a provided user ID (in case someone is masquerading as a different user...
        if ($uid = $this->_getParam('uid', false)) {
            $user = \Fisdap\EntityUtils::getEntity('User', $uid);
        }

        return $user;
    }

    public function toggleCollapseAction()
    {
        $widget = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $this->_getParam('wid', 0));
        $widget->is_collapsed = !$widget->is_collapsed;
        $widget->save();

        $this->_helper->json(true);
    }

    public function addWidgetAction()
    {
        $widgetDef = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetDefinition', $this->_getParam('wdef_id'));

        $user = $this->getUserEntity();

        if ($user) {

            // Check to see if the widget being added is unique- if it is, check to see if another
            // instance of that widget exists for this section...
            if ($widgetDef->is_unique) {
                $widgetsData = \Fisdap\EntityUtils::getRepository('MyFisdapWidgetData');

                if ($widgetsData->widgetAlreadyExistsInSection($user->id, $this->_getParam('sname'), $widgetDef->id)) {
                    $this->_helper->json(array('error' => 'This widget already exists in this section, and cannot be added again.'));
                }
            }

            $newWidget = null;

            $newWidget = new \Fisdap\Entity\MyFisdapWidgetData();

            $newWidget->is_collapsed = false;
            $newWidget->is_required = false;

            $newWidget->column_position = $this->_getParam('order');

            $newWidget->user = $user;

            $newWidget->widget = $widgetDef;

            $newWidget->section = $this->_getParam('sname');

            $newWidget->program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $user->getProgramId());

            $newWidget->is_hidden = false;
            $newWidget->save();

            $this->_helper->json(array('id' => $newWidget->id));
        } else {
            $this->_helper->json(array('error' => 'Please log in'));
        }
    }

    public function removeWidgetAction()
    {
        $widget = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $this->_getParam('wid', 0));

        if ($widget->is_required) {
            $this->_helper->json(false);
        } else {
            $widget->is_hidden = true;
            $widget->save();

            $this->_helper->json(true);
        }
    }

    public function undeleteWidgetAction()
    {
        $widget = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $this->_getParam('wid', 0));

        $widget->is_hidden = false;
        $widget->save();

        $this->_helper->json(true);
    }

    public function updateWidgetSettingsAction()
    {
        $widget = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $this->_getParam('wid', 0));
        $widget->data = serialize($this->_getParam('data', array()));
        $widget->save();

        $this->_helper->json(true);
    }

    public function renderWidgetAction()
    {
        $wid = $this->_getParam('wid', 0);
        $widgetData = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $wid);

        $html = '';

        $className = $widgetData->widget->class_name;

        if (class_exists($className)) {
            if ($className::userCanUseWidget($widgetData->id)) {
                $html = $widgetData->getWidgetClassInstance()->renderContainer();
            }
        } else {
            $html = "<div id='widget_{$wid}_container' style='border: 1px solid #FF5555; background-color: #FFCCCC; padding: 7px;'>
			Sorry, it appears that the " . $widgetData->widget->display_title . " widget is no longer
			supported.  Please contact customer support, or
			<a href='#' onclick='deleteWidget({$wid}); return false;'>click here</a> to delete this widget.
			</div>";
        }

        $this->_helper->json($html);
    }

    public function getWidgetListAction()
    {
        $user = $this->getUserEntity();

        if ($user) {
            $widgetsData = \Fisdap\EntityUtils::getRepository('MyFisdapWidgetData')->getWidgetsForSection($user->id, $this->_getParam('sname'));

            $returnData = array();

            foreach ($widgetsData as $widget) {
                if (!$widget->is_hidden) {
                    $returnData[] = $widget->id;
                }
            }

            $this->_helper->json($returnData);
        } else {
            $this->_helper->json(array('error' => 'Please log in first.'));
        }
    }

    public function rerouteAjaxRequestAction()
    {
        $widgetData = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $this->_getParam('wid', 0));

        $widgetInstance = new $widgetData->widget->class_name($widgetData->id);

        // Assume failure...
        $result = false;

        $method = $this->_getParam('fcn', '');

        if (method_exists($widgetInstance, $method) && $widgetInstance->callbackIsRegistered($method)) {
            $method = $this->_getParam('fcn');

            $result = $widgetInstance->$method($this->_getParam('data', array()));
        }

        if ($this->_getParam('html-results', 0) == 1) {
            echo $result;
            die();
        } else {
            $this->_helper->json($result);
        }
    }

    public function getAvailableWidgetsForSection()
    {
        $width = $this->_getParam('secw', 0);

        $widgetDefs = \Fisdap\EntityUtils::getRepository('MyFisdapWidgetData')->getAvailableWidgetsForSection($this->_getParam('secw', 0));

        $returnArray = array();

        foreach ($widgetDefs as $def) {
            $returnArray[] = array('id' => $def->id, 'title' => $def->display_title);
        }

        $this->_helper->json($returnArray);
    }
}
