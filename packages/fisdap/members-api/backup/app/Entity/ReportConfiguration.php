<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;


/**
 * Entity class for Report Configurations. A report configuration is the set of input values
 * that go into running a Fisdap report.
 *
 * @Entity(repositoryClass="Fisdap\Data\Report\DoctrineReportConfigurationRepository")
 * @Table(name="fisdap2_report_configurations")
 *
 * @todo Write some unit tests!
 * @todo Write setters/getters and other core functionality
 */
class ReportConfiguration extends Timestampable
{
    /**
     * @var array containing invalid fields from validation
     */
    public $invalidFields = array();
    
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var UserContext
     * @ManyToOne(targetEntity="UserContext")
     * @JoinColumn(name="user_role_id", referencedColumnName="id")
     */
    protected $user_context;
    
    /**
     * @var string
     * @Column(type="text", nullable=false)
     */
    protected $config;
    
    /**
     * @ManyToOne(targetEntity="Report")
     * @JoinColumn(name="report_id", referencedColumnName="id")
     */
    protected $report;

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $favorite = false;

    
    // getters and setters
    
    /**
     *	Config is saved in the database as a serialized array of submitted form values
     */
    public function get_config()
    {
        return unserialize($this->config);
    }
    public function set_config($config)
    {
        $this->config = serialize($config);
    }
    
    public function set_user_context($value) {
        $this->user_context = self::id_or_entity_helper($value, 'UserContext');
    }
    
    public function set_report($value) {
        $this->report = self::id_or_entity_helper($value, 'Report');
    }
    
    public function getDescription() {
        $report = EntityUtils::getEntity('Report', $this->report->id);
        $reportClass = "\\Fisdap_Reports_{$this->report->class}";
        
        if (class_exists($reportClass)) {
            $report = new $reportClass($report, $this->get_config());
            return $report->getShortConfigLabel();
        }
        
        return null;
    }
    
}