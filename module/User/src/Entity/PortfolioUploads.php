<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for Student Portfolio options.
 *
 * @Entity(repositoryClass="Fisdap\Data\Portfolio\DoctrinePortfolioUploadsRepository")
 * @Table(name="fisdap2_portfolio_uploads")
 */
class PortfolioUploads extends File
{
	/**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @ManyToOne(targetEntity="StudentLegacy", inversedBy="portfolioUploads")
	 * @JoinColumn(name="student_id", referencedColumnName="Student_id")
	 */
	protected $student;
	
	/**
	 * @ManyToOne(targetEntity="User", inversedBy="portfolioUploads")
	 * @JoinColumn(name="user_id", referencedColumnName="id")
	 */
	protected $uploader;
    
    /**
    * @Column(type="string")
    */
    protected $original_name;
	
	public function processStudentFile($uploadFile, $description, $studentId){
		$this->student = self::id_or_entity_helper($studentId, 'StudentLegacy');
		$this->uploader = User::getLoggedInUser();
		
		parent::processFile($uploadFile, $description);
	}
}
