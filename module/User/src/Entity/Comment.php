<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;


/**
 * A base class for all Fisdap entities to extend from.
 *
 * @Entity
 * @Table(name="fisdap2_comments")
 * @HasLifecycleCallbacks
 * 
 * @author Maciej Bogucki
 *
 * @todo implement hiding comments from certain users
 * currently disabled: Entity(repositoryClass="\Fisdap\Entity\DnadRepository")
 */
class Comment
{
	const TABLES_SHIFTS = 'shifts';
	const TABLES_RUNS = 'runs';
	
	const TABLE_PREFIX = 'fisdap2';

	protected static $htNl = "\n";
	
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @Column(type="integer")
	 */
	protected $parent_id=0;
	
	/**
	 * Table comment is for
	 * @Column(name="`table`", type="string")
	 *
	 * , columnDefinition="ENUM('shifts', 'runs')"
	 */
	protected $table;

	public function set_table($table)
	{
		// only allow enum values
		if (!in_array($table, array(
			self::TABLES_SHIFTS,
			self::TABLES_RUNS)
			))
		{
			throw new \Fisdap_Exception_InvalidArgument("Invalid table $table");
		}
		$this->table = $table;
	}
	
	/**
	 * id in table the comment is for
	 * @Column(type="integer")
	 */
	protected $table_data_id;
	
	/**
     * @Column(type="boolean")
     */
    protected $soft_deleted = false;
	protected $last_soft_deleted = null;
	
	/**
	 *	Setter for soft_deleted
	 */
	public function set_soft_deleted($value)
	{
		// save original value of soft_deleted
		if (is_null($this->last_soft_deleted)) {
			$this->last_soft_deleted = $this->soft_deleted;
		}
		
		$this->soft_deleted = $value;
	}
	
	/** 
	 * @ManyToOne(targetEntity="User")
	 * @JoinColumn(name="user_id", referencedColumnName="id")
	 */
	protected $user;
	
	/**
	 * @Column(type="text")
	 */
	protected $comment;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $instructor_viewable_only=0;
	
	/**
	 * @var \DateTime
	 * @  Column(type="datetime", nullable=true)
	 */
	//protected $deleted_time;
	
        
	/**
	 * @Column(type="datetime")
	 */
	protected $updated;
        
	/**
	 * @Column(type="datetime")
	 */
	protected $created;

	/**
	 * @PreUpdate
	 */
	public function onSoftDeleteAndUndelete()
	{
		// keep: temporarily disables.. something else broke it... set last_deleted field if soft_deleted field changed
		//if (!is_null($this->last_soft_deleted) && $this->last_soft_deleted !== $this->soft_deleted) {
		//		$this->deleted_time = new \DateTime();
		//}
	}

	protected static $loadSoftDeletedRows = false;
		
	/**
	 *	Enables / disables loading of soft_deleted rows
	 */
	public static function loadSoftDeletedRows($value) {
		self::$loadSoftDeletedRows = (bool) $value;
	}	
	
	/**
	 *	Query builder base
	 */
	protected static function getCommentQueryBuilder($table, $dataId)
	{
		//$builder = $this->getQueryBuilder();
		//$builder = \Fisdap\EntityUtils::getEntityManager()->createQueryBuilder();
		$entityManager = EntityUtils::getEntityManager();
		
		$builder = $entityManager->createQueryBuilder();
		
		$builder->setParameter('table', $table);
		$builder->setParameter('dataId', $dataId);
		
		$builder->select('c')
				->from('\Fisdap\Entity\Comment', 'c')
				->where('c.table_data_id = \'' . $dataId . "'")
				->andWhere('c.table = \'' . $table . "'")
				->orderBy('c.created', false);
				
		return $builder;
	}
	
	/**
	 *	@todo DNAD
	 *	@todo implement: check permissions for viewingUserId
	 *	@param string $table
	 *	@param integer $dataId key id for $table
	 *	@param mived $viewintUserId:
	 *		if boolean	use/don't user currently logged in user
	 *		otherwise	expects user entity
	 */
	public static function getUserViewableComments($table, $dataId, $viewingUser=true) //getUserVisibleComments
	{
		// determine user
		$user = User::getUser($viewingUser);
		
		$entityManager = EntityUtils::getEntityManager();
		
		$builder = self::getCommentQueryBuilder($table, $dataId);
		
		$deletedAfterObj = new \DateTime('7 days ago');
		$deletedAfter = $deletedAfterObj->format("Y-m-d H:i:s");
		
		//		this wasn't working, don't know why:
		//$builder->andWhere("c.created > ?1");
		//$builder->setParameter(1, $deletedAfter);
		//		neither this:
		//$builder->setParameters(array(
		//	'deletedafter' => $deletedAfter));
		//$builder->andWhere("c.created > :deletedafter");

		if (!self::$loadSoftDeletedRows || !$user) {
			$builder->andWhere('c.soft_deleted = 0');
		} else {
			// show recent comments deleted
			$subQuery = 'c.soft_deleted = 0'
				. ' OR ('
				. ' c.soft_deleted = 1'
				. " AND c.updated > '$deletedAfter'"
				//. " AND c.user_id = '" . $user->id . "'" // show recent comments deleted only by viewingUser
				. ' )';
			$builder->andWhere($subQuery);
		}
		
		// filter out comments hidden for viewing user
		if ($user && $user->getCurrentRoleName()!='instructor') {
			$builder->andWhere('c.instructor_viewable_only=0');
		}
		
		//$dql = $builder->getDql();
		//var_dump(array($dql, $query, $subQuery)); exit;
		
		$query = $entityManager->createQuery($builder);
		
		$comments = $query->getResult();
		
		return $comments;
	}
	
	public static function getUsersCommentingOnPost($table, $dataId)
	{
		$entityManager = EntityUtils::getEntityManager();
		
		$builder = self::getCommentQueryBuilder($table, $dataId);
		$builder->select('DISTINCT(c.user_id) as user_id, c.id');
		
		$query = $entityManager->createQuery($builder);
		$results = $query->getResult();
		
		$userEntities = EntityUtils::getEntitiesForQueryResults($results, 'User'); //,'user_id'
		//to get just user ids do this instead: $inArray = \Util_Db::organizeByField($results);	return $inArray['user_id'];
		
		return $userEntities;
	}

	
	/**
	 * @param string $table
	 * @param integer $dataId
	 * @param mixed $user (Entity Object)
	 * @param mixed $whichAreNew:
	 * 	datetime (show all after)
	 * 	array of comments
	 */ 
	//public static function getNewlyPostedComments($table, $dataId, $user, $whichAreNew)
	//{
	//	$comments = self::getUserViewableComments($table, $dataId, $user);
	//	return self::generateNewCommentsNotification($comments, $whichAreNew);
	//}
	
	/**
	 *	Generates notifications for all users on the 'feed'
	 */
	public static function generateNotifications($table, $dataId, $whichAreNew)
	{
		// get comments for the 'feed'
		$comments = self::getUserViewableComments($table, $dataId);
		
		// find all users viewing the 'feed'
		$users = array();
		$userIds = array();
		
		foreach ($comments as $comment) {
			if (!in_array($comment->user->id, $userIds)) {
				$userIds[] = $comment->user->id;
				$users[] = $comment->user;
			}
		}
	
		$testingAllReports = '';
		// run report for each user
		foreach ($users as $user) {
			$report = self::generateNotificationReportForUser($comments, $user, $whichAreNew);

			//$sendToDisplay = 'To: (' . $user->id . ') ' . $user->first_name . ' ' . $user->last_name . '<' . $user->email . '>' . self::$htNl . self::$htNl;
			//$testingAllReports .= self::$htNl . self::$htNl . self::$htNl . $sendToDisplay . $report;
		
			if (!$report) {
				continue;
			}
			
			// send email report
		
			// @todo user real email -> here: testing email only
			$options = array(
				'To' => array(307), //, 'maciekmn@gmail.com' => "Maciej (Magic) Bogucki"),
				//'From' => 307,
				'Subject' => 'FISDAP: New comment on ' . $table . '(' . $dataId . ') by ' . $user->first_name . ' ' . $user->last_name . '..',
			);
			
			\Fisdap\Mail::sendMail($options, $subject , $sendToDisplay . $report);
		}
		
		return $testingAllReports;
	}
	
	/**
	 *
	 *	@param array(Entity::Comment)
	 *	@param Entity::User
	 *	@return string Report for emailing
	 */
	public static function generateNotificationReportForUser($comments, $user, $whichAreNew)
	{
		// find new comments for user
		$newComments = array();
		foreach ($comments as $comment) {
			if ($comment->isNew($user, $whichAreNew)) {
				$newComments[] = $comment;
			}
		}
		
		if (empty($newComments)) {
			return false;
		}
		
		// new comments first
		foreach ($newComments as $comment) {
			$body .= $comment->user->first_name . ' ' . $comment->user->last_name
				  . ' commented on ' . $comment->table . ' (' . $comment->table_data_id . '):' . self::$htNl
				  . $comment->showComment();
		}
		
		// heading for all comments
		$body .= str_repeat('-', 80)
			  . self::$htNl . self::$htNl .'All comments on ' . $comment->table
			  . ' (' . $comment->table_data_id . '):' . self::$htNl . self::$htNl;
		
		// all comments
		foreach ($comments as $comment) {
			$body .= $comment->showComment();
		}
		
		return $body;
	}
	
	
	/**
	 *	Generates report given comment Entities and which to filter as 'new'
	 *	@param array(Entity) $comments
	 *	@param Entity::User
	 *	@param mixed $whichAreNew
	 *	@todo by $whichAreNew by datetime
	 */
	/*public static function generateNewCommentsNotification($comments, $userViewing, $whichAreNew)
	{
		$newComments = array();
		
		// find new comments
		if (is_array($whichAreNew)) {	// by list given
			foreach ($comments as $comment) {
				if (in_array($comment->id, $whichAreNew)) {
					$newComments[] = $comment;
				}
			}
		} else {						// by datetime 
			// @todo
		}
		
		// no new comments = no notification needed
		if (!$newComments) {
			return array();
		}
		
		$out = '';
		foreach($newComments as $comment) {
			if ($out) {
				$out .= self::$htNl;
			}
			$out .= $comment->showComment();
		}
		return $out;
	}*/
	
	/**
	 *	@param criteria datetime or list of comment ids
	 *	@return boolean isNew for notification purposes (comment)
	 *	@todo by date ($whichAreNew is datetime)
	 */
	public function isNew($userViewing, $whichAreNew)
	{
		// \Util_Dev::vard('UserId: '.$userViewing->id.' Comment: User: '.$this->user_id . '|'.$this->user->id.' cId: ' .$this->id, $this->id);
		
		// viewing user's comment?
		if ($this->user->id == $userViewing->id) {
			return false;
		}
		
		// new comment?
		if (is_array($whichAreNew)) {	// by list given
			return (in_array($this->id, $whichAreNew));
		} else {
			// @todo by date ($whichAreNew is datetime)
			return false;
		}
		
		return false;
	}

	/**
	 *	Shows single comment
	 */
	public function showComment()
	{
		// date & user:
		\Util_Dev::vard('showComment:' . $this->id . ' UserId: '.$this->user->id. ' ' . $this->user->first_name . ' ' . $this->user->last_name);
		$heading = '(' . $this->created->format('n/j/Y H:i:s') . ') ' . $this->user->first_name . ' ' . $this->user->last_name . ':' . self::$htNl;
		return $heading . $this->comment . self::$htNl . self::$htNl;
	}
	
	/**
	 *	Get all users posting on specific 'post' ex. shift #12345
	 */
	public static function getUsersOnPost($table, $dataId)	//getUsersForDataId
	{
		$comments = self::getUserViewableComments($table, $dataId);
		
		$users = array();
		
		$users = array();
		$user_ids = array();
		foreach ($comments as $id => $comment) {
			if (!in_array($comment->user->id, $user_ids)) {
				$user_ids[] = $comment->user->id;
				$users[] = $comment->user;
			}
		}
		
		return $users;
	}
	
	protected $otherDbFields = array('editable', 'deletable', 'viewing_user');
	
	/**
	 *	Enabling getters/setters on business logic fields
	 */
	public function isDatabaseField($field)
	{
		if (in_array($field, $this->otherDbFields)) {
			return true;
		}
		
		return parent::isDatabaseField($field);
	}
	
	public function get_editable()
	{
		// only recent comment
		$tooOld = new \DateTime('5 days ago');
		
		return ($this->updated > $tooOld && $this->viewing_user->id == $this->user->id);
	}
	
	public function get_deletable()
	{
		return ($this->viewing_user->id == $this->user->id);
	}
	
	/**
	 * Modifying updated field behavior. It will be marked updated only if comment changes
	 * 
	 * @preUpdate
	 */
	public function updated()
	{
		if ($this->commentOriginalValue !== false) {
			parent::updated();
		}
	}
	
	protected $commentOriginalValue = false;
	public function set_comment($value)
	{
		if ($this->commentOriginalValue===false) {
			$this->commentOriginalValue = $this->comment;
		} else if ($this->commentOriginalValue===$value) {	// set to original value?
			$this->commentOriginalValue = false;
		}
		$this->comment = $value;
	}
	
	/**
	 * @param mixed $viewingUser - by default logged in user is used
	 */
	public function set_viewing_user($viewingUser)
	{
		$this->viewing_user = User::getUser($viewingUser);
	}
	
	public function get_viewing_user()
	{
		return User::getUser($this->viewing_user);
	}
	
	//// Coulnd't use it because constructors don't work in doctrine. (/w em)
	//protected static $loadedOnceStatically = false;
	//
	//protected function loadOnce()
	//{
	//	if (self::$loadedOnceStatically) {
	//		return;
	//	}
	//	
	//	\Fisdap\EntityUtils::addAsDatabaseField(getClass($this), 'editable');
	//	
	//	self::$loadedOnceStatically = true;
	//}
}
