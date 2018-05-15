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
 * Entity class for Legacy Marketing Billboards.
 *
 * @Entity
 * @Table(name="mktg_Campaign_Audience_Data")
 */
class MarketingCampaignAudienceLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="CampAudience_id", type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="MarketingCampaignLegacy", inversedBy="campaign_audiences")
     * @JoinColumn(name="Campaign_id", referencedColumnName="Campaign_id")
     */
    protected $campaign;

    /**
     * @Column(name="GroupNumber", type="integer")
     */
    protected $group_number;

    /**
     * @Column(name="CriterionType", type="string")
     */
    protected $criterion_type;

    /**
     * @Column(name="CriterionValue", type="string")
     */
    protected $criterion_value;

    /**
     * @Column(name="NotFlag", type="boolean")
     */
    protected $not_flag;

    public function userContextMatches($userContext)
    {
        $match = true;

        switch ($this->criterion_type) {
            case 'account_type':
                if ($userContext->isInstructor() && $this->criterion_value == "instructor") {
                    $match = true;
                } elseif (!$userContext->isInstructor() && $this->criterion_value == "student") {
                    $match = true;
                } else {
                    $match = false;
                }

                break;

            case 'certification_level':
                if ($userContext->isStudent()) {
                    $match = ($this->criterion_value == $userContext->getRoleData()->getCertification()->getName());
                } else {
                    $match = false;
                }
                break;

            case 'product':
                $match = $userContext->getPrimarySerialNumber()->hasProductAccess($this->criterion_value);
                break;

            case 'program_setting':
                if ($this->criterion_value == 'uses_beta') {
                    $prog = $userContext->getProgram();

                    $match = ($prog->use_beta == 1);
                }
                break;
        }

        if ($this->not_flag) {
            $match = !$match;
        }

        return $match;
    }
}
