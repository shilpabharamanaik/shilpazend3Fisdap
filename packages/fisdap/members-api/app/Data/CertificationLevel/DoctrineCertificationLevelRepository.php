<?php namespace Fisdap\Data\CertificationLevel;

use Fisdap\Data\Repository\DoctrineRepository;
use Fisdap\Data\Repository\RetrievesByName;
use Fisdap\Entity\Profession;

/**
 * Class DoctrineCertificationLevelRepository
 *
 * @package Fisdap\Data\CertificationLevel
 */
class DoctrineCertificationLevelRepository extends DoctrineRepository implements CertificationLevelRepository
{
    use RetrievesByName;


    /**
     * @param Profession|integer|array|null $profession
     * @return array
     *
     * @todo One day, typehint this function
     */
    public function getAllCertificationLevelInfo($profession = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('cl.id, cl.description, cl.abbreviation, cl.name, cl.configuration_blacklist, cl.bit_value')
            ->from('\Fisdap\Entity\CertificationLevel', 'cl');

        if (is_array($profession)) {
            $qb->where($qb->expr()->in("cl.profession", $profession));
        } elseif ($profession) {
            $qb->where("cl.profession = ?1")
                ->setParameter(1, $profession);
        }

        $qb->orderBy('cl.display_order', 'ASC');

        $results = $qb->getQuery()->getResult();

        // key by name (which is the shortened, system-y version of the cert level)
        $return = array();
        foreach ($results as $certLevel) {
            $return[$certLevel['name']] = $certLevel;
        }

        return $return;
    }

    /**
     * @param Profession|integer|array|null $profession
     * @return array
     *
     * @todo One day, typehint this function
     */
    public function getSortedFormOptions($profession = null)
    {
        $rawCerts = $this->getAllCertificationLevelInfo($profession);

        // then dump them into an array that a zend form element will understand
        $formOptions = array();
        foreach ($rawCerts as $index => $certOption) {
            $formOptions[$certOption['id']] = $certOption['description'];
        }

        return $formOptions;
    }

    /**
     * Get Certification Level info based on the defined $profession of the user and return it in an unsorted array
     * @param Profession|integer|array|null $profession
     * @return array $formOptions
     *
     * @todo One day, typehint this function
     */
    public function getFormOptions($profession)
    {
        $rawCerts = $this->getAllCertificationLevelInfo($profession);
        $formOptions = array();
        foreach ($rawCerts as $cert) {
            $formOptions[$cert['id']] = $cert['description'];
        }

        return $formOptions;
    }

    /**
     * Grab all the products in form-friendly format unless we exclude some (different than getFormOptions in that
     * a $professionId is not required)
     * @param integer $exclude configuration code of products to exlude
     * @param boolean $listOnly only return a HTML formatted list of products
     * @param boolean $staff get staff only accounts
     * @param boolean $readOnly is this being used to populate a form or read a list of products
     * @return array of \Fisdap\Entity\CertificationLevel
     */
    public function getAllFormOptions($exclude = 0, $include = false, $listOnly = false, $staff = false, $readOnly = true, $professionId = null)
    {
        $formOptions = array();
        $products = $this->getProducts($exclude, $include, $listOnly, $staff, $readOnly, $professionId);
        foreach ($products as $product) {
            $formOptions[$product->configuration] = $product->name;
        }
        asort($formOptions);

        return $formOptions;
    }
}
