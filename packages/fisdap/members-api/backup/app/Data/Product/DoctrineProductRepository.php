<?php namespace Fisdap\Data\Product;

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineProductRepository
 *
 * @package Fisdap\Data\Product
 */
class DoctrineProductRepository extends DoctrineRepository implements ProductRepository
{
    /**
     * @inheritdoc
     */
    public function getProducts(
        $exclude = 0,
        $include = false,
        $listOnly = false,
        $staff = false,
        $readOnly = true,
        $professionId = null
    ) {
        //Default to program profession if none given
        if (!$professionId) {
            $professionId = \Fisdap\Entity\ProgramLegacy::getCurrentProgram()->profession->id;
        }

        // Default to EMS if it's still NULL
        if (!$professionId) {
            $professionId = 1;
        }

        // Add profession to the list of criteria
        $criteria = array("profession" => $professionId);

        // If we're not requesting staff only products and we're not in read-only mode, exclude them upfront
        if (!$staff && !$readOnly) {
            $criteria['staff_only'] = $staff;
        }

        $results = $this->findBy($criteria);

        if ($include) {
            $includedResults = array();
        }

        //If we have products to exclude go thru them, because DQL is stupid
        if ($exclude > 0) {
            foreach ($results as $i => $result) {
                if ($result->configuration & $exclude) {
                    unset($results[$i]);
                    if ($include) {
                        if ($listOnly) {
                            $includedResults[] = $result->name;
                        } else {
                            $includedResults[] = $result;
                        }
                    }
                }
            }
        }

        if ($include) {
            if ($listOnly) {
                return implode(",<br />", $includedResults);
            } else {
                return $includedResults;

            }
        } else {
            return $results;
        }
    }

    /**
     * @inheritdoc
     */
    public function getProductsByCategory($category)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('pro')
            ->from('\Fisdap\Entity\Product', 'pro')
            ->where('pro.category = ?1')
            ->setParameter(1, $category);


        return $qb->getQuery()->getResult();
    }

    /**
     * @inheritdoc
     */
    public function getProductsWithMoodleCourses()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('pro')
            ->from('\Fisdap\Entity\Product', 'pro')
            ->where('pro.moodle_course_id is not null');

        return $qb->getQuery()->getResult();
    }


    /**
     * @inheritdoc
     */
    public function getProductsMatchingConfigAndCertLevel($configuration, $certificationLevelId = null)
    {

        //I hate to do this, but we have to. If the cert level is AEMT and the configuration matches internship products, set the cert level to paramedic for matching purposes.
        if ($certificationLevelId->id == 5 && ($configuration & 1 || $configuration & 2)) {
            $certificationLevelId = 3;
        }

        $qb = $this->createQueryBuilder('p');
        
        $qb->where('BIT_AND(p.configuration, ?1) > 0')
            ->setParameter(1, $configuration);
        
        if (!is_null($certificationLevelId)) {
            $qb->andWhere('p.certification_level = ?2')
                ->setParameter(2, $certificationLevelId);
        }
        
        return $qb->getQuery()->getResult();
    }
    

    /**
     * @inheritdoc
     */
    public function getFormOptions(
        $exclude = 0,
        $include = false,
        $listOnly = false,
        $staff = false,
        $readOnly = true,
        $professionId = null
    ) {
        $formOptions = array();
        $products = $this->getProducts($exclude, $include, $listOnly, $staff, $readOnly, $professionId);
        foreach ($products as $product) {
            $formOptions[$product->configuration] = $product->name;
        }
        asort($formOptions);

        return $formOptions;
    }

    
    /**
     * Get the packages that are still available for upgrade for a given cert level
     *
     * @param $certLevel
     * @param $config
     * @return array
     */
    public function getAvailablePackages($certLevel, $config)
    {
        $availablePackages = array();
        $packages = \Fisdap\EntityUtils::getRepository("ProductPackage")->findByCertification($certLevel);

        // go through each package; if the config contains none of those products, it is available for upgrade
        foreach ($packages as $package) {
            if (($config | $package->configuration) == ($config + $package->configuration)) {
                $availablePackages[] = $package;
            }
        }
        return $availablePackages;

    }

    /**
     * Get an array containing the names and descriptions of the products in the given configuration
     *
     * @param integer $configuration The bitwise code indicating which products we want
     * @param integer $professionId the same config bit may have different name/description depending on profession
     * @param boolean $student If this is a student, do not show instructor products, even if they're part of the config
     * @return array
     */
    public function getProductInfo($configuration, $professionId, $student = false)
    {
        $info = array();
        $products = $this->getProducts($configuration, true, false, false, true, $professionId);
        foreach ($products as $product) {
            // don't show students preceptor training
            if (!$student || ($student && $product->id != 9)) {
                $info[$product->id]['name'] = $product->name;
                $info[$product->id]['description'] = $product->description;
            }
        }

        return $info;
    }

    /**
     * Given a configuration, get a formatted list of product names
     * @param integer $configuration The bitwise code indicating which products we want
     * @param integer $professionId the same config bit may have different name/description depending on profession
     * @param boolean|false $student If this is a student, do not show instructor products, even if they're part of the config
     * @param string $format what format do we want the list in?
     * @return array|string
     */
    public function getProductList($configuration, $professionId, $student = false, $format = "string")
    {
        $list = array();
        $products = $this->getProductInfo($configuration, $professionId, $student);
        foreach ($products as $product) {
            $list[] = $product['name'];
        }

        switch ($format) {
            case "array":
                return $list;
            case "string":
            default:
                return implode(", ", $list);
        }

    }
}
