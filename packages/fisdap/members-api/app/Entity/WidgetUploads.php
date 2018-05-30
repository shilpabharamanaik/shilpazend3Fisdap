<?php namespace Fisdap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for widget file uploads (see the FileUpload widget for the interface).
 *
 * @Entity(repositoryClass="Fisdap\Data\Widget\DoctrineWidgetUploadsRepository")
 * @Table(name="fisdap2_widget_uploads")
 */
class WidgetUploads extends File
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;
    
    /**
     * @ManyToOne(targetEntity="User", inversedBy="portfolioUploads")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $uploader;
    
    /**
     * @Column(type="boolean")
     */
    protected $educators_allowed = false;
    
    /**
     * @var ArrayCollection
     * @ManyToMany(targetEntity="CertificationLevel")
     * @JoinTable(name="fisdap2_widget_uploads_certification_levels",
     *  joinColumns={@JoinColumn(name="widget_upload_id", referencedColumnName="id")},
     *  inverseJoinColumns={@JoinColumn(name="certification_level_id",referencedColumnName="id")})
     */
    protected $certification_levels;
    
    public function init()
    {
        $this->certification_levels = new ArrayCollection();
    }
    
    public function processFile($uploadFile, $description)
    {
        $this->uploader = User::getLoggedInUser();
    
        parent::processFile($uploadFile, $description);
    }
    
    /**
     * Set the certifications for an uploaded file
     * @param array $values the IDs of the certification levels for this upload
     * @return \Fisdap\Entity\WidgetUpload
     */
    public function setCertificationIds($values)
    {
        $this->certification_levels->clear();
        foreach ($values as $value) {
            $this->certification_levels->add(self::id_or_entity_helper($value, "CertificationLevel"));
        }
        
        return $this;
    }
}
