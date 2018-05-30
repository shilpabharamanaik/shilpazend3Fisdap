<?php namespace Fisdap\Api\Users\UserContexts\Roles\Students\Permissions;

use Fisdap\Api\Products\Finder\FindsProducts;
use Fisdap\Api\Users\UserContexts\Permissions\UserContextPermission;
use Fisdap\Entity\UserContext;

/**
 * Validates whether a (student) user role has Skills Tracker or Scheduler
 *
 * @package Fisdap\Api\Users\UserContexts\Roles\Students\Permissions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class HasSkillsTrackerOrScheduler implements UserContextPermission
{
    const SCHEDULER_LIMITED_PRODUCT_NAME = 'Scheduler (Limited)';
    const SCHEDULER_UNLIMITED_PRODUCT_NAME = 'Scheduler (Unlimited)';
    const SKILLS_TRACKER_LIMITED_PRODUCT_NAME = 'Skills Tracker (Limited)';
    const SKILLS_TRACKER_UNLIMITED_PRODUCT_NAME = 'Skills Tracker (Unlimited)';


    /**
     * @var FindsProducts
     */
    private $productsFinder;


    /**
     * @param FindsProducts $productsFinder
     */
    public function __construct(FindsProducts $productsFinder)
    {
        $this->productsFinder = $productsFinder;
    }


    /**
     * @inheritdoc
     */
    public function permitted(UserContext $userContext)
    {
        $userProducts = $this->productsFinder->getUserContextProductNames($userContext->getId());

        $hasLimitedScheduler = $this->productsFinder->hasProduct(self::SCHEDULER_LIMITED_PRODUCT_NAME, $userProducts);
        $hasLimitedSkillsTracker = $this->productsFinder->hasProduct(
            self::SKILLS_TRACKER_LIMITED_PRODUCT_NAME,
            $userProducts
        );
        $hasUnlimitedScheduler = $this->productsFinder->hasProduct(
            self::SCHEDULER_UNLIMITED_PRODUCT_NAME,
            $userProducts
        );
        $hasUnlimitedSkillsTracker = $this->productsFinder->hasProduct(
            self::SKILLS_TRACKER_UNLIMITED_PRODUCT_NAME,
            $userProducts
        );

        return ($hasLimitedScheduler === true ||
            $hasLimitedSkillsTracker === true ||
            $hasUnlimitedScheduler === true ||
            $hasUnlimitedSkillsTracker === true);
    }
}
