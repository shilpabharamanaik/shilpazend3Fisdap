<?php namespace Fisdap\Api\Shifts\PreceptorSignoffs\Jobs;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Data\PreceptorRating\PreceptorRatingRepository;
use Fisdap\Entity\PreceptorRating;
use Fisdap\Entity\PreceptorRatingRaterType;
use Fisdap\Entity\PreceptorRatingType;
use Fisdap\Entity\PreceptorSignoff;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Swagger\Annotations as SWG;

/**
 * Class SetRatings
 * @package Fisdap\Api\Shifts\PreceptorSignoffs\Jobs
 * @author  Isaac White <isaac.white@ascendlearning.com>
 *
 * @SWG\Definition(
 *     definition="Ratings",
 *     required={ "type", "raterType" }
 * )
 */
final class SetRatings extends Job implements RequestHydrated
{
    /**
     * @var integer
     * @see PreceptorRatingType
     * @SWG\Property(type="integer", default=1)
     */
    public $type;

    /**
     * @var integer
     * @see PreceptorRatingRaterType
     * @SWG\Property(type="integer", description="This is the Rater's Type (Student/Instructor)", default=2)
     */
    public $raterType;
    
    /**
     * @var integer|null
     * @SWG\Property(type="integer", description="This is the value assigned by the Rater, (-1 mean N/A)", default=2)
     */
    public $value = null;

    /**
     * @var integer|null
     * @see PreceptorSignoff
     */
    protected $signoff;
    
    /**
     * @param EntityManagerInterface $em
     * @param EventDispatcher $eventDispatcher
     * @param PreceptorRatingRepository $preceptorRatingRepository
     * @return PreceptorRating|object
     */

    public function handle(
        EntityManagerInterface $em,
        EventDispatcher $eventDispatcher,
        PreceptorRatingRepository $preceptorRatingRepository
    ) {
        $this->em = $em;
        
        $rating = $preceptorRatingRepository->findOneBy(['signoff' => $this->signoff, 'type' => $this->type, 'rater_type' => $this->raterType]);

        $rating = $rating ? $rating : new PreceptorRating;

        $type = $this->validResourceEntityManager(PreceptorRatingType::class, $this->type, true);

        $raterType = $this->validResourceEntityManager(PreceptorRatingRaterType::class, $this->raterType, true);

        /** Set Rating fields  **/
        $rating->setRatingType($type);
        $rating->setRatingRaterType($raterType);
        $rating->setValue($this->value);
        
        $eventDispatcher->fire($type);
        
        return $rating;
    }
    
    public function setSignoff(PreceptorSignoff $signoff)
    {
        $this->signoff = $signoff;
    }
    
    public function getSignoff()
    {
        return $this->signoff;
    }
}
