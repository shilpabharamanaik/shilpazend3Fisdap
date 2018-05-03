<?php namespace Fisdap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;


/**
 * Entity class for Legacy Sites.
 * 
 * @Entity(repositoryClass="Fisdap\Data\Site\DoctrineSiteLegacyRepository")
 * @Table(name="AmbulanceServices")
 * @HasLifecycleCallbacks
 */
class SiteLegacy extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(name="AmbServ_id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
    /**
     * @Column(name="AmbServName", type="string")
     */
    protected $name;
    
    /**
     * @Column(name="ContactName", type="string", nullable=true)
     */
    protected $contact_name;
    
    /**
     * @Column(name="ContactTitle", type="string", nullable=true)
     */
    protected $contact_title;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $contact_email;
    
    /**
     * @Column(name="Address", type="string", nullable=true)
     */
    protected $address;
    
    /**
     * @Column(name="City", type="string", nullable=true)
     */
    protected $city;
    
    /**
     * @Column(name="PostalCode", type="string", nullable=true)
     */
    protected $zipcode;
    
    /**
     * @Column(name="Region", type="string", nullable=true)
     */
    protected $state;
    
    /**
     * @Column(name="Country", type="string", nullable=true)
     */
    protected $country = "USA";
    
    /**
     * @Column(name="BPhone", type="string", nullable=true)
     */
    protected $phone;
    
    /**
	 * @codeCoverageIgnore
     * @deprecated
     * @Column(name="DirPhone", type="string", nullable=true)
     */
    protected $dir_phone;
    
    /**
	 * @codeCoverageIgnore
     * @deprecated
     * @Column(name="DispPhone", type="string", nullable=true)
     */
    protected $disp_phone;
    
    /**
     * @Column(name="FaxNumber", type="string", nullable=true)
     */
    protected $fax;
    
    /**
	 * @codeCoverageIgnore
     * @deprecated
     * @Column(name="Medical_Director", type="string", nullable=true)
     */
    protected $medical_director;
    
    /**
     * @Column(name="AmbServAbrev", type="string")
     */
    protected $abbreviation;
    
    /**
     * @Column(name="Type", type="string")
     */
    protected $type = "field";
    
    /**
	 * @ManyToOne(targetEntity="ProgramLegacy")
	 * @JoinColumn(name="OwnerProgram_id", referencedColumnName="Program_id")
	 */
	protected $owner_program;
    
    /**
	 * @OneToMany(targetEntity="BaseLegacy", mappedBy="site", cascade={"persist", "remove"})
	 * @JoinColumn(name="AmbServ_id", referencedColumnName="AmbServ_id")
	 */
	protected $bases;
    
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="ProgramSiteLegacy", mappedBy="site", cascade={"persist","remove"})
     * @JoinColumn(name="AmbServ_id", referencedColumnName="AmbServ_id")
     */
	protected $program_site_associations;
    
	/**
	 * @OneToMany(targetEntity="ShiftLegacy", mappedBy="site")
	 */
	protected $shift;
        
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="ProgramSiteShare", mappedBy="site", cascade={"persist","remove"})
     * @JoinColumn(name="Site_id", referencedColumnName="AmbServ_id")
     */
    protected $site_shares;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="SiteStaffMember", mappedBy="site", cascade={"persist","remove"})
     * @JoinColumn(name="site_id", referencedColumnName="AmbServ_id")
     */
    protected $staff_members;
	
    public function init()
	{
		$this->bases = new ArrayCollection();
		$this->program_site_associations = new ArrayCollection();
        $this->site_shares = new ArrayCollection();
        $this->staff_members = new ArrayCollection();
	}

	public function set_owner_program($value)
	{
		$this->owner_program = self::id_or_entity_helper($value, "ProgramLegacy");
		return $this;
	}

	public function getId()
	{
		return $this->id;
	}

	public function addBase(BaseLegacy $base)
	{
		$this->bases->add($base);
		$base->site = $this;
	}
	
	public function removeBase(BaseLegacy $base)
	{
		$this->bases->removeElement($base);
		$base->site = null;
	}

    /**
     * Checks to see if this site is active for a given program.
     *
     * @param $program_id
     * @return bool
     */
	public function isSiteActive($program_id) {
	    foreach ($this->program_site_associations as $site_association) {
	        if ($program_id == $site_association->program->id && $this->id == $site_association->site->id) {
                return ($site_association->active ? 1 : 0);
            }
        }

        return -1;
    }

    /**
     * Add a staff member to a site
     *
     * @param SiteStaffMember $staff_member
     */
    public function addStaffMember(SiteStaffMember $staff_member)
    {
        $this->staff_members->add($staff_member);
        $staff_member->site = $this;
    }

    /**
     * Remove a staff member from a site
     *
     * @param SiteStaffMember $staff_member
     */
    public function removeStaffMember(SiteStaffMember $staff_member)
    {
        $this->staff_members->removeElement($staff_member);
        $staff_member->site = null;
    }
	
	
	
	public function getAssociationByProgram($program_id)
	{
		
		if($this->program_site_associations){
			
			foreach($this->program_site_associations as $assoc){
				if($assoc->program->id == $program_id){
					return $assoc;
				}
			}
			
		}
		
		return false;
	}
	
	public function getAccreditationInfoByProgram($program_id)
	{
		return EntityUtils::getRepository("SiteAccreditationInfo")->getInfo($this->id, $program_id);
	}

    /**
     * @param integer $programId
     * @param mixed $types string | array of strings containing types
     * @param boolean $active
     * @return array site_id => site_name
     */
	public static function getSites($programId, $types = null, $active = true, $opt_groups = false)
	{
		$sites = array();
        $sites_grouped_by_type = array();

		if (is_null($types)) {
			$types = array();
		} else if (!is_array($types)) {
			$types = array($types);
		}

		$associations = EntityUtils::getRepository("SiteLegacy")->getSiteAssociationsByProgram($programId, $active);

		foreach ($associations as $association) {

            $include_site = false;

			if (!empty($types)) {
				if (in_array($association->site->type, $types)) {
                    if ($active) {
                        if (count(EntityUtils::getRepository("BaseLegacy")->getBaseAssociationsByProgramOptimized($association->site->id, $programId, $active)) > 0) {
                            $include_site = true;
                        }
                    } else {
                        $include_site = true;
                    }
				}
			} else {
			    if ($active) {
                    if (count(EntityUtils::getRepository("BaseLegacy")->getBaseAssociationsByProgramOptimized($association->site->id, $programId, $active)) > 0) {
                        $include_site = true;
                    }
                } else {
                    $include_site = true;
                }
			}

            if($include_site){

                $sites[$association->site->id] = $association->site->name;

                $type = ucfirst($association->site->type);

                if(!is_array($sites_grouped_by_type[$type])){
                    $sites_grouped_by_type[$type] = array();
                }

                $sites_grouped_by_type[$type][$association->site->id] = $association->site->name;
            }

		}

        ksort($sites_grouped_by_type);

		return ($opt_groups) ? $sites_grouped_by_type : $sites;
	}
	

	
	public function getSiteAddress()
	{
		$addressParts = array();

		if(trim($this->address) != ""){
			$addressParts[] = trim($this->address);
		}

		if(trim($this->city) != ""){
			$addressParts[] = trim($this->city);
		}

		if((trim($this->state) . " " . trim($this->zipcode)) != ""){
			$addressParts[] = trim($this->state) . " " . trim($this->zipcode);
		}

		return implode(", ", $addressParts);

	}

    /**
     * @param ShiftLegacy $shift the shift, we need this so we know which base we need the address for
     * @return string the appropriate address, formatted for use in Google Maps
     */
    public function getMapAddress(ShiftLegacy $shift)
    {
        switch ($shift->type) {
            case "field":
                return $shift->base->getMapAddress();
                break;
            case "clinical":
                // if the department has an address, return that
                if ($shift->base->getMapAddress()) {
                    return $shift->base->getMapAddress();
                } else {
                    // otherwise return the site address
                    $addressString = $this->getSiteAddress();
                    return str_replace(" ", "+", $addressString);
                }
                break;
            case "lab":
                $addressString = $this->getSiteAddress();
                return str_replace(" ", "+", $addressString);
                break;
        }
    }
	
	public function hasValidMapAddress($shift)
	{
		$shiftHasValidAddress = false;
		
		if($shift->type != "field"){
			// If they have the address and any two other points, return true...
			if(trim($this->address) != ""){
				$totalPoints = 0;

				if(trim($this->city) != "") $totalPoints++;
				if(trim($this->state) != "") $totalPoints++;
				if(trim($this->zipcode) != "") $totalPoints++;

				if($totalPoints >= 2){
					return true;
				}
			// Otherwise, return false.  No use without an address.
			}

			return false;
		}else{
			// First check to see if there is only one base- if there is, use
			// its address...
			if(count($this->bases) == 1){
				return $this->bases[0]->hasValidMapAddress();
			}else{
				return $shift->base->hasValidMapAddress();
			}
		}
	}
        
	public function getAssociatedPrograms()
	{
		$programs = array();
		foreach ($this->program_site_associations as $association) {
			$program_data["id"] = $association->program->id;
			$program_data["name"] = $association->program->name;
			$program_data["active"] = $association->active;
			$program_data["shared"] = $association->program->sharesSite($this->id);
			$program_data["admin"] = $association->program->isAdmin($this->id);
			$program_data["pending"] = $association->program->pendingApproval($this->id);

			$programs[] = $program_data;
		}
		
		return $programs;
	}

	public function getNetworkPrograms()
	{
		$network_programs = array();
		$associated_programs = $this->getAssociatedPrograms();
		if($associated_programs){
			foreach($associated_programs as $program_data){
				if($program_data['shared']){
					$network_programs[] = $program_data['id'];
				}
			}
		}
		
		return $network_programs;
	}

	public function getAdminPrograms()
	{
		$programs = array();
		foreach ($this->site_shares as $share) {
			if ($share->admin) {
				$programs[$share->program->id] = $share->program->name;
			}
		}
		
		return $programs;
	}
	
	public function getSharedPrograms()
	{
		$programs = array();
		foreach ($this->site_shares as $share) {
			$programs[$share->program->id] = $share->program;
		}
		
		return $programs;
	}
	
	/**
	 * Add a shared scheduler association between Site and Program
	 *
	 * @param \Fisdap\Entity\ProgramSiteShare $share
	 */
	public function addShare(ProgramSiteShare $share)
	{
		$this->site_shares->add($share);
		$share->site = $this;
	}

       	/**
	 * Get the shared scheduler association entity for this Site and a given Program
	 */
	public function getShareByProgram($program_id)
	{
            foreach ($this->site_shares as $share) {
                if ($share->program->id == $program_id) {
                	return $share; 
                }
            }
            
            return false;
	}

	public function sendSharingRequest($program_id)
    {
	    $program = EntityUtils::getEntity("ProgramLegacy", $program_id);
	    $user = \Fisdap\Entity\User::getLoggedInUser();
	    if (!$user->isInstructor()) {
			return false;
	    }

	    foreach ($this->site_shares as $share) {
            if ($share->program->id == $program->id) {
			    // there is already a sharing association between this site and program
			    return false;
			}
	    }

	    // create the connection
	    $share = EntityUtils::getEntity("ProgramSiteShare");
	    $share->program = $program;
	    $share->requesting_instructor = $user->getCurrentRoleData();
	    $this->addShare($share);
	    $this->save();
	
	    // send the mail
	    $mail = new \Fisdap_TemplateMailer();
        $mail->setSubject("Fisdap Shared Scheduler request")
             ->setViewParam("requesting_inst", $user->getName())
             ->setViewParam("program", $user->getCurrentRoleData()->program->name)
             ->setViewParam("phone", $user->work_phone)
             ->setViewParam("email", $user->email)
             ->setViewParam("site", $this->name)
             ->setViewParam("sharingUrl", \Util_HandyServerUtils::getCurrentServerRoot() . "account/sites/site/siteId/" . $this->id . "#sharedScheduler");
	    foreach ($this->getSharingContactInfo() as $contact) {
		    $mail->addTo($contact["email"]);
	    }

	    if ($this->hasNetwork()) {
		    $mail->sendHtmlTemplate("request-sharing.phtml");
	    } else {
            $mail->addTo('support@fisdap.net');
			$mail->sendHtmlTemplate("setup-sharing.phtml");
	    }

        return true;
    }
        
        public function hasNetwork()
        {
            foreach ($this->site_shares as $share) {
                if ($share->approved) {
                    return true;
                }
            }
            
            return false;
        }
        
        public function getSharingContactInfo()
        {
            $contact_info = array();
            foreach ($this->site_shares as $share) {
                if ($share->admin) {
                    $admin = $share->program->getProgramContact();
		    if ($admin->user) {
                    	$contact_info[] = array("name" => $admin->user->getName(), "email" => $admin->email);
                    }
		}
            }
            if (count($contact_info) < 1) {
                $contact_info[] = array("name" => "Fisdap", "email" => "support@fisdap.net");
            }
            return $contact_info;
        }
	
}
