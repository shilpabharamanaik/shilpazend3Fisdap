<?php
/**
 * Created by PhpStorm.
 * User: khanson
 * Date: 4/22/15
 * Time: 2:05 PM
 */

namespace Fisdap\Service;

/**
 * Provides transformation and evaluation methods for working with products
 *
 * @package Fisdap\Service
 */
class ProductService
{
    const SKILLS_TRACKER_ICON = "/images/product-icons/product-icon-1.svg";
    const SCHEDULER_ICON = "/images/product-icons/product-icon-2.svg";
    const COMPREHENSIVE_EXAM_ICON = "/images/product-icons/product-icon-512.svg";
    const ENTRANCE_EXAM_ICON = "/images/product-icons/product-icon-262144.svg";
    const STUDY_TOOLS_ICON = "/images/product-icons/product-icon-16.svg";
    const TRANSITION_COURSE_ICON = "/images/product-icons/product-icon-32768.svg";

    const PRECEPTOR_TRAINING_CONFIG = 64;
    const PARAMEDIC_TRANSITION_CONFIG = 32768;
    const EMTB_TRANSITION_CONFIG = 65536;
    const AEMT_TRANSITION_CONFIG = 131072;

    /**
     * @param $products
     * @param $user
     * @param $currentConfig
     *
     * @return array
     */
    public function sortProducts($products, $user, $currentConfig)
    {
        $sortedProducts = array();
        $upgradeableCount = 0;

        // go through each of the given products and get info about them and sort them into categories
        foreach ($products as $product) {
            // don't show preceptor training to students
            if ($product->id == 9) {
                continue;
            }

            $productInfo = array();

            $categoryInfo = $this->getCategoryInfo($product);

            $productInfo['product'] = $product;
            $productInfo['upgradeable'] = $this->canUpgrade($product, $user, $currentConfig);
            $productInfo['attempts'] = $this->canPurchaseMoreAttempts($product, $user, $currentConfig);

            $sortedProducts['products'][$categoryInfo['key']]['categoryInfo'] = $categoryInfo;
            $sortedProducts['products'][$categoryInfo['key']]['products'][] = $productInfo;

            if ($productInfo['upgradeable']) {
                $upgradeableCount++;
            }
        }

        $sortedProducts['upgradeableCount'] = $upgradeableCount;

        return $sortedProducts;
    }

    /**
     * @param $availableProducts
     * @param $user
     * @param $currentConfig
     * @param $orphanStudyTools
     * @return array
     */
    public
    function sortProductsForUpgrade($availableProducts, $user, $currentConfig, $orphanStudyTools)
    {
        $sortedProducts = array();

        // go through each of the given products and get info about them and sort them into categories
        foreach ($availableProducts as $product) {

            // see if this product should even be listed
            if ($this->canUpgrade($product, $user, $currentConfig, $orphanStudyTools)) {
                $productInfo = array();

                $categoryInfo = $this->getCategoryInfo($product);

                $productInfo['product'] = $product;
                $productInfo['attempts'] = $this->canPurchaseMoreAttempts($product, $user, $currentConfig);

                $sortedProducts[$categoryInfo['key']]['categoryInfo'] = $categoryInfo;
                $sortedProducts[$categoryInfo['key']]['products'][] = $productInfo;
            }
        }

        return $sortedProducts;
    }

    /**
     * @param $user
     * @param $currentConfig
     * @param $orphanStudyTools
     * @param $product
     *
     * @return bool
     */
    public function canUpgrade($product, $user, $currentConfig, $orphanStudyTools = false)
    {
        // if this is the Fisdap Study Tools program, only let them see products from the "Exam Practice" and
        // "Supplemental Learning" (ie; Medrills) categories
        if ($orphanStudyTools &&
            !($product->category->name == "Exam Practice" || $product->category->name == "Supplemental Learning")
        ) {
            return false;
        }

        // if this user can purchase more attempts, they can upgrade
        if ($this->canPurchaseMoreAttempts($product, $user, $currentConfig)) {
            return true;
        } else if ($currentConfig & $product->configuration) {
            // if they already have this product and they can't buy more attempts, they can't upgrade
            return false;
        }

        // if we've gotten this far, it's an upgradeable product!
        return true;
    }

    /**
     * @param $product
     *
     * @return mixed
     */
    public function getCategoryInfo($product)
    {
        $categoryInfo = array();

        switch ($product->category->id) {
            case 1:
                // Internship products are listed in separate categories
                switch ($product->configuration) {
                    case 1:
                    case 4096:
                        $categoryInfo['key'] = 3;
                        $categoryInfo['icon'] = self::SKILLS_TRACKER_ICON;
                        $categoryInfo['name'] = "Skills Tracker";
                        $categoryInfo['description'] = "Check with your instructor before upgrading to Skills Tracker.";
                        break;
                    case 2:
                    case 8192:
                    default:
                        $categoryInfo['key'] = 4;
                        $categoryInfo['icon'] = self::SCHEDULER_ICON;
                        $categoryInfo['name'] = "Scheduler";
                        $categoryInfo['description'] = "Check with your instructor before upgrading to Scheduler.";
                        break;
                }
                break;
            case 2:
                $categoryInfo['key'] = 2;
                $categoryInfo['icon'] = self::COMPREHENSIVE_EXAM_ICON;
                $categoryInfo['name'] = "Secure proctored exams";
                $categoryInfo['description'] = "Must be proctored by an instructor at your school.";
                break;
            case 3:
                $categoryInfo['key'] = 1;
                $categoryInfo['icon'] = self::STUDY_TOOLS_ICON;
                $categoryInfo['name'] = "Practice exams";
                $categoryInfo['description'] = "Practice quizzes and a full-length practice test you take on your own.";
                break;
            case 4:
                $categoryInfo['key'] = 5;
                $categoryInfo['icon'] = self::TRANSITION_COURSE_ICON;
                $categoryInfo['name'] = "Transition course";
                $categoryInfo['description'] = "An interactive course you can take on your own to transition to the National Education Standards.";
                break;
            default:
                $categoryInfo = false;
        }

        return $categoryInfo;
    }

    /**
     * @param $product
     * @param $user
     * @param $currentConfig
     * @return bool
     */
    public function canPurchaseMoreAttempts($product, $user, $currentConfig)
    {
        // see if the user already has this product, but can buy more attempts
        if (($currentConfig & $product->configuration) && $product->has_multiple_attempts) {

            // make sure the user has a Moodle user account in the context of this product
            $contexts = array();
            foreach ($product->moodle_quizzes as $test) {
                $contexts[$test->getContext()][] = $test;
            }
            foreach ($contexts as $context => $tests) {
                $checkedIds = \Fisdap\MoodleUtils::getMoodleUserIds(array($user->id => $user->username), $context);
                if (!$checkedIds[$user->id]) {
                    return false;
                }
            }

            // if we've gotten here, the user should be able to buy more attempts
            return true;

        }
        return false;
    }

    /**
     * Given a configuration, return an array of product icon html
     * @var integer $configuration
     * @var string $size how big the icons should be
     * @return array
     */
    public function getProductIcons($configuration)
    {
        $icons = array();

        if ($configuration & 4097) {
            $icons["skills_tracker"] = self::SKILLS_TRACKER_ICON;
        }

        if ($configuration & 8194) {
            $icons["scheduler"] = self::SCHEDULER_ICON;
        }

        if ($configuration & 3840) {
            $icons["testing"] = self::COMPREHENSIVE_EXAM_ICON;
        }

        if ($configuration & 144) {
            $icons["study_tools"] = self::STUDY_TOOLS_ICON;
        }

        if ($configuration & 229376) {
            $icons["transition_course"] = self::TRANSITION_COURSE_ICON;
        }

        if ($configuration & 262144) {
            $icons["entrance_exam"] = self::ENTRANCE_EXAM_ICON;
        }

        return $icons;
    }

    /**
     * Given a configuration, return an array of product html titles
     * @var integer $configuration
     * @return array
     */
    public function getProductTitles($configuration)
    {
        $titles = array();

        if ($configuration & 1) {
            $titles["skills_tracker"] .= "Skills Tracker (Unlimited)";
        }

        if ($configuration & 4096) {
            $titles["skills_tracker"] .= "Skills Tracker (Limited)";
        }

        if ($configuration & 2) {
            $titles["scheduler"] .= "Scheduler (Unlimited)";
        }

        if ($configuration & 8192) {
            $titles["scheduler"] .= "Scheduler (Limited)";
        }

        if ($configuration & 256) {
            $titles["testing"] .= "EMT Comprehensive Exams &#013;";
        }

        if ($configuration & 1024) {
            $titles["testing"] .= "EMT Unit Exams &#013;";
        }

        if ($configuration & 512) {
            $titles["testing"] .= "Paramedic Comprehensive Exams &#013;";
        }

        if ($configuration & 2048) {
            $titles["testing"] .= "Paramedic Unit Exams &#013;";
        }

        if ($configuration & 128) {
            $titles["study_tools"] .= "EMT Study Tools &#013;";
        }

        if ($configuration & 16) {
            $titles["study_tools"] .= "Paramedic Study Tools &#013;";
        }

        if ($configuration & 65536) {
            $titles["transition_course"] .= "EMT Transition Course";
        }

        if ($configuration & 131072) {
            $titles["transition_course"] .= "AEMT Transition Course";
        }

        if ($configuration & 32768) {
            $titles["transition_course"] .= "Paramedic Transition Course";
        }

        if ($configuration & 262144) {
            $titles["entrance_exam"] = "Entrance Exam";
        }

        return $titles;
    }

}