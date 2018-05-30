<?php namespace Fisdap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Professions
 *
 * @Entity(repositoryClass="Fisdap\Data\Profession\DoctrineProfessionRepository")
 * @Table(name="fisdap2_profession")
 */
class Profession extends Enumerated
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $name;

    /**
     * @var ArrayCollection|CertificationLevel[]
     * @OneToMany(targetEntity="CertificationLevel", mappedBy="profession")
     */
    protected $certifications;
    
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="ReportCategory", mappedBy="profession")
     */
    protected $report_categories;

    public function __construct()
    {
        $this->certifications = new ArrayCollection();
        $this->report_categories = new ArrayCollection();
    }


    /**
     * @return ArrayCollection|CertificationLevel[]
     */
    public function getCertifications()
    {
        return $this->certifications;
    }
}
