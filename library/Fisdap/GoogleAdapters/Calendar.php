<?php

class Fisdap_GoogleAdapters_Calendar
{
	static function getAuthLink(){
		$next = getCurrentUrl();
		$scope = 'https://www.google.com/calendar/feeds/';
		$secure = false;
		$session = true;
		return Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure, $session);
	}
	
	static function authMe($username, $password)
	{		
		$client = Zend_Gdata_ClientLogin::getHttpClient($username, $password, Zend_Gdata_Calendar::AUTH_SERVICE_NAME);
		
		return $client;
	}
	
	static function outputCalendarList($client)
	{
		$gdataCal = new Zend_Gdata_Calendar($client);
		$calFeed = $gdataCal->getCalendarListFeed();
		echo '<h1>' . $calFeed->title->text . '</h1>';
		echo '<ul>';
		foreach ($calFeed as $calendar) {
			echo '<li>' . $calendar->title->text . '</li>';
		}
		echo '</ul>';
	}
	
	static function outputCalendar($client)
	{
		$gdataCal = new Zend_Gdata_Calendar($client);
		$eventFeed = $gdataCal->getCalendarEventFeed();
		echo "<ul>\n";
		foreach ($eventFeed as $event) {
			echo "\t<li>" . $event->title->text .  " (" . $event->id->text . ")\n";
			echo "\t\t<ul>\n";
			foreach ($event->when as $when) {
				echo "\t\t\t<li>Starts: " . $when->startTime . "</li>\n";
			}
			echo "\t\t</ul>\n";
			echo "\t</li>\n";
		}
		echo "</ul>\n";
	}
	
	static function createEvent ($client, $title, $desc, $duration, $start = null, $where = null, $tzOffset = '-06')
	{
		if($start == null){
			$start = new DateTime();
		}
		
		$gdataCal = new Zend_Gdata_Calendar($client);
		$newEvent = $gdataCal->newEventEntry();
	
		$newEvent->title = $gdataCal->newTitle($title);
		
		if($where != null){
			$newEvent->where = array($gdataCal->newWhere($where));
		}
		
		$newEvent->content = $gdataCal->newContent("$desc");
	
		$when = $gdataCal->newWhen();
		
		$when->startTime = $start->format('Y-m-d') . "T" . $start->format('H:i') . ":00.000" . $tzOffset . ":00";
		
		$start->add(new DateInterval('PT' . ($duration * 60) . 'S'));
		
		$when->endTime = $start->format('Y-m-d') . "T" . $start->format('H:i') . ":00.000" . $tzOffset . ":00";
		
		
		$newEvent->when = array($when);
	
		// Upload the event to the calendar server
		// A copy of the event as it is recorded on the server is returned
		$createdEvent = $gdataCal->insertEvent($newEvent);
		
		return $createdEvent->id->text;
	}
	
}