<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Fisdap\Entity\BaseLegacy;
use Fisdap\Entity\ProgramSiteLegacy;
use Fisdap\Entity\SiteLegacy;
use Fisdap\EntityUtils;


/**
 * Class Sites
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait Sites
{
    /**
     * @OneToMany(targetEntity="ProgramSiteLegacy", mappedBy="program", cascade={"persist","remove"})
     * @JoinColumn(name="Program_id", referencedColumnName="Program_id")
     */
    protected $program_site_associations;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="ProgramSiteShare", mappedBy="program", cascade={"persist","remove"})
     * @JoinColumn(name="Program_id", referencedColumnName="Program_id")
     */
    protected $site_shares;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="SiteStaffMember", mappedBy="program", cascade={"persist","remove"})
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $site_staff_members;


    /**
     * @codeCoverageIgnore
     * @deprecated 
     */
    public function createDemoSites()
    {
        foreach($this->program_types as $type) {
            switch($type->id) {
                case 1:
                    $type = "lab";
                    break;
                case 2:
                case 3:
                    $type = "clinical";
                    break;
                case 4:
                    $type = "field";
                    break;
            }

            $site = new SiteLegacy;
            $site->name = $this->name;
            $site->abbreviation = $this->abbreviation;
            $site->address = $this->address;
            $site->city = $this->city;
            $site->state = $this->state;
            $site->zipcode = $this->zip;
            $site->country = $this->country;
            $site->phone = $this->phone;
            $site->type = $type;
            $site->owner_program = $this;
            $site->save(false);

            $base = new BaseLegacy;
            $base->name = "Main";
            $base->site = $site;
            $base->abbreviation = "Main";
            $base->city = $this->city;
            $base->state = $this->state;
            $base->address = $this->address;
            $base->zip = $this->zip;
            $base->type = $type;
            $base->save(false);

            //Add site and base to program
            $this->addSite($site, true);
            $this->addBase($base, true);
            $this->save();
        }
    }
    
    
    public function usesSharing() {
        foreach ($this->site_shares as $share) {
            if ($share->approved) {
                return true;
            }
        }
        return false;
    }


    public function isActiveSite($site_id){
        foreach ($this->program_site_associations as $association) {
            if ($association->site->id == $site_id) {
                return $association->active;
            }
        }
        return false;
    }


    public function sharesSite($site_id) {
        foreach ($this->site_shares as $share) {
            if ($share->site->id == $site_id && $share->approved) {
                return true;
            }
        }
        return false;
    }

    public function getNetworkPrograms($site)
    {
        $ids = array();

        if(($this->sharesSite($site->id))){
            $programs = $site->getAssociatedPrograms();
            if($programs){
                foreach($programs as $program_data){
                    if($program_data["shared"]){
                        $ids[] = $program_data["id"];
                    }
                }
            }
        }

        return $ids;
    }


    public function pendingApproval($site_id) {
        foreach ($this->site_shares as $share) {
            if ($share->site->id == $site_id && $share->approved == 0) {
                return true;
            }
        }
        return false;
    }


    public function isAdmin($site_id){
        foreach ($this->site_shares as $share) {
            if ($share->site->id == $site_id && $share->admin) {
                return true;
            }
        }
        return false;
    }


    public function seesSharedStudents($site_id){
        if($this->site_shares){
            foreach ($this->site_shares as $share) {
                if ($share->site->id == $site_id && $share->see_students) {
                    return true;
                }
            }
        }
        return false;
    }


    public function addSite(SiteLegacy $site, $active = 1)
    {
        if ($this->getEntityRepository()->getAssociationCountBySite($site->id, $this->id)) {
            return true;
        }

        $association = new ProgramSiteLegacy();
        $association->site = $site;
        $association->program = $this;
        $association->active = $active;
        $association->save();

        $this->program_site_associations->add($association);
        return true;
    }

    
    public function hasSite(SiteLegacy $site){
        if ($this->getEntityRepository()->getAssociationCountBySite($site->id, $this->id)) {
            return true;
        }
        else {
            return false;
        }
    }

    
    public function removeSite(SiteLegacy $site)
    {
        foreach ($this->program_site_associations as $association) {
            if ($association->site == $site) {
                $this->program_site_associations->removeElement($association);
                $association->delete();
            }
        }
    }

    
    public function toggleSite(SiteLegacy $site, $active)
    {
        $success = false;

        foreach ($this->program_site_associations as $association) {
            if ($association->site == $site) {
                $association->active = $active;
                $association->save();
                $success = true;
            }
        }

        return $success;
    }

    
    public function getSharedStatus($site_id){
        $site = EntityUtils::getEntity('SiteLegacy', $site_id);

        if (count($site->site_shares) < 1) {
            return 0; // no sharing network
        }

        if ($this->isAdmin($site_id)) {
            return 4; // admin
        }

        if ($this->sharesSite($site_id)) {
            return 3; // in network
        }

        if ($this->pendingApproval($site_id)) {
            return 2; // pending approval
        }

        return 1; // not in network
    }
}