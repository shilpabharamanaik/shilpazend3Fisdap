<?php namespace Fisdap\Entity;

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
 * Entity class for Site Staff members.
 *
 * @Entity(repositoryClass="Fisdap\Data\Site\DoctrineSiteStaffMemberRepository")
 * @Table(name="fisdap2_site_staff_member")
 */
class SiteStaffMember extends EntityBaseClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var \Fisdap\Entity\SiteLegacy
     * @ManyToOne(targetEntity="SiteLegacy", inversedBy="staff_members")
     * @JoinColumn(name="site_id", referencedColumnName="AmbServ_id")
     */
    protected $site;

    /**
     * @var \Fisdap\Entity\ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy", inversedBy="staff_members")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;

    /**
     * @Column(type="string", length=40)
     */
    protected $first_name;

    /**
     * @Column(type="string", length=40)
     */
    protected $last_name;

    /**
     * @Column(type="string", length=60, nullable=true)
     */
    protected $title;

    /**
     * @Column(type="string", length=30, nullable=true)
     */
    protected $phone;

    /**
     * @Column(type="string", length=30, nullable=true)
     */
    protected $pager;

    /**
     * @Column(type="string", length=60, nullable=true)
     */
    protected $email;

    /**
     * @var string
     * @Column(type="string", length=200, nullable=true)
     */
    protected $notes;

    /**
     * @ManyToMany(targetEntity="BaseLegacy")
     * @JoinTable(name="fisdap2_site_staff_member_base_association",
     *      joinColumns={@JoinColumn(name="staff_member_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="base_id", referencedColumnName="Base_id")}
     *      )
     **/
    protected $bases;

    /**
     * Initialize this instance and create the collections
     */
    public function init()
    {
        $this->bases = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set which site this staff member is associated with
     *
     * @param SiteLegacy | int $site
     * @return $this
     * @throws \Exception
     */
    public function set_site($site)
    {
        $this->site = self::id_or_entity_helper($site, "SiteLegacy");
        return $this;
    }

    /**
     * Set which program this staff member is associated with
     * Note: since this staff member belongs to this program, the staff member will be viewable by any
     * other programs in this sharing network, but will "go with" this program if the program leaves the sharing network
     *
     * @param ProgramLegacy | int $program
     * @return $this
     * @throws \Exception
     */
    public function set_program($program)
    {
        $this->program = self::id_or_entity_helper($program, "ProgramLegacy");
        return $this;
    }

    /**
     * Check to see if this staff member has any contact information
     * @return bool
     */
    public function hasContactInfo()
    {
        return ($this->phone || $this->email || $this->pager);
    }

    /**
     * @return array an array of associated base names, keyed by id
     */
    public function getBases()
    {
        $bases = array();
        foreach ($this->bases as $base) {
            $bases[$base->id] = $base->name;
        }
        return $bases;
    }

    /**
     * Update all the base associations for this staff member to the bases given in the array
     * @param array $bases an array of BaseLegacy objects or base ids
     */
    public function updateBases(array $bases)
    {
        // first clear out the current associations
        $this->bases->clear();

        // then add each base
        foreach ($bases as $base) {
            $base = self::id_or_entity_helper($base, "BaseLegacy");

            $this->bases->add($base);
        }

        $this->save();
    }
}
