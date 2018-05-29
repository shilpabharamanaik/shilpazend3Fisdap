<?php namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Entity class for Reports.
 *
 * @Entity(repositoryClass="Fisdap\Data\Report\DoctrineReportRepository")
 * @Table(name="fisdap2_reports")
 */
class Report extends Enumerated
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $name;
    
    /**
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $class;
    
    /**
     * @Column(type="text", nullable=true)
     */
    protected $description;
    
    /**
     * @Column(type="text", nullable=true)
     */
    protected $student_description;
    
    /**
     * @var boolean
     * @Column(type="boolean", nullable=false)
     */
    protected $standalone = false;
    
    /**
     * @ManyToMany(targetEntity="ReportCategory")
     * @JoinTable(name="fisdap2_category_report",
     *  joinColumns={@JoinColumn(name="report_id", referencedColumnName="id")},
     *  inverseJoinColumns={@JoinColumn(name="category_id",referencedColumnName="id")})
     */
    protected $categories;

    public function init()
    {
        $this->categories = new ArrayCollection();
    }
    
    /**
     *	Returns report class of given report class name
     *	@return \Fisdap\Entity\Report
     */
    public static function getByClass($class)
    {
        $repo = EntityUtils::getEntityManager()->getRepository("\Fisdap\Entity\Report");
        return $repo->findOneByClass($class);
    }
    
    /**
     *	Returns an array of categories relevent to THIS user
     *	@return array
     */
    public function getCategories($userobj)
    {
        $categories = array();
        $program = $userobj->getCurrentProgram();
        foreach ($this->categories as $category) {
            if ($category->profession == $program->profession) {
                $categories[$category->id] = $category;
            }
        }
        return $categories;
    }
    
    /**
     *	Returns a pretty string of categories relevent to THIS user
     *	@return string
     */
    public function getCategoryList($userobj)
    {
        $categories = array();
        foreach ($this->getCategories($userobj) as $category) {
            $categories[] = $category->name;
        }
        return implode(", ", $categories);
    }
    
    /**
     *	Returns a pretty string of category ids relevent to THIS user
     *	@return string
     */
    public function getCategoryIds($userobj)
    {
        $categories = array();
        foreach ($this->getCategories($userobj) as $category) {
            $categories[] = $category->id;
        }
        return implode(",", $categories);
    }
    
    public function getDescription($userobj)
    {
        $student = ($userobj->getCurrentRoleName() == 'student');
        if ($student && !is_null($this->student_description)) {
            return $this->student_description;
        }
        return $this->description;
    }
}
