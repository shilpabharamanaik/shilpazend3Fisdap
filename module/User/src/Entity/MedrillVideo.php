<?php namespace User\Entity;
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
 * Entity class for Medrill Videos 
 *
 * @Entity(repositoryClass="Fisdap\Data\Medrill\DoctrineMedrillVideoRepository")
 * @Table(name="fisdap2_medrill_video")
 */
class MedrillVideo extends EntityBaseClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(type="integer")
     */
    protected $vimeo_id;

    /**
     * @Column(type="string", length=60)
     */
    protected $title;

    /**
     * @var MedrillCategory
     * @ManyToOne(targetEntity="MedrillCategory")
     * @JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * @var string
     * @Column(type="string", length=200)
     */
    protected $caption;

    /**
     * @Column(type="text")
     */
    protected $description;

    /**
     * @ManyToMany(targetEntity="Product")
     * @JoinTable(name="fisdap2_medrill_video_product_association",
     *      joinColumns={@JoinColumn(name="medrill_video_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="product_id", referencedColumnName="id")}
     *      )
     **/
    protected $products;

    /**
     * Initialize this instance and create the collections
     */
    public function init()
    {
        $this->products = new ArrayCollection();
    }
}
