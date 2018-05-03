<?php namespace Fisdap\Api\Products\SerialNumbers\Listeners;

use Fisdap\Api\Products\SerialNumbers\Events\SerialNumberWasActivated;
use Fisdap\Data\Product\ProductRepository;
use Fisdap\Data\SerialNumber\SerialNumberLegacyRepository;
use Fisdap\Entity\SerialNumberLegacy;
use Fisdap\Logging\Events\EventLogging;


/**
 * An event listener for enrolling a user in Moodle courses,
 * when a serial number (SerialNumberLegacy Entity) has been activated
 *
 * @package Fisdap\Api\Products\SerialNumbers\Listeners
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @todo
 */
final class EnrollUserInMoodleCourses
{
    use EventLogging;


    /**
     * @var SerialNumberLegacyRepository
     */
    private $serialNumberLegacyRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;


    /**
     * EnrollUserInMoodleCourses constructor.
     *
     * @param SerialNumberLegacyRepository $serialNumberLegacyRepository
     * @param ProductRepository            $productRepository
     */
    public function __construct(
        SerialNumberLegacyRepository $serialNumberLegacyRepository,
        ProductRepository $productRepository
    ) {
        $this->serialNumberLegacyRepository = $serialNumberLegacyRepository;
        $this->productRepository = $productRepository;
    }


    public function handle(SerialNumberWasActivated $event)
    {
        /** @var SerialNumberLegacy $serialNumber */
//        $serialNumber = $this->serialNumberLegacyRepository->getOneById($event->getId());
//
//         $products = EntityUtils::getRepository("Product")->getProductsWithMoodleCourses();
//
//        foreach ($products as $product) {
//            if ($this->hasProduct($product->configuration)) {
//                //Deal with the transition course separately
//                if ($product->category->id == 4) {
//
//                    //Get the moodle API for this moodle context
//                    $moodleAPI = new \Util_MoodleAPI($product->moodle_context);
//
//                    //Look for moodle override
//                    $moodleOverride = EntityUtils::getRepository("MoodleCourseOverride")->findOneBy(array("product" => $product->id, "program" => $this->program->id));
//
//                    if ($moodleOverride->id) {
//                        $courseId = $moodleOverride->moodle_course_id;
//                    } else {
//                        $courseId = $product->moodle_course_id;
//                    }
//
//                    //Enroll the user in the correct course
//                    $result = $moodleAPI->enrollCourse($this->user, $courseId);
//
//                    //Get possible moodle groups to add this user to
//                    //$moodleGroups = \Fisdap\EntityUtils::getRepository("MoodleGroup")->findBy(array("product" => $product->id, "program" => $this->program->id));
//
//                    //foreach ($moodleGroups as $group) {
//                    //	$result = $moodleAPI->addGroupMember($this->user, $group->moodle_group_id);
//                    //}
//                } else {
//                    // add to db
//                    $addToDb = false;
//                    if ($product->id == 9) {
//                        // we have preceptor training, make sure the account is an instructor before we add to the db
//                        $addToDb = ($this->user->getCurrentRoleName() == "instructor") ? true : false;
//                    } else {
//                        $addToDb = true;
//                    }
//
//                    if ($addToDb) {
//                        EntityUtils::getRepository("User")->enrollInMoodleCourse($product, $this->user->username);
//                    }
//                }
//            }
//        }

    }
}