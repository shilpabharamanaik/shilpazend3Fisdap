<?php

class MyFisdap_Widgets_ReviewSectionSignup extends MyFisdap_Widgets_Base
{
    protected $registeredCallbacks = array('joinSection', 'unsubscribeSection');
    
    public function render()
    {
        $html = "";
        
        $html .= $this->renderSessionTable();
        
        $html .= $this->renderCustomReviewTable();
        
        return $html;
    }
    
    private function renderCustomReviewTable()
    {
        $user = $this->getWidgetUser();
        
        $reviewItems = \Fisdap\EntityUtils::getRepository('ScheduledSessionsLegacy')->getCustomReviewItems($user->id);

        if ($reviewItems->rowCount() > 0) {
            $html = "
				<h2>
					Independent Reviews
				</h2>
				<div class='review-widget-text'>
					It looks like Mike has assigned you some test items to review online.  Thanks for helping!
				</div>
				<br />
			";
            
            $html .= "
				<table class='review-table'>
					<tr class='review-table-head'>
						<th class='review-center'>Item #</td>
						<th class='review-center'>Due</td>
					</tr>
			";
            
            foreach ($reviewItems as $item) {
                $reviewSignupLink = "shift/evals/item_review.php@action=display@mode=assignment@assignment_id=" . $item['ReviewAssignment_id'];
                $realReviewLink = "/oldfisdap/redirect?loc=" . $reviewSignupLink;
                
                $html .= "<tr>";
                $html .= "<td class='review-center'><a href='$realReviewLink'>{$item['Data_id']}</a></td>";
                $html .= "<td class='review-center'>{$item['DateReviewDue']}</td>";
                $html .= "</tr>";
            }
            
            $html .= "</table>";
            
            return $html;
        }
        
        return '';
    }
    
    private function renderSessionTable()
    {
        $user = $this->getWidgetUser();
        
        $reviewData = $this->getUpcomingSessions();
        
        $html = "
			<h2>
				Group Reviews
			</h2>
			<div class='review-widget-text'>
				Learn how to validate an exam, help us review our tests, and earn Rewards Points
				toward discounts.  Plus, participation helps you with accreditation.  Sign up for a group
				review teleconference! (Sessions usually last 1 hour; \"Quickie\" sessions are 30 minutes).
			</div>
			
			<script>
				function joinEvent(id){
					data = {
						sessionId: id
					}
					routeAjaxRequest({$this->widgetData->id}, 'joinSection', data, function(){
						reloadWidget({$this->widgetData->id});
					});
				}
				
				function unsubscribeEvent(id){
					data = {
						signupId: id
					}
					routeAjaxRequest({$this->widgetData->id}, 'unsubscribeSection', data, function(){
						reloadWidget({$this->widgetData->id});
					});
				}
			</script>
		";
        
        if (count($reviewData) == 0) {
            return $html .= "There are no upcoming group reviews.";
        }
        
        $html .= "
			<table class='review-table'>
				<tr class='review-table-head'>
					<th class='review-center'>Join</td>
					<th class='review-center'>Availability</td>
					<th class='review-center'>Date</td>
					<th class='review-center'>Time <br />(Central)</td>
					<th class='review-center'>Topic</td>
				</tr>
		";
        
        foreach ($reviewData as $data) {
            // Can be used a boolean to track whether the user is assigned to this review or not...
            $signupId = \Fisdap\EntityUtils::getRepository('ScheduledSessionsLegacy')->userAlreadySubscribed($user->getCurrentRoleData()->id, $data['id']);
            
            //Display a full session
            if ($data['total_slots'] == $data['used_slots']) {
                
                //If we're signed up, display a drop link, otherwise display a disabled checkbox
                if ($signupId) {
                    $html .= "<tr class='review-assigned-row'>";
                    $html .= "<td class='review-center'><a href='#' onclick='unsubscribeEvent({$signupId}); return false;'>Drop</a></td>";
                } else {
                    $html .= "<tr>";
                    $html .= "<td class='review-center'><input type='checkbox' disabled='DISABLED' /></td>";
                }
                
                $html .= "
						<td class='session-review-full-text review-center'>FULL</td>
						<td class='session-review-disabled-text review-center'>{$data['date']->format('m-d-Y')}</td>
						<td class='session-review-disabled-text review-center'>{$data['time']}</td>
						<td class='session-review-disabled-text'>{$data['topic']}</td>
				";
            } else {
                //If we're signed up, display a drop link, otherwise display a join checkbox
                if ($signupId) {
                    $html .= "<tr class='review-assigned-row'>";
                    $html .= "<td class='review-center'><a href='#' onclick='unsubscribeEvent({$signupId}); return false;'>Drop</a></td>";
                } else {
                    $html .= "<tr>";
                    $html .= "<td class='review-center'><input type='checkbox' onclick='joinEvent({$data['id']})' /></td>";
                }
                
                // Print out the number of open/closed slots...
                $html .= "<td class='review-center review-slots'>";
        
                for ($i=1; $i<=$data['total_slots']; $i++) {
                    if ($i == 6) {
                        $html .= "<img src='/images/icons/seat_plus.png' />";
                        break;
                    } else {
                        if ($i <= $data['used_slots']) {
                            $html .= "<img src='/images/icons/seat_closed.png' />";
                        } else {
                            $html .= "<img src='/images/icons/seat_open.png' />";
                        }
                    }
                }
                
                $html .= "</td>";
        
                $html .= "<td class='review-center'>" . $data['date']->format('m-d-Y') . "</td>";
                $html .= "<td class='review-center'>" . $data['time'] . "</td>";
                $html .= "<td>" . $data['topic'] . "</td>";
            }
        }
        
        $html .= "</tr>";
        $html .= "</table>";
        
        return $html;
    }
    
    public function getUpcomingSessions()
    {
        $repos = \Fisdap\EntityUtils::getRepository('ScheduledSessionsLegacy');
        $sessions = $repos->getUpcomingSessions();
        
        $returnData = array();
        
        foreach ($sessions as $session) {
            $atom = array();
            
            $atom['id'] = $session->id;
            $atom['total_slots'] = $session->total_slots;
            $atom['date'] = $session->date;
            $atom['time'] = $session->start_time;
            $atom['topic'] = $session->topic;
            $atom['used_slots'] = $repos->getUsedSlotsCount($session->id);
            $atom['open_slots'] = $atom['total_slots'] - $atom['used_slots'];
            
            $returnData[] = $atom;
        }
        
        usort($returnData, array('self', 'sortByDate'));
        
        return $returnData;
    }
    
    public static function sortByAvailability($a, $b)
    {
        if ($a['open_slots'] == $b['open_slots']) {
            return 0;
        } else {
            return ($a['open_slots'] < $b['open_slots'])? 1 : -1;
        }
    }
    
    public static function sortByDate($a, $b)
    {
        $a1 = $a['date']->format('Ymd') . " " . $a['time'] . " " . $a['topic'];
        $b1 = $b['date']->format('Ymd') . " " . $b['time'] . " " . $b['topic'];
        
        if ($a1 == $b1) {
            return 0;
        } else {
            return ($a1 > $b1)? 1 : -1;
        }
    }
    
    public function joinSection($data)
    {
        $user = $this->getWidgetUser();
        
        $signup = new \Fisdap\Entity\ScheduledSessionSignupsLegacy();
        $signup->instructor = $user->getCurrentRoleData();
        $signup->scheduled_session = \Fisdap\EntityUtils::getEntity('ScheduledSessionsLegacy', $data['sessionId']);
        $signup->attended = -1;
        $signup->cant_come = 0;
        $signup->notes = '';
        
        $signup->save();
        
        return true;
    }
    
    public function unsubscribeSection($data)
    {
        $signup = \Fisdap\EntityUtils::getEntity('ScheduledSessionSignupsLegacy', $data['signupId']);
        $signup->cant_come = 1;
        $signup->save();
        
        return true;
    }
    
    public function getDefaultData()
    {
        return array();
    }
    
    public static function userCanUseWidget($widgetId)
    {
        // Only instructors get this widget...
        $user = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $widgetId)->user;
        
        return $user->isInstructor();
    }
}
