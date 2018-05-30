<?php namespace Fisdap\Api\Shifts\Patients\Skills\Jobs\Ivs;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Shifts\Patients\Skills\AbstractSkills;
use Fisdap\Entity\Iv;
use Fisdap\Entity\IvFluid;
use Fisdap\Entity\IvProcedure;
use Fisdap\Entity\IvSite;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Swagger\Annotations as SWG;

/**
 * Class SetIvs
 * @package Fisdap\Api\Shifts\Patients\Skills\Jobs\Ivs
 * @author  Isaac White <isaac.white@ascendlearning.com>
 *
 * @SWG\Definition(
 *     definition="Iv",
 *     description="This is a model representation of Iv for storage.",
 *     required={ "procedureId", "ivSiteId", "fluidId" }
 * )
 */
final class SetIvs extends AbstractSkills
{
    /**
     * @var integer
     * @SWG\Property(type="integer", example=0)
     */
    public $ivId = 0;

    /**
     * @var integer
     * @SWG\Property(type="integer", example=3)
     */
    public $procedureId;

    /**
     * @var integer
     * @SWG\Property(type="integer", example=5)
     */
    public $ivSiteId;

    /**
     * @var integer
     * @SWG\Property(type="integer", example=4)
     */
    public $fluidId;

    /**
     * @var integer
     * This parameter is duplicated here from the AbstractSkills so the
     * example parameter can be set differently.
     * @SWG\Property(type="integer", example=18)
     */
    public $size;

    public function setIvId($ivId)
    {
        $this->ivId = $ivId;
    }

    /**
     * @param EntityManagerInterface $em
     * @param EventDispatcher $eventDispatcher
     *
     * @return Iv
     */
    public function handle(
        EntityManagerInterface $em,
        EventDispatcher $eventDispatcher
    ) {
        $this->em = $em;

        // Try to grab an existing Iv. If not found, create a new one.
        $ivs = $this->validResourceEntityManager(Iv::class, $this->ivId);
        $ivs = $ivs ? $ivs : new Iv;

        $ivs->set_patient($this->patient);
        $ivs->set_shift($this->shift);

        $ivs->setProcedure($this->validResourceEntityManager(IvProcedure::class, $this->procedureId, true));
        $ivs->setSite($this->validResourceEntityManager(IvSite::class, $this->ivSiteId, true));
        $ivs->setFluid($this->validResourceEntityManager(IvFluid::class, $this->fluidId, true));

        // This uses the 'size' parameter and maps it to 'gauge'. The transformer maps it back.
        $ivs->setDefaultSkills($this);

        $eventDispatcher->fire($this->ivId);

        return $ivs;
    }
    
    public function rules()
    {
        return [
            'procedureId' => 'required|integer',
            'size'        => 'integer',
            'ivSiteId'    => 'required|integer',
            'fluidId'     => 'required|integer',
        ];
    }
}
