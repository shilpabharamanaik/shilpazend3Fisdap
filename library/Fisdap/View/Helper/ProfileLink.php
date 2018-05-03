<?php

use Fisdap\Api\Users\CurrentUser\CurrentUser;


/**
 * ProfileLink helper
 *
 * Call as $this->profileLink() in your layout script
 */
class Zend_View_Helper_ProfileLink extends Zend_View_Helper_Abstract
{
	/**
	 * @var CurrentUser
	 */
	private $currentUser;


	public function __construct()
	{
		$this->currentUser = Zend_Registry::get('container')->make(CurrentUser::class);
	}


	public function profileLink()
	{
		$this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/profile-link.js");
		$this->view->headScript()->appendFile("/js/signaturePad/assets/jquery.signaturepad.min.js");

		if ($this->currentUser->user()) {

            $notifications = $this->view->notificationNavPopup(\Fisdap\EntityUtils::getRepository('Notification')
				->getUnviewedNotificationsByUserContext($this->currentUser->context()));

            $logoutLink = "/login/logout";
            $logoutText = "Logout";

            // Check to see if this visitor is masquerading as another user
			$sess = new Zend_Session_Namespace('fisdap.auth');
			if (count($sess->identities) > 1) {
				$names = $sess->identities;
				array_shift($names);
				$username = "<img src='/images/masquerade.png' class='masquerade' alt='masquerading as...' title='masquerading as..' /> -> "
                    . implode(' -> ', $names);
				$logoutLink = "/login/logout/legacyUnmask/true";
                $logoutText = "Logout of {$this->currentUser->user()->getUsername()}&#39;s account";
                $masqText = "";
            } else if ($this->currentUser->user()->isStaff()) { // check to see if this user has authorization to masquerade
				$this->view->headScript()->appendFile("/js/jquery.chosen.relative.js");
				$this->view->headLink()->appendStylesheet("/css/jquery.chosen.css");
				$username = $this->hasGhostAccount() ? $this->currentUser->user()->getEmail() : $this->currentUser->user()->getUsername();
				$gearsText = " | <a href='#'><img src='/images/icons/gear_gray_small.png' id='staff-settings'  alt='settings' title='settings' /></a>";
				$masqText = " | <a href='/login/masquerade' title='Masquerade as another user'><img src='/images/masquerade.png' class='masquerade' alt='masquerade' title='masquerade' /></a> ";
				$programOptions = \Fisdap\Entity\ProgramLegacy::getFormOptions();				
				$programSelectText = " | " . $this->view->formSelect("staff_program", $this->currentUser->context()->getProgram()->getId(), array(), $programOptions);
				//$this->view->jQuery()->addOnLoad("");
			} else {
                $gearsText = " | " . $this->view->contextSwitcher($this->currentUser->context());
				$username = $this->hasGhostAccount() ? $this->currentUser->user()->getEmail() : $this->currentUser->user()->getUsername();
            }

            if ($this->currentUser->user()->hasPermission("Edit Student Accounts")) {
                $studentSearch = " | "
                               . "<form class='header-search-form' id='student-search' action='JavaScript:postSearchForm()' method='POST' autocomplete='off'>"
                               . "<input class='header-search-box fancy-input' type='text' id='search-string' name='search-string'></form>"
                               . "<img src='/images/icons/magnifying-glass-dark-gray.png' class='magnifying-glass' title='Search for students' />";
            }


			$ret = "Hi, <span class='user_first_name'>{$this->currentUser->user()->getFirstName()}"
				 . "</span>! (<span class='user_name'>{$username}</span>) ";
			$ret .= $masqText;
			$ret .= $gearsText;
			$ret .= $programSelectText;

            $ret .= $studentSearch;

            $ret .= $notifications;

			$ret .= " | "
                 . "<a class='logout' style='font-weight: 900;' href='$logoutLink'>"
                 . "<img src='/images/icons/logout-orange-20px.png' class='logout-img' title='$logoutText' /></a>";

			return $ret;
		}
		
		return "<span class='user_first'><a href='/login'>Login</a></span>";
	}


	private function hasGhostAccount()
	{
		return $this->currentUser->user()->getUsername() === $this->currentUser->user()->getLtiUserId()
			|| $this->currentUser->user()->getUsername() === $this->currentUser->user()->getPsgUserId();
	}
}
