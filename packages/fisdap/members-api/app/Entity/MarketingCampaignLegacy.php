<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Legacy Marketing Billboards.
 *
 * @Entity
 * @Table(name="mktg_Campaign_Data")
 */
class MarketingCampaignLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="Campaign_id", type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @OneToMany(targetEntity="MarketingCampaignAudienceLegacy", mappedBy="campaign")
     * @JoinColumn(name="Campaign_id", referencedColumnName="Campaign_id")
     */
    protected $campaign_audiences;

    /**
     * @OneToMany(targetEntity="MarketingCampaignBillboardLegacy", mappedBy="billboard")
     * @JoinColumn(name="Billboard_id", referencedColumnName="Billboard_id")
     */
    protected $campaign_billboards;

    /**
     * @Column(name="CampaignName", type="string")
     */
    protected $campaign_name;

    /**
     * @Column(name="StartDate", type="date")
     */
    protected $start_date;

    /**
     * @Column(name="EndDate", type="date")
     */
    protected $end_date;

    /**
     * @Column(name="Action", type="string")
     */
    protected $action;

    /**
     * @Column(name="Priority", type="integer")
     */
    protected $priority;

    /**
     * @Column(name="CampaignNotes", type="string")
     */
    protected $campaign_notes;

    public function isUserContextAuthorized($userContext)
    {
        $audience_criteria = $this->campaign_audiences;
        $group_matches = array();

        // loop through each group
        foreach ($audience_criteria as $audienceRecord) {
            $group = $audienceRecord->group_number;

            if (!isset($group_matches[$group])) {
                $group_matches[$group] = true;
            }

            $group_matches[$group] = ($audienceRecord->userContextMatches($userContext) && $group_matches[$group]);
        }

        foreach ($group_matches as $gm) {
            if ($gm) {
                return true;
            }
        }

        return false;
    }

    public function isDateApproved()
    {
        $today = new \DateTime();

        if ($today > $this->start_date && $today <= $this->end_date) {
            return true;
        } else {
            return false;
        }
    }
}
