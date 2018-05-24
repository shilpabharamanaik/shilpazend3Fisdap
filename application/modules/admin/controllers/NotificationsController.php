<?php
use Fisdap\EntityUtils;

/**
 * primary controller for the Notification Center.
 *
 * @package
 * @subpackage Controllers
 */
class Admin_NotificationsController extends Fisdap_Controller_Staff
{
	public function init()
    {
        parent::init();	
    }
	
	public function indexAction()
	{
		$this->view->pageTitle = "Notification Center";
        $this->view->pageTitleLinkURL = "/admin/notifications/history";
        $this->view->pageTitleLinkText = "<< Back to notification history";
		$this->view->headScript()->appendFile("/js/tableSorter/jquery.tablesorter.min.js");

        $this->view->form = new Admin_Form_CreateNotification();

        //when the form is submitted, redirect to the history page
        if ($this->getRequest()->isPost()) {
            if ($this->view->form->process($this->getRequest()->getPost())) {
                $this->redirect('/admin/notifications/history');
            }
        }
    }

    public function historyAction()
    {
        //establish the initial loaded number of notifications, used in getAllNotifications
        $loadLimit = 10;
        $this->view->offset = $loadLimit;

        $this->view->pageTitle = "Notification Center";
        $this->view->headScript()->appendFile("/js/tableSorter/jquery.tablesorter.min.js");
        $this->view->headScript()->appendFile("/js/jquery.sliderCheckbox.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.sliderCheckbox.css");

        //get notifications, limit initial return
        $notificationRepo = EntityUtils::getRepository('Notification');
        $notifications = $notificationRepo->getAllNotifications(null, $loadLimit);

        // get user view data for all these notifications before we send it to the view
        foreach ($notifications as $key => $notification) {
            $viewData = $notificationRepo->getViewDataByNotification($notification['id']);
            $notifications[$key]['view_data']['open'] = $viewData[0]['view_count'];
            $notifications[$key]['view_data']['closed'] = $viewData[1]['view_count'];
        }
        $this->view->notifications = $notifications;
    }

    public function loadMoreNotificationsAction()
    {
        //get $offset and $limit from history.js, limit is set at 30 while offset changes
        $offset = $this->getParam('offset');
        $limit = $this->getParam('limit', 30);

        //get notifications, use parameters $offset and $limit from history.js
        $this->view->notifications = EntityUtils::getRepository('Notification')->getAllNotifications($offset, $limit);

        //render notifications using partialLoop
        $notificationsHtml = $this->view->partialLoop("notificationHistoryRow.phtml", $this->view->notifications);

        //return notification html via json keyed array
        $this->_helper->json(array("html"=>$notificationsHtml, "count"=>count($this->view->notifications)));
    }

    /**
     * Given POST data representing a potential
     */
    public function generateNotificationPreviewAction()
    {
        $notificationParams = [
            'title' => $this->getParam('title'),
            'message' => $this->getParam('message'),
        ];

        if ($notificationTypeId = $this->getParam('notificationType')) {
            $notificationType = EntityUtils::getEntity("NotificationType", $notificationTypeId);
            $notificationParams['class'] = $notificationType->class;
            $notificationParams['notification_type_name'] = $notificationType->name;
        }

        $this->view->addScriptPath(APPLICATION_PATH . "/views/scripts/");
        $this->_helper->json($this->view->partial("notificationPopup.phtml", $notificationParams));
    }

    public function toggleNotificationAction()
    {
        $notification_id = $this->_getParam('notification_id');
        $active  = $this->_getParam('active');
        $notification = EntityUtils::getEntity("Notification", $notification_id);
        $notification->active = $active;
        $notification->save();

        $this->_helper->json($notification->id);
    }


}