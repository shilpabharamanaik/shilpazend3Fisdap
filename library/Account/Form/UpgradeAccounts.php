<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * Form for upgrading students
 */

/**
 * @package    Account
 */
class Account_Form_UpgradeAccounts extends Fisdap_Form_Base
{
	/**
	 * @var array the decorators for the form
	 */
	protected static $_formDecorators = array(
		'FormErrors',
		array('ViewScript', array('viewScript' => "forms/upgradeAccountsForm.phtml")),
		array('Form', array('class' => 'upgrade-accounts-form')),
	);
	
	/**
	 * @var integer
	 */
	public $configuration;
	
	/**
	 * @var array
	 */
	public $students;
	
	/**
	 * @param int $userId the id of the user interacting with this form
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($configuration = 0, $students = array(), $instructors = array(), $options = null)
	{
		$this->configuration = $configuration;
		$this->students = $students;
		
		parent::__construct($options);
	}
	
	/**
	 * init method that adds all the elements to the form
	 */
	public function init()
	{
		parent::init();

		$this->setAttrib("id", "upgrade-students-form");
		$this->addJsFile("/js/library/Account/Form/upgrade-accounts.js");
		
		$configuration = new Zend_Form_Element_Hidden("configuration");
		$configuration->setDecorators(array("ViewHelper"))
					  ->setOrder(1);
		$this->addElement($configuration);
		
		$this->setDecorators(self::$_formDecorators);
	}
	
	
	public function process($post)
	{
		$students = $post['studentIDs'];
		if (count($students) == 0) {
			return "Please select a student.";
		}
		
		$errorText = "";
		foreach ($students as $studentId) {
			$student = \Fisdap\EntityUtils::getEntity("StudentLegacy", $studentId);

			if(!$post['products_' . $studentId] && !$post['downgradeProducts_' . $studentId] && !$post['reduceAttemptProducts_' . $studentId]){
				if(strlen($errorText) > 1){
					$errorText .= "<br />";
				}
				$errorText .= $student->first_name . " " . $student->last_name . " does not have any products selected.";
			}
		}

		if(strlen($errorText) > 1){
			return array('error' => $errorText, 'orderId' => NULL);
		}
		
		// get products that have multiple attempts upgradeability, this is useful for checking later
		$repo =  \Fisdap\EntityUtils::getRepository('Product');
		$productsWithAttempts = $repo->getProductsWithMoodleCourses();
		$productsWithAttemptsConfig = 0;
		$productsWithAttemptsContexts = array();
		foreach($productsWithAttempts as $product) {
			$productsWithAttemptsConfig += $product->configuration;
			foreach($product->moodle_quizzes as $moodleTest) {
				$productsWithAttemptsContexts[$moodleTest->getContext()]['products'][$product->id] = $product;
				if (!isset($productsWithAttemptsContexts[$moodleTest->getContext()]['contextConfig']) ||
						(!($product->configuration & $productsWithAttemptsContexts[$moodleTest->getContext()]['contextConfig']))) {
					$productsWithAttemptsContexts[$moodleTest->getContext()]['contextConfig'] += $product->configuration;
				}
			}
		}
		
		//Create new order for upgrade
		$order = \Fisdap\EntityUtils::getEntity("Order");
		$order->user = \Fisdap\Entity\User::getLoggedInUser();
		$order->upgrade_purchase = true;
		$order->order_type = 1;
		
		//Loop over selected students and add the products
		$warningMessages = array();
		foreach ($students as $studentId) {
			// get the products upgrade / downgrade config values
			$upgradeConfig = $downgradeConfig = $reduceConfig = 0;
			if (is_array($post['products_' . $studentId])) {
				$upgradeConfig = array_sum($post['products_' . $studentId]);
			}
			if (is_array($post['downgradeProducts_' . $studentId])) {
				$downgradeConfig = array_sum($post['downgradeProducts_' . $studentId]);
			}
			if (is_array($post['reduceAttemptProducts_' . $studentId])) {
				$reduceConfig = array_sum($post['reduceAttemptProducts_' . $studentId]);
			}


			// make sure we have at least one product selected for upgrade or downgrade otherwise don't add an orderConfig
			if ($upgradeConfig || $downgradeConfig || $reduceConfig) {
				$student = \Fisdap\EntityUtils::getEntity("StudentLegacy", $studentId);
				$serialNumber = $student->getUserContext()->getPrimarySerialNumber();
				
				// check to see if there are any "additional moodle attempts" products in the upgrade config
				// if so, make sure the user has a Moodle account established in that context
				// (can't upgrade a moodle account that doesn't exist)
				if ($upgradeConfig & $productsWithAttemptsConfig) {
					// check for moodle ID in each matched product's context
					foreach($productsWithAttemptsContexts as $context => $info) {
						if ($upgradeConfig & $info['contextConfig']) {
							$checkedIds = \Fisdap\MoodleUtils::getMoodleUserIds(array($student->user->id => $student->user->username), $context);
							if (!$checkedIds[$student->user->id]) {
								// we don't have a verified user ID in Moodle for this user in this context,
								// so we have to remove this context's products from the upgrade configuration
								$productNames = array();
								$needWarning = FALSE;
								foreach($info['products'] as $product) {
									if ($product->configuration & $upgradeConfig & $serialNumber->configuration) {
										$productNames[] = $product->name;
										$upgradeConfig -= $product->configuration;
										$needWarning = TRUE;
									}
								}
								if ($needWarning) {
									$warningMessages[] = $student->first_name . ' ' . $student->last_name . " - You cannot buy additional attempts for a student who hasn't taken the test yet.<br>";
								}
							}
						}
					}
				}
				
				// check one more time for actual products to upgrade, as some may have been dropped due to the moodle account check
				if ($upgradeConfig || $downgradeConfig || $reduceConfig) {
					//Create and add order config to order			
					$orderConfig = \Fisdap\EntityUtils::getEntity("OrderConfiguration");
					$order->addOrderConfiguration($orderConfig);
		
					//Save the rest of the order config details
					$orderConfig->upgraded_user = $student->user;

                    //check for a null response from getCurrentRoleData() -- user might have never logged in to set this
                    if (is_null($student->user->getCurrentRoleData())) {
                        //this will force a current user context to be set for the user if there isn't one already.
                        $upgraded_user_context = $student->user->getCurrentUserContext();
                    }
					
					$orderConfig->configuration = $upgradeConfig;
					$orderConfig->downgrade_configuration = $downgradeConfig;
					$orderConfig->reduce_configuration = $reduceConfig;
					$orderConfig->certification_level = $student->user->getCurrentRoleData()->getCertification();
					$orderConfig->quantity = 1;
					$orderConfig->calculateFinalPrice();
				}
			}
		}
		
		//Add order config to order and save
		$order->save();
		
		// format any warning messages
		if (count($warningMessages) == 0) {
			$warningMessages = NULL;
		} else {
			$warningMessages = implode(' ', $warningMessages);
		}
		
		return array('orderId' => $order->id, 'warning' => $warningMessages);
	}
}
