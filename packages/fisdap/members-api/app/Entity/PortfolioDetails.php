<?php namespace Fisdap\Entity;

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
 * @Entity
 * @Table(name="fisdap2_portfolio_details")
 */
class PortfolioDetails extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="StudentLegacy", inversedBy="portfolioOptions")
     * @JoinColumn(name="student_id", referencedColumnName="Student_id")
     */
    protected $student;
    
    /**
     * @Column(type="text")
     */
    protected $portfolio_description = "";
    
    public function get_formatted_date($field)
    {
        if ($this->$field == null) {
            return '';
        } else {
            return $this->$field->format('m/d/Y');
        }
    }
}
