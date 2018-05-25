<?php

use Fisdap\RewardPoint\RewardPointService;

class MyFisdap_Widgets_Rewards extends MyFisdap_Widgets_Base
{
    /**
     * @var RewardPointService
     */
    protected $rewardPointService;

    public function __construct($widgetId)
    {
        parent::__construct($widgetId);

        $this->rewardPointService = new RewardPointService();
    }

	public function render(){
		$points = $this->calculatePoints();
		
		$pointPercent = 0;
		
		if($points < 50){
			$pointPercent = 0;
		}elseif($points < 100){
			$pointPercent = 10;
		}elseif($points < 300){
			$pointPercent = 25;
		}elseif($points < 600){
			$pointPercent = 50;
		}elseif($points < 1000){
			$pointPercent = 75;
		}else{
			$pointPercent = 100;
		}
		
		$html = "";
		
		$html .= "
			<div class='grid_12'>
				<div class='grid_6'>
					<h2>Current Discounts</h2>
					<div class='rewards_block'>
						<div class='rewards_text'>
		";
		
		$discounts = $this->getDiscountText();
		if (count($discounts) > 0) {
			foreach ($discounts as $discount) {
				$html .= "<div class='reward_discount'>$discount</div>";
			}
		} else {
			$html .= "<div class='reward_discount'>No active discounts!</div>";
		}
		
		$html .= "
							<a href='/oldfisdap/redirect?loc=testing/prog_pop_summary.html'>View History</a>
						</div>
					</div>
					
					<h2>Earn Points</h2>
					<div class='rewards_block'>
						<div class='rewards_text'>
							<a href='/oldfisdap/redirect?loc=OSPE/ContributorTraining/index.html'>Contribute Test Items</a> <br />
							<a href='/exchange/scenario/list/'>Contribute Scenarios</a> <br />
							<a href='/oldfisdap/redirect?loc=testing/item_status.php'>View My test Items</a> <br />
							<a href='/oldfisdap/redirect?loc=testing/prog_pop_summary.html'>View History</a> <br />
						</div>
					</div>
				</div>
				<div class='grid_6'>
					<div class='rewards_block'>
					<h2>Reward Points</h2>
						<div class='rewards_text'>
							<div>You currently have:</div>
							<div class='rewards_points_summary_text'>
								<span class='rewards_points_text_big'>{$points}</span> points
							</div>
							
							<div class='reward_bar_background'>
								<div class='reward_bar_foreground' style='width: {$pointPercent}%;'></div>
							</div>
							
							<br />
							
							<div>
								That's enough for <span class='rewards_points_text_big'>{$pointPercent}%</span> off 
								Fisdap Study Tools or Testing accounts for one year.  Call 651.690.9241 to redeem your points.
							</div>
						</div>
					</div>
				</div>
				<span class='clear'></span>
			</div>
			<span class='clear'></span>
		";
		
		return $html;
	}
	
	private function calculatePoints(){
		$user = $this->getWidgetUser();
		$programid = $this->getWidgetProgram()->id;
		
		$totalPoints = 
			$this->rewardPointService->calculatePoints('donated', $programid) +
            $this->rewardPointService->calculatePoints('validated', $programid) +
            $this->rewardPointService->calculatePoints('individual_review', $programid) +
            $this->rewardPointService->calculatePoints('consensus_review', $programid) +
            $this->rewardPointService->calculatePoints('bonus', $programid) +
            $this->rewardPointService->calculatePoints('spent', $programid)
		;
		
		return $totalPoints;
	}
	
	private function getDiscountText(){
		$progId = $this->getWidgetProgram()->id;
		
		$discounts = $this->rewardPointService->getDiscounts($progId, true, false);
		
		$discountReturn = array();
		
		foreach($discounts as $discount){
			if (!($discount['Configuration'] & 2) && $discount['PercentOff'] > 0) {
				$acct_type = $discount['Type'];
				$prod_desc = $this->getProductDescription($discount['Configuration']);
				
				if($acct_type != 'All'){
					$discountReturn[] = $discount['PercentOff']."% off $prod_desc for $acct_type accounts";
				}else{
					$discountReturn[] = $discount['PercentOff']."% off $prod_desc for all accounts";
				}
			}
		}
		
		return $discountReturn;
	}
	
	private function getProductDescription($config){
		$description = array();
		
		if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'tracking')) {
			$description[] = 'Tracking';
		}
		if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'pda')) {
			$description[] = 'PDA';
		}
		if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'scheduler')) {
			$description[] = 'Scheduler';
		}
		if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'testing')) {
			$description[] = 'Testing';
		}
		if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'prep')) {
			$description[] = 'Study Tools (paramedic)';
		}
		if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'preceptortraining')) {
			$description[] = 'Clinical Educator Training';
		}
		if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'emtb_study_tools')) {
			$description[] = 'Study Tools (basic)';
		}
		if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'emtb_comprehensive_exams')) {
			$description[] = 'Comprehensive Exams (basic)';
		}
		if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'para_comprehensive_exams')) {
			$description[] = 'Comprehensive Exams (paramedic)';
		}
		if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'emtb_unit_exams')) {
			$description[] = 'Unit Exams (basic)';
		}
		if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'para_unit_exams')) {
			$description[] = 'Unit Exams (paramedic)';
		}
		
		return implode(', ', $description);
	}
	
	public function getDefaultData(){
		return array();
	}
	
	public static function userCanUseWidget($widgetId){
		// Only instructors should be able to use this widget.
		
		$user = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $widgetId)->user;
		
		return $user->isInstructor();
	}
}