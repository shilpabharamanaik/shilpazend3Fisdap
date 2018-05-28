<?php
namespace Fisdap\RewardPoint;

class RewardPointService
{
	public static $old_projects = "-1,1,4,5,7,8,9";

	// point multiplier values...
	public static $ITEM_CONTRIBUTION = 2;
	public static $ITEM_VALIDATED = 4;
	public static $INDIVIDUAL_REVIEW = 3;
	public static $CONSENSUS_REVIEW = 15;

	public function calculatePoints($type, $programid){
		$query = '';
		$multiplier = 0;

		switch($type){
			case 'donated':
					$query = "SELECT count(*) as count " .
							"FROM fisdap2_users U, AuthorData AD, " .
							"Asset_def A, Item_def I, InstructorData ID " .
							"WHERE U.id = ID.user_id " .
							"AND (ID.ProgramId = " . $programid . " OR ID.ProgramId =" . $programid * -1 . ") " .
							"AND U.id=AD.UserAuth_id " .
							"AND AD.AssetDef_id=A.AssetDef_id " .
							"AND A.PointEligibility & 1 " .
							"AND A.Data_id=I.Item_id " .
							"AND I.Project_id NOT IN (" . self::$old_projects . ") " .
							"AND U.id NOT IN (SELECT user_id FROM StaffData)";

					$multiplier = self::$ITEM_CONTRIBUTION;
				break;
			case 'validated':
					$query = "SELECT count(*) as count " .
							"FROM fisdap2_users U, AuthorData AD, " .
							"Asset_def A, Item_def I, InstructorData ID " .
							"WHERE U.id = ID.user_id " .
							"AND (ID.ProgramId = " . $programid . " OR ID.ProgramId =" . $programid * -1 . ") " .
							"AND U.id=AD.UserAuth_id " .
							"AND AD.AssetDef_id=A.AssetDef_id " .
							"AND A.PointEligibility & 2 " .
							"AND A.Data_id=I.Item_id " .
							"AND I.ValidStatus=3 " .
							"AND I.Project_id NOT IN (" . self::$old_projects . ") " .
							"AND U.id NOT IN (SELECT user_id FROM StaffData)";

					$multiplier = self::$ITEM_VALIDATED;
				break;
			case 'individual_review':
					$query = "SELECT count(*) as count " .
							"FROM fisdap2_users U, ReviewAssignmentData RAD, " .
							"Asset_def A, Item_def I, InstructorData ID " .
							"WHERE U.id = ID.user_id " .
							"AND (ID.ProgramId = " . $programid . " OR ID.ProgramId =" . $programid * -1 . ") " .
                            "AND U.id=RAD.UserAuth_id " .
							"AND RAD.DateReviewReceived!='0000-00-00' " .
							"AND RAD.Active=1 " .
							"AND RAD.AssetDef_id=A.AssetDef_id " .
							"AND A.Data_id=I.Item_id " .
							"AND I.Project_id NOT IN (" . self::$old_projects . ") " .
							"AND U.id NOT IN (SELECT user_id FROM StaffData)";

					$multiplier = self::$INDIVIDUAL_REVIEW;
				break;
			case 'consensus_review':
					$query = "SELECT count(*) as count " .
							"FROM ScheduledSessionSignups signup, ScheduledSessions session, InstructorData ID, fisdap2_users U " .
							"WHERE signup.Instructor_id = ID.Instructor_id " .
                            "AND (ID.ProgramId = " . $programid . " OR ID.ProgramId =" . $programid * -1 . ") " .
							"AND signup.Attended=1 " .
                            "AND ID.user_id = U.id " .
							"AND signup.ScheduledSession_id = session.ScheduledSession_id " .
							"AND session.Type=1 " .
                            "AND U.id NOT IN (SELECT user_id FROM StaffData)";
					$multiplier = self::$CONSENSUS_REVIEW;

				break;
			case 'bonus':
				$query = "SELECT sum(Amount) as count ".
						  "FROM POPTransactions ".
						  "WHERE program_id=$programid ".
						  "AND amount>0 ".
						  "AND transaction_date<='" . date('Y-m-d') . "'";
				$multiplier = 1;
				break;
			case 'spent':
				$query = "SELECT sum(Amount) as count ".
						  "FROM POPTransactions ".
						  "WHERE program_id=$programid ".
						  "AND amount<0 ".
						  "AND transaction_date<='" . date('Y-m-d') . "'";
				$multiplier = 1;
				break;
		}

		if (!empty($query)) {
			return $this->executeRawQuery($query) * $multiplier;
		}else {
			return 0;
		}
	}

	private function executeRawQuery($query, $countOnly=true){
		$conn = \Fisdap\EntityUtils::getEntityManager()->getConnection();
		$result = $conn->query($query);

		$res = $result->fetch();

		if($countOnly){
			return $res['count'];
		}else{
			return $res;
		}
	}

	public function getDiscounts($programid, $active, $rewards){
		$select = "SELECT Type,StartDate,EndDate,PercentOff,Configuration ".
				"FROM PriceDiscount ".
				"WHERE Program_id=$programid ".
				"AND PercentOff>0 ";
		if($active) {
			$today = Date('Y-m-d');
			$select .= "AND StartDate <='$today' ".
					"AND EndDate >= '$today' ";
		}
		if($rewards) {
			$select .= "AND Rewards = 1 ";
		}
		$select .= "ORDER BY EndDate";

		//echo "<br>$select<br>";
		$conn = \Fisdap\EntityUtils::getEntityManager()->getConnection();
		$query = $conn->query($select);

		$result = array();

		while($row = $query->fetch()){
			$result[] = $row;
		}

		//round the percentages
		foreach ($result as $key => $discount) {
			$percent_off = round($discount['PercentOff']);
			$result[$key]['PercentOff'] = $percent_off;
		}

		return $result;
	}
}