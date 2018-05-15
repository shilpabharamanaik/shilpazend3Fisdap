<?php namespace Fisdap\Data\Requirement;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;
use Fisdap\Data\Slot\SlotAssignmentRepository;
use Fisdap\Entity\Requirement;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Entity\RequirementNotification;
use Fisdap\EntityUtils;

/**
 * Class DoctrineRequirementRepository
 *
 * @package Fisdap\Data\Requirement
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineRequirementRepository extends DoctrineRepository implements RequirementRepository
{
    /**
     * Cached array totalling the account types for the logged in user's program
     */
    public static $accountTotals;

    /**
     * Cached array totalling the site types for the logged in user's program
     */
    public static $siteTotals;

    /**
     * @var array containing users that need their compliance computed
     */
    public $recomputeCompliance = array();

    /**
     * @var array containing users that need to be sent notifications regarding compliance
     */
    public $usersToNotify = array();


    public function getUniversalRequirements($form_options = false)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("distinct r, c")
            ->from('\Fisdap\Entity\Requirement', "r")
            ->join("r.category", "c")
            ->andWhere("r.universal = 1")
            ->orderBy("r.name");

        if ($form_options) {
            $data = $qb->getQuery()->getArrayResult();
            $return_vals = array();

            foreach ($data as $req) {
                if (!$return_vals[$req['category']['name']]) {
                    $return_vals[$req['category']['name']] = array();
                }

                $return_vals[$req['category']['name']][$req['id']] = $req['name'];
            }
        } else {
            $return_vals = $qb->getQuery()->getResult();
        }

        return $return_vals;
    }


    /**
     * @inheritdoc
     */
    public function getAttachedUserContextIdsByRequirements(array $req_ids, $program_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("r.id as requirement_id, ur.id as userContextId")
            ->from('\Fisdap\Entity\RequirementAttachment', "ra")
            ->join("ra.requirement", "r")
            ->join("ra.user_context", "ur")
            ->andWhere($qb->expr()->in('r.id', $req_ids))
            ->andWhere("ur.program = ?1")
            ->andWhere("ra.archived = 0")
            ->setParameter(1, $program_id);

        $return_vals = array();
        $data = $qb->getQuery()->getArrayResult();

        foreach ($req_ids as $id) {
            $return_vals[$id] = array();
        }

        foreach ($data as $attachment) {
            $return_vals[$attachment['requirement_id']][] = $attachment['userContextId'];
        }

        return $return_vals;
    }

    public function hasRequirement($req, $user_context)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("count(ra.id)")
            ->from('\Fisdap\Entity\RequirementAttachment', "ra")
            ->where("ra.user_context = ?1")
            ->andWhere("ra.requirement = ?2")
            ->setParameter(1, $user_context)
            ->setParameter(2, $req);

        return (bool) $qb->getQuery()->getSingleScalarResult();
    }

    public function toggleRequirement($programId, $requirementId, $active)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->update('\Fisdap\Entity\RequirementAssociation ras')
            ->set("ras.active", $active)
            ->andWhere('ras.program = ?1')
            ->andWhere('ras.requirement = ?2')
            ->setParameters(array(1 => $programId, 2 => $requirementId));

        $qb->getQuery()->execute();
    }

    /**
     * Get all requirements that are passed their expiration date, mark them as expired, and send emails if appropriate
     *
     * Run via cron in scripts/crons/expireRequirements.php
     *
     * @return array of user role IDs
     */
    public function expireRequirements()
    {
        //Get all requirement attachments past their expiration date that aren't yet marked expired
        //Students first
        $qb = $this->_em->createQueryBuilder();
        $qb->select("partial ra.{id, completed}, partial r.{id, name}, partial ur.{id}, partial u.{id, first_name, last_name, email}, n.send_non_compliant_assignment")
            ->from('\Fisdap\Entity\StudentLegacy', 's')
            ->join('s.user_context', 'ur')
            ->leftJoin('ur.requirement_attachments', 'ra')
            ->leftJoin("ur.user", "u")
            ->leftJoin("ra.requirement", "r")
            ->leftJoin("r.requirement_notifications", "n", "WITH", "n.program = ur.program")
            ->andWhere("ra.expiration_date <= ?1")
            ->andWhere("ra.archived = 0")
            ->andWhere("ra.expired = 0")
            ->andWhere("s.graduation_status = 1")
            ->andWhere("DATE_DIFF(ur.end_date, CURRENT_DATE()) >= -90")
            ->setParameter(1, date_create("now")->format("Y-m-d"));

        $studentResults = $qb->getQuery()->getResult();

        //Now instructors
        $qb = $this->_em->createQueryBuilder();
        $qb->select("partial ra.{id, completed}, partial r.{id, name}, partial ur.{id}, partial u.{id, first_name, last_name, email}, n.send_non_compliant_assignment")
            ->from('\Fisdap\Entity\RequirementAttachment', 'ra')
            ->leftJoin("ra.user_context", "ur")
            ->leftJoin("ur.user", "u")
            ->leftJoin("ra.requirement", "r")
            ->leftJoin("r.requirement_notifications", "n", "WITH", "n.program = ur.program")
            ->andWhere("ra.expiration_date <= ?1")
            ->andWhere("ra.archived = 0")
            ->andWhere("ra.expired = 0")
            ->andWhere('ur.role = 2')
            ->setParameter(1, date_create("now")->format("Y-m-d"));

        $instructorResults = $qb->getQuery()->getResult();

        $results = array_merge($studentResults, $instructorResults);

        //Loop over results and determine if compliance needs to be updated and then expire
        foreach ($results as $result) {
            $attachment = $result[0];
            $send_notification = $result["send_non_compliant_assignment"];

            $attachment->recordHistory(2, "expired");
            $attachment->expired = 1;

            //We only need to recompute compliance if the requirement was completed when it expired
            if ($attachment->completed == 1) {
                $this->recomputeCompliance[] = $attachment->user_context->id;
            }

            if ($send_notification) {
                $this->usersToNotify[$attachment->user_context->id][] = array(
                    "requirementName" => $attachment->requirement->name,
                    "status" => "expired",
                    "name" => $attachment->user_context->user->getName(),
                    "email" => $attachment->user_context->user->email,
                );
            }
        }

        $this->_em->flush();

        return $this->recomputeCompliance;
    }

    /**
     * Run via cron in scripts/crons/expireRequirements.php
     * @return array
     */
    public function markPastDueRequirements()
    {
        $qb = $this->_em->createQueryBuilder();

        //Get all requirement attachments due today that are incomplete, not expired and not archived
        $qb->select("partial ra.{id}, partial r.{id, name}, partial ur.{id}, partial u.{id, first_name, last_name, email}, n.send_non_compliant_assignment")
            ->from('\Fisdap\Entity\RequirementAttachment', "ra")
            ->leftJoin("ra.user_context", "ur")
            ->leftJoin("ur.user", "u")
            ->leftJoin("ra.requirement", "r")
            ->leftJoin("r.requirement_notifications", "n", "WITH", "n.program = ur.program")
            ->andWhere("ra.due_date = ?1")
            ->andWhere("ra.completed = 0")
            ->andWhere("ra.archived = 0")
            ->andWhere("ra.expired = 0")
            ->setParameter(1, date_create("now")->format("Y-m-d"));

        $results = $qb->getQuery()->getResult();

        foreach ($results as $result) {
            $attachment = $result[0];
            $send_notification = $result["send_non_compliant_assignment"];

            $attachment->recordHistory(2, "past due");
            $this->recomputeCompliance[] = $attachment->user_context->id;


            if ($send_notification) {
                $this->usersToNotify[$attachment->user_context->id][] = array(
                    "requirementName" => $attachment->requirement->name,
                    "status" => "past due",
                    "name" => $attachment->user_context->user->getName(),
                    "email" => $attachment->user_context->user->email,
                );
            }
        }

        $this->_em->flush();

        return $this->recomputeCompliance;
    }

    /**
     * Get all requirements with warnings that need to get sent out today
     */
    public function sendRequirementWarnings()
    {
        //Get all student requirements that need to get sent
        $qb = $this->_em->createQueryBuilder();
        $qb->select("ra.due_date, ra.expiration_date, ra.completed, r.name, ur.id as userContextId, u.id as user_id, u.first_name, u.last_name, u.email, w.warning_offset_value as days")
            ->from('\Fisdap\Entity\StudentLegacy', 's')
            ->join('st.user_context', 'ur')
            ->leftJoin('ur.requirement_attachments', 'ra')
            ->leftJoin("ur.user", "u")
            ->leftJoin("ra.requirement", "r")
            ->leftJoin('r.requirement_associations', 'ras', 'WITH', 'ras.program = ur.program')
            ->leftJoin("r.requirement_notifications", "n", "WITH", "n.program = ur.program")
            ->leftJoin("n.warnings", "w")
            ->andWhere("w.send_warning_notification = 1")
            ->andWhere("(DATE_ADD(CURRENT_DATE(), w.warning_offset_value, 'DAY') = ra.due_date AND ra.completed = 0) OR (DATE_ADD(CURRENT_DATE(), w.warning_offset_value, 'DAY') = ra.expiration_date AND ra.completed = 1)")
            ->andWhere("ra.archived = 0")
            ->andWhere("ra.expired = 0")
            ->andWhere("ras.active = 1")
            ->andWhere("s.graduation_status = 1")
            ->andWhere("DATE_DIFF(ur.end_date, CURRENT_DATE()) >= -90")
            ->orderBy("w.warning_offset_value");

        $studentResults = $qb->getQuery()->getResult();


        //Get all instructor requirements that need to get sent
        $qb = $this->_em->createQueryBuilder();
        $qb->select("ra.due_date, ra.expiration_date, ra.completed, r.name, ur.id as userContextId, u.id as user_id, u.first_name, u.last_name, u.email, w.warning_offset_value as days")
            ->from('\Fisdap\Entity\RequirementAttachment', "ra")
            ->leftJoin("ra.user_context", "ur")
            ->leftJoin('ur.requirement_attachments', 'ra')
            ->leftJoin("ur.user", "u")
            ->leftJoin("ra.requirement", "r")
            ->leftJoin('r.requirement_associations', 'ras', 'WITH', 'ras.program = ur.program')
            ->leftJoin("r.requirement_notifications", "n", "WITH", "n.program = ur.program")
            ->leftJoin("n.warnings", "w")
            ->andWhere("ur.role = 2")
            ->andWhere("w.send_warning_notification = 1")
            ->andWhere("(DATE_ADD(CURRENT_DATE(), w.warning_offset_value, 'DAY') = ra.due_date AND ra.completed = 0) OR (DATE_ADD(CURRENT_DATE(), w.warning_offset_value, 'DAY') = ra.expiration_date AND ra.completed = 1)")
            ->andWhere("ra.archived = 0")
            ->andWhere("ra.expired = 0")
            ->andWhere("ras.active = 1")
            ->orderBy("w.warning_offset_value");

        $instructorResults = $qb->getQuery()->getResult();

        $results = array_merge($studentResults, $instructorResults);

        $usersToNotify = array();

        foreach ($results as $result) {
            $usersToNotify[$result['userContextId']][] = array(
                "requirementName" => $result['name'],
                "status" => $result['completed'] == 1 ? "expiring" : "due",
                "name" => $result['first_name'] . " " . $result['last_name'],
                "email" => $result['email'],
                //"date" => $result['completed'] == 1 ? $result['expiration_date']->format("M j, Y") : $result['due_date']->format("M j, Y"),
                "days" => $result['days'],
            );
        }

        RequirementNotification::sendWarnings($usersToNotify);
    }

    /**
     * Given one or several UserContext IDs, update their compliance based
     * on their current requirements, and current shifts.
     * todo - refactor - SRP violation - move to a service
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function updateCompliance($userContextIds)
    {
        if (!is_array($userContextIds)) {
            $userContextIds = [$userContextIds];
        }

        foreach ($userContextIds as $userContextId) {
            $sites = [];
            $user_context = EntityUtils::getEntity("UserContext", $userContextId);

            // Get the user's slot assignments from today and onward

            /** @var SlotAssignmentRepository $slotAssignmentRepository */
            $slotAssignmentRepository = EntityUtils::getRepository("SlotAssignment");
            $assignments = $slotAssignmentRepository->getUserContextAssignmentsByDate($userContextId, new \DateTime());

            // Loop over slot assignments to get all sites they're going to
            foreach ($assignments as $assignment) {
                $sites[$assignment->slot->event->site->id][] = $assignment;
            }

            foreach ($sites as $siteId => $siteAssignments) {
                $siteCompliant = $this->isProgramSiteCompliant($userContextId, $siteId, $user_context->program->id);
                $globalSite = $user_context->program->sharesSite($siteId);
                $globalSiteCompliant = ($globalSite) ? $this->isGlobalSiteCompliant($userContextId, $siteId) : 1;

                foreach ($siteAssignments as $assignment) {
                    $assignment->compliant = $siteCompliant;
                    $assignment->global_site_compliant = $globalSiteCompliant;
                }
            }
        }

        $this->_em->flush();
    }

    public function isCompliant($userContextId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("count(ra.id)")
            ->from('\Fisdap\Entity\RequirementAttachment', "ra")
            ->andWhere("ra.user_context = ?1")
            ->andWhere("(ra.completed = 0 OR ra.expiration_date <= ?2) AND ra.due_date <= ?2")
            ->andWhere("ra.archived = 0")
            ->setParameter(1, $userContextId)
            ->setParameter(2, date_create("now")->format("Y-m-d"));

        $result = $qb->getQuery()->getSingleScalarResult();

        return ($result == 0);
    }


    /**
     * @inheritdoc
     */
    public function isProgramCompliant($userContextId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("count(ra.id)")
            ->from('\Fisdap\Entity\RequirementAttachment', "ra")
            ->join("ra.requirement", "r")
            ->join("r.requirement_associations", "ras")
            ->join("ra.user_context", "ur")
            ->andWhere("ra.user_context = ?1")
            ->andWhere("(ra.completed = 0 OR ra.expiration_date <= ?2) AND ra.due_date <= ?2")
            ->andWhere("ra.archived = 0")
            ->andWhere("ras.program = ur.program")
            ->andWhere("ras.site IS NULL")
            ->andWhere("ras.active = 1")
            ->setParameter(1, $userContextId)
            ->setParameter(2, date_create("now")->format("Y-m-d"));

        $result = $qb->getQuery()->getSingleScalarResult();

        return ($result == 0);
    }


    /**
     * @inheritdoc
     */
    public function isProgramSiteCompliant($userContextId, $siteId, $program_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("distinct(r.id)")
            ->from('\Fisdap\Entity\RequirementAttachment', "ra")
            ->join("ra.requirement", "r")
            ->join("r.requirement_associations", "ras")
            ->join("ra.user_context", "ur")
            ->andWhere("ra.user_context = ?1")
            ->andWhere("(ra.completed = 0 OR ra.expiration_date <= ?2) AND ra.due_date <= ?2")
            ->andWhere("ra.archived = 0")
            ->andWhere("ras.program = ur.program")
            ->andWhere("ras.site = ?3 OR ras.site IS NULL")
            ->andWhere("ras.active = 1")
            ->setParameters(array(
                1 => $userContextId,
                2 => date_create("now")->format("Y-m-d"),
                3 => $siteId));

        $result = $qb->getQuery()->getResult();

        // we're not clean about deleting associations that no longer matter, so we need to be sure we only return ones that do matter
        $req_associations = $this->getRequirementAssociations($program_id);

        $relevant_attachments = array();
        foreach ($result as $id) {
            // getResult seems to have reformatted the way it returns the result
            if (is_array($id)) {
                $id = array_shift($id);
            }
            if ($req_associations[$id]) {
                // if this req has associations, it's relevant
                $relevant_attachments[] = $id;
            }
        }

        return (count($relevant_attachments) == 0);
    }

    public function isLocalSiteCompliant($userContextId, $siteId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("count(ra.id)")
            ->from('\Fisdap\Entity\RequirementAttachment', "ra")
            ->join("ra.requirement", "r")
            ->join("r.requirement_associations", "ras")
            ->join("ra.user_context", "ur")
            ->andWhere("ra.user_context = ?1")
            ->andWhere("(ra.completed = 0 OR ra.expiration_date <= ?2) AND ra.due_date <= ?2")
            ->andWhere("ra.archived = 0")
            ->andWhere("ras.program = ur.program")
            ->andWhere("ras.site = ?3")
            ->andWhere("ras.active = 1")
            ->setParameters(array(
                1 => $userContextId,
                2 => date_create("now")->format("Y-m-d"),
                3 => $siteId));

        $result = $qb->getQuery()->getSingleScalarResult();

        return ($result == 0);
    }


    /**
     * @inheritdoc
     */
    public function isGlobalSiteCompliant($userContextId, $siteId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("count(ra.id)")
            ->from('\Fisdap\Entity\RequirementAttachment', "ra")
            ->join("ra.requirement", "r")
            ->join("r.requirement_associations", "ras")
            ->join("ra.user_context", "ur")
            ->join("ras.site", "site")
            ->join("site.site_shares", "ss")
            ->andWhere("ras.site = ?3")
            ->andWhere("ra.user_context = ?1")
            ->andWhere("(ra.completed = 0 OR ra.expiration_date <= ?2) AND ra.due_date <= ?2")
            ->andWhere("ra.archived = 0")
            ->andWhere("ss.approved = 1")
            ->andWhere("ss.program = ur.program")
            ->andWhere("ras.global = 1")
            ->andWhere("ras.active = 1")
            ->setParameter(1, $userContextId)
            ->setParameter(2, date_create("now")->format("Y-m-d"))
            ->setParameter(3, $siteId);

        $result = $qb->getQuery()->getSingleScalarResult();

        return ($result == 0);
    }

    /**
     * Get all requirements tied to a given site in a given program
     */
    public function getLocalRequirementsBySite($siteId, $programId, $active = 1)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("distinct r")
            ->from('\Fisdap\Entity\Requirement', "r")
            ->join("r.requirement_associations", "ras")
            ->andWhere("ras.program = ?1")
            ->andWhere("ras.site = ?2")
            ->andWhere("ras.active = ?3")
            ->setParameters(array(1 => $programId, 2 => $siteId, 3 => $active));

        return $qb->getQuery()->getResult();
    }

    /**
     * Get all requirements tied to a given site in a given program's sharing network
     * that are marked as global
     */
    public function getGlobalRequirementsBySite($siteId, $programId, $active = 1)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("distinct r")
            ->from('\Fisdap\Entity\Requirement', "r")
            ->join("r.requirement_associations", "ras")
            ->join("ras.site", "site")
            ->join("site.site_shares", "ss")
            ->andWhere("ss.program = ?1")
            ->andWhere("ras.site = ?2")
            ->andWhere("ras.active = ?3")
            ->andWhere("ras.global = 1")
            ->andWhere("ss.approved = 1")
            ->setParameters(array(1 => $programId, 2 => $siteId, 3 => $active));

        return $qb->getQuery()->getResult();
    }

    public function getAllSiteRequirements($program_id, $filters = array())
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("distinct r, cat")
            ->from('\Fisdap\Entity\Requirement', "r")
            ->join("r.category", "cat")
            ->leftJoin("r.requirement_associations", "ras")
            ->leftJoin("ras.site", "s")
            ->leftJoin('s.program_site_associations', 'psa')
// 		   ->andWhere("ras.global = 0")
            //->andWhere('psa.active = true')
            ->andWhere('psa.program = ?1')
            ->andWhere('ras.program = ?1')
            ->setParameters(array(1 => $program_id));

        //Apply additional filters
        $qb = $this->applyRequirementFilters($qb, $filters);

        return $qb->getQuery()->getResult();
    }

    public function getAllProgramRequirements($program_id, $filters = array())
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("distinct r, cat")
            ->from('\Fisdap\Entity\Requirement', "r")
            ->join("r.requirement_associations", "ras")
            ->join("r.category", "cat")
            ->andWhere("ras.program = ?1")
            ->andWhere("ras.site IS NULL");
        $qb->setParameters(array(1 => $program_id));

        // remove site filtering info, since we're looking for program requirements
        $filters['sites'] = array();

        // Apply additional filters
        $qb = $this->applyRequirementFilters($qb, $filters);

        return $qb->getQuery()->getResult();
    }

    public function getAllUniversalAssocations($program_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("distinct r.id")
            ->from('\Fisdap\Entity\Requirement', "r")
            ->join("r.requirement_associations", "ras")
            ->andWhere("ras.program = ?1")
            ->andWhere("r.universal = true");
        $qb->setParameters(array(1 => $program_id));

        return $qb->getQuery()->getArrayResult();
    }

    public function getAllGlobalRequirementsByProgram($program_id, $filters = array())
    {
        $site_ids = EntityUtils::getRepository('SiteLegacy')->getSharedSites($program_id);

        if (count($site_ids) > 0) {
            $qb = $this->_em->createQueryBuilder();

            $qb->select("distinct r, cat")
                ->from('\Fisdap\Entity\Requirement', "r")
                ->join("r.requirement_associations", "ras")
                ->join("ras.site", "site")
                ->join("r.category", "cat")
                ->andWhere("ras.site IN (" . implode(",", $site_ids) . ")")
                ->andWhere("ras.active = 1")
                ->andWhere("ras.global = 1");

            //Apply additional filters
            $qb = $this->applyRequirementFilters($qb, $filters);

            return $qb->getQuery()->getResult();
        }

        return;
    }

    /**
     * @param int $program_id
     * @param array|null $site_ids
     * @param array $filters
     * @return mixed
     */
    public function getSharedRequirementsByProgram($program_id, $site_ids, $filters = array())
    {
        if (count($site_ids) > 0) {
            $qb = $this->_em->createQueryBuilder();

            $qb->select("distinct r, cat")
                ->from('\Fisdap\Entity\Requirement', "r")
                ->join("r.requirement_associations", "ras")
                ->join("ras.site", "site")
                ->join("r.category", "cat")
                ->andWhere("ras.site IN (" . implode(",", $site_ids) . ")")
                ->andWhere("ras.active = 1")
                ->andWhere("ras.global = 1")
                ->andWhere("ras.program != ?1");
            $qb->setParameters(array(1 => $program_id));

            //Apply additional filters
            $qb = $this->applyRequirementFilters($qb, $filters);

            return $qb->getQuery()->getResult();
        }

        return;
    }

    private function applyRequirementFilters($qb, $filters)
    {
        if (isset($filters['active'])) {
            $active = $filters['active'];
        } else {
            $active = true;
        }

        if ($active) {
            $qb->andWhere("ras.active = true");
        }

        if (array_key_exists("universal", $filters)) {
            if ($filters['universal']) {
                $qb->andWhere("r.universal = true");
            }
        }

        if (array_key_exists("sites", $filters) && count($filters['sites'])) {
            $qb->join("ras.site", "site")
                ->andWhere($qb->expr()->in('site.id', $filters['sites']));
        }

        if (array_key_exists("category", $filters) && count($filters['category'])) {
            $qb->andWhere($qb->expr()->in('cat.id', $filters['category']));
        }

        //If either the account type filter OR user role filter is set, make sure to join the appropriate tables
        if ((array_key_exists("userContexts", $filters) && count($filters['userContexts'])) || (array_key_exists("accountType", $filters) && count($filters['accountType']))) {
            $qb->join("r.requirement_attachments", "ra")
                ->join("ra.user_context", "ur");
        }

        if (array_key_exists("userContexts", $filters) && count($filters['userContexts'])) {
            $qb->andWhere($qb->expr()->in('ur.id', $filters['userContexts']));
        }

        if (array_key_exists("accountType", $filters) && count($filters['accountType'])) {
            //Do some funky logic if instructor was selected in the account type field
            if (in_array(0, $filters['accountType'])) {
                //if instructors are the only account type selected, restrict to just instructors
                if (count($filters['accountType']) == 1) {
                    $qb->andWhere("ur.role = 2");
                } else {
                    $qb->andWhere($qb->expr()->orX($qb->expr()->in('ur.certification_level', $filters['accountType']), "ur.certification_level IS NULL"));
                }
            } else {
                $qb->andWhere($qb->expr()->in('ur.certification_level', $filters['accountType']));
            }
        }



        return $qb;
    }

    public function getFormOptions($program_id, $include_program_level = true, $include_site_level = true, $include_shared_level = true)
    {
        $requirements = $this->getRequirements($program_id, $include_program_level, $include_site_level, $include_shared_level);
        $options = array('Site' => array(), 'Program' => array(), 'Shared' => array());

        if ($include_site_level && $requirements['site_level']) {
            foreach ($requirements['site_level'] as $site_req) {
                $options['Site'][$site_req->id] = $site_req->name;
            }
        }

        if ($include_program_level && $requirements['program_level']) {
            foreach ($requirements['program_level'] as $prog_req) {
                $options['Program'][$prog_req->id] = $prog_req->name;
            }
        }

        if ($include_shared_level && $requirements['shared_level']) {
            foreach ($requirements['shared_level'] as $prog_req) {
                $options['Shared'][$prog_req->id] = $prog_req->name;
            }
        }

        return $options;
    }

    public function getRequirements($program_id, $include_program_level = true, $include_site_level = true, $include_shared_level = true, $just_ids = false, $filters = array())
    {
        if ($include_site_level) {
            $site_requirements = $this->getAllSiteRequirements($program_id, $filters);
        }

        if ($include_program_level) {
            $program_requirements = $this->getAllProgramRequirements($program_id, $filters);
        }

        if ($include_shared_level) {
            $shared_requirements = $this->getAllGlobalRequirementsByProgram($program_id, $filters);
        }

        if ($just_ids) {
            $req_ids = array();
            if ($site_requirements) {
                foreach ($site_requirements as $req) {
                    $req_ids[] = $req->id;
                }
            }
            if ($program_requirements) {
                foreach ($program_requirements as $req) {
                    $req_ids[] = $req->id;
                }
            }
            if ($shared_requirements) {
                foreach ($shared_requirements as $req) {
                    $req_ids[] = $req->id;
                }
            }
            $return_array = array_unique($req_ids);
        } else {
            $return_array = array("site_level" => $site_requirements, "program_level" => $program_requirements, "shared_level" => $shared_requirements);
        }

        return $return_array;
    }

    // get an array of requirements and all the associations for a given program, including shared requirements
    public function getRequirementAssociations($program_id)
    {
        $program_reqs = array();

        // get all program requirement associations
        $pqb = $this->_em->createQueryBuilder();
        $pqb->select("partial r.{id}")
            ->from('\Fisdap\Entity\Requirement', "r")
            ->join("r.requirement_associations", "ra")
            ->andWhere("ra.program = ?1")
            ->andWhere("ra.active = true")
            ->andWhere("ra.site is null")
            ->setParameters(array(1 => $program_id));
        $program_requirement_associations = $pqb->getQuery()->getArrayResult();

        // add program requirements to the array
        foreach ($program_requirement_associations as $pra) {
            $program_reqs[$pra['id']]['program'] = true;
        }

        // get all site requirement associations
        $sqb = $this->_em->createQueryBuilder();
        $sqb->select("partial r.{id},".
            "partial ra.{id, global},".
            "partial p.{id}, ".
            "partial s.{id, name, type}")
            ->from('\Fisdap\Entity\Requirement', "r")
            ->leftJoin("r.requirement_associations", "ra")
            ->join("ra.program", "p")
            ->join("ra.site", "s")
            ->andWhere("ra.active = true")
            ->andWhere('ra.program = ?1')
            ->setParameters(array(1 => $program_id));
        $site_requirement_associations = $sqb->getQuery()->getArrayResult();

        // get the shared requirements, too
        $shared_site_ids = EntityUtils::getRepository('SiteLegacy')->getSharedSites($program_id);
        if (count($shared_site_ids) > 0) {
            $shqb = $this->_em->createQueryBuilder();
            $shqb->select("partial r.{id},".
                "partial ra.{id, global},".
                "partial p.{id},".
                "partial s.{id, name, type}")
                ->from("\Fisdap\Entity\Requirement", "r")
                ->join("r.requirement_associations", "ra")
                ->leftJoin("ra.program", "p")
                ->leftJoin("ra.site", "s")
                ->andWhere("ra.active = true")
                ->andWhere('ra.program != ?1')
                ->andWhere('ra.global = 1')
                ->andWhere("ra.site IN (" . implode(",", $shared_site_ids) . ")")
                ->setParameters(array(1 => $program_id));
            $shared_site_requirement_associations = $shqb->getQuery()->getArrayResult();

            // add the shared requirements to the array
            foreach ($shared_site_requirement_associations as $shared_req_association) {
                $req_id = $shared_req_association['id'];
                $newReq = true;

                foreach ($site_requirement_associations as $key => $sra) {
                    // if this req is already part of the array, just add the association(s)
                    if ($req_id == $sra['id']) {
                        foreach ($shared_req_association['requirement_associations'] as $association) {
                            $site_requirement_associations[$key]['requirement_associations'][] = $association;
                        }
                        $newReq = false;
                    }
                }

                if ($newReq) {
                    $site_requirement_associations[] = $shared_req_association;
                }
            }
        }

        // add site requirements to the array
        foreach ($site_requirement_associations as $sra) {
            $req_id = $sra['id'];
            $global = array();
            $local = array();

            foreach ($sra['requirement_associations'] as $association) {

                // skip this association if it's not shared with this program
                if ($association['program']['id'] != $program_id && !$association['global']) {
                    continue;
                }

                $site_id = $association['site']['id'];
                $site_name = $association['site']['name'];
                $site_type = $association['site']['type'];
                if ($association['global']) {
                    $global[$site_id] = true;
                } else {
                    $local[$site_id] = true;
                }
                $program_reqs[$req_id]['site'][$site_type][$site_id] = array('name' => $site_name,
                                                                             'type' => $site_type,
                                                                             'global' => $global[$site_id],
                                                                             'local' => $local[$site_id]);
            }

            // now that all the associations are added, sort the array by site type then name so's it's pretty in a list
            if (count($program_reqs[$req_id]['site']) > 0) {
                @ksort($program_reqs[$req_id]['site']);
                foreach ($program_reqs[$req_id]['site'] as $type => $type_group) {
                    @uasort($type_group, array('self', 'sortAssociationsBySiteName'));
                    $program_reqs[$req_id]['site'][$type] = $type_group;
                }
            }
        }

        return $program_reqs;
    }

    public static function sortAssociationsBySiteName($a, $b)
    {
        return strcmp($a['name'], $b['name']);
    }

    // get all the appropriate attachments for a given user
    public function getAttachments($userContextId, $status, $active)
    {
        $userContext = EntityUtils::getEntity('UserContext', $userContextId);
        $qb = $this->_em->createQueryBuilder();

        //Get all requirement attachments
        $qb->select("distinct ra")
            ->from('\Fisdap\Entity\RequirementAttachment', "ra")
            ->join("ra.requirement", "r")
            ->join("r.requirement_associations", "ras")
            ->join("ra.user_context", "ur")
            ->where("ra.user_context = ?1")
            ->andWhere("ras.active = 1")
            ->setParameter(1, $userContextId)
            ->orderBy("r.name");

        if ($status == 'non-compliant-only') {
            // missing and past due date OR expired
            $qb->andWhere("(ra.completed = false AND ra.due_date <= ?2) OR (ra.expired = 1 OR ra.expiration_date <= ?2)")
                ->setParameter(2, date_create("now")->format("Y-m-d"));
        }
        if ($status == 'compliant-only') {
            // completed and not expired
            $qb->andWhere("(ra.completed = true AND (ra.expiration_date >= ?2 OR ra.expiration_date IS NULL))")
                ->setParameter(2, date_create("now")->format("Y-m-d"));
        }
        if ($status == 'pending') {
            // missing and not past due
            $qb->andWhere("(ra.completed = false AND ra.due_date > ?2)")
                ->setParameter(2, date_create("now")->format("Y-m-d"));
        }
        if ($active) {
            $qb->andWhere("ra.archived = false");
        }

        $attachments = $qb->getQuery()->getResult();

        // now make sure these attachments are relevant, that is, that they are currently linked to the user's program
        $req_associations = $this->getRequirementAssociations($userContext->program->id);
        $relevant_attachments = array();
        foreach ($attachments as $attachment) {
            if ($req_associations[$attachment->requirement->id]) {
                // if this req has associations, it's relevant
                $relevant_attachments[] = $attachment;
            }
        }

        return $relevant_attachments;
    }

    /**
     * Get all attachments for a given requirement in a given program
     * and return them with hydrated user and certification info
     *
     * @param Requirement   $requirement
     * @param ProgramLegacy $program
     *
     * @return array
     */
    public function getAttachmentsByRequirementAndProgram(Requirement $requirement, ProgramLegacy $program)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('partial a.{id,archived,due_date,completed,expiration_date}, partial ur.{id}, partial u.{id,first_name,last_name}, partial c.{id,description,name}')
            ->from('\Fisdap\Entity\RequirementAttachment', 'a')
            ->join('a.user_context', 'ur')
            ->join('ur.user', 'u')
            ->leftJoin('ur.certification_level', 'c')
            ->andWhere('a.requirement = ?1')
            ->andWhere('ur.program = ?2')
            ->setParameters([1 => $requirement, 2 => $program]);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get all the user role ids attached to a given requirment
     * @param integer $requirement_id
     * @param integer $program_id
     * @return array of UserContext IDs
     */
    public function getUserContextIdsByRequirement($requirement_id, $program_id = null)
    {
        $qb = $this->_em->createQueryBuilder();

        //Get all requirement attachments
        $qb->select("ur.id")
            ->from('\Fisdap\Entity\RequirementAttachment', "ra")
            ->join("ra.user_context", "ur")
            ->andWhere("ra.requirement = ?1")
            ->andWhere("ra.archived = 0")
            ->setParameter(1, $requirement_id);

        if (!is_null($program_id)) {
            $qb->andWhere("ur.program = ?2")
                ->setParameter(2, $program_id);
        }

        return array_map("current", $qb->getQuery()->getScalarResult());
    }

    public function getNonCompliantAttachmentsByProgram($program_id)
    {
        $qb = $this->_em->createQueryBuilder();

        // we're not clean about deleting associations that no longer matter, so we need to be sure we only return ones that do matter
        $req_associations = $this->getRequirementAssociations($program_id);

        $qb->select("distinct partial ra.{id}, partial ur.{id,end_date}, partial c.{id,description}, partial p.{id}")
            ->from('\Fisdap\Entity\RequirementAttachment', "ra")
            ->join("ra.requirement", "r")
            ->join('r.requirement_associations', 'ras')
            ->join("ra.user_context", "ur")
            ->join("ur.user", "u")
            ->leftJoin("ur.certification_level", "c")
            ->join("ur.program", "p")
            ->andWhere("ras.active = 1")
            ->andWhere("ur.program = ?1")
            ->andWhere("ra.archived = 0")
            ->orderBy("u.last_name, u.first_name")
            ->setParameters(array(
                1 => $program_id));

        // missing and past due date OR expired
        $qb->andWhere("(ra.completed = 0 AND ra.due_date <= ?2) OR ra.expiration_date <= ?2")
            ->andWhere($qb->expr()->in('r.id', '?3'))
            ->setParameter(2, date_create("now")->format("Y-m-d"))
            ->setParameter(3, array_keys($req_associations));


        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function getGlobalNonCompliantAttachmentsBySite($program_id, $global_network_assocations, $network_program_ids, $order_by_names)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("ra, ur")
            ->from('\Fisdap\Entity\RequirementAttachment', "ra")
            ->join("ra.requirement", "r")
            ->join("r.requirement_associations", "ras")
            ->join("ra.user_context", "ur")
            ->join("ur.user", "u")
            ->join("ur.program", "p");

        if (!$order_by_names) {
            $qb->join("ur.certification_level", "cert");
        }

        $qb->andWhere("ras.id IN (" . implode(",", $global_network_assocations) . ")")
            ->andWhere("p.id != ?2")
            ->andWhere("p.id IN (" . implode(",", $network_program_ids) . ")")
            ->andWhere("ra.archived = 0")
            ->andWhere("ras.active = 1");

        if ($order_by_names) {
            $qb->orderBy("u.last_name, u.first_name");
        } else {
            $qb->orderBy("cert.description");
        }

        $qb->andWhere("(ra.completed = 0 OR ra.expiration_date <= ?1) AND ra.due_date <= ?1")
            ->setParameter(1, date_create("now")->format("Y-m-d"))
            ->setParameter(2, $program_id);

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    // get all the appropriate attachments for a given user and site, inclusing program-wide attachments
    public function getAttachmentsBySite($userContextId, $site_id, $status, $global, $active)
    {
        $qb = $this->_em->createQueryBuilder();


        $qb->select("ra")
            ->from('\Fisdap\Entity\RequirementAttachment', "ra")
            ->join("ra.requirement", "r")
            ->join("r.requirement_associations", "ras")
            ->join("ra.user_context", "ur")
            ->andWhere("ra.user_context = ?1")
            ->andWhere("ras.program = ur.program")
            ->andWhere("ras.site = ?2 OR ras.site IS NULL")
            ->andWhere("ras.active = 1")
            ->andWhere("ra.archived = 0")
            ->orderBy("r.name")
            ->setParameters(array(
                    1 => $userContextId,
                    2 => $site_id));

        if ($status == 'non-compliant-only') {
            // missing and past due date OR expired
            $qb->andWhere("(ra.completed = false AND ra.due_date <= ?3) OR (ra.expired = true OR ra.expiration_date < ?3)")
                ->setParameter(3, date_create("now")->format("Y-m-d"));
        }
        if ($status == 'compliant-only') {
            // completed and not expired
            $qb->andWhere("(ra.completed = true AND (ra.expiration_date >= ?3 OR ra.expiration_date IS NULL))")
                ->setParameter(3, date_create("now")->format("Y-m-d"));
        }
        if ($status == 'pending') {
            // missing and not past due
            $qb->andWhere("(ra.completed = false AND ra.due_date > ?3)")
                ->setParameter(3, date_create("now")->format("Y-m-d"));
        }
        if ($active) {
            $qb->andWhere("ra.archived = false");
        }

        $result = $qb->getQuery()->getResult();

        // add in shared attachments, too, if necessary
        if ($global) {
            $shared = $this->getGlobalAttachmentsBySite($userContextId, $site_id, $status, $active);
            // make sure we don't have repeats
            foreach ($shared as $shared_req) {
                if (array_search($shared_req, $result) === false) {
                    $result[] = $shared_req;
                }
            }
        }


        return $result;
    }

    public function getGlobalAttachmentsBySite($userContextId, $site_id, $status, $active)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("ra")
            ->from('\Fisdap\Entity\RequirementAttachment', "ra")
            ->join("ra.requirement", "r")
            ->join("r.requirement_associations", "ras")
            ->join("ra.user_context", "ur")
            ->join("ras.site", "site")
            ->join("site.site_shares", "ss")
            ->andWhere("ras.site = ?2")
            ->andWhere("ra.user_context = ?1")
            ->andWhere("ss.approved = 1")
            ->andWhere("ss.program = ur.program")
            ->andWhere("ras.global = 1")
            ->andWhere("ra.archived = 0")
            ->orderBy("r.name")
            ->setParameters(array(
                    1 => $userContextId,
                    2 => $site_id));

        if ($status == 'non-compliant-only') {
            // missing and past due date OR expired
            $qb->andWhere("(ra.completed = false AND ra.due_date <= ?2) OR (ra.expired = true OR ra.expiration_date < ?3)")
                ->setParameter(3, date_create("now")->format("Y-m-d"));
        }
        if ($status == 'compliant-only') {
            // completed and not expired
            $qb->andWhere("(ra.completed = true AND (ra.expiration_date >= ?3 OR ra.expiration_date IS NULL))")
                ->setParameter(3, date_create("now")->format("Y-m-d"));
        }
        if ($status == 'pending') {
            // missing and not past due
            $qb->andWhere("(ra.completed = false AND ra.due_date > ?3)")
                ->setParameter(3, date_create("now")->format("Y-m-d"));
        }
        if ($active) {
            $qb->andWhere("ra.archived = false");
        }

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function getRequirementAttachmentsByUserContexts($userContextIds, $requirements = [])
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("ra.id, ra.completed, ra.due_date, ra.expiration_date, r.id as req_id, r.name as req_name, r.expires, ur.id as userContextId, u.first_name, u.last_name, c.description as user_context_certification")
            ->from('\Fisdap\Entity\UserContext', "ur")
            ->join("ur.user", "u")
            ->leftJoin("ur.certification_level", "c")
            ->join('ur.requirement_attachments', 'ra')
            ->join("ra.requirement", "r")
            ->where($qb->expr()->in('ur.id', $userContextIds))
            ->andWhere("ra.archived = 0");

        if (!empty($requirements)) {
            $qb->andWhere($qb->expr()->in('r.id', $requirements));
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function getSiteRequirementAssociations($requirementId, $siteId, $programId, $includeGlobal)
    {
        // include sharing if applicable
        $programClause = $includeGlobal ? "ra.program = ?3 OR ra.global = 1" : "ra.program = ?3";

        // let's get site association for this program
        $qb = $this->_em->createQueryBuilder();
        $qb->select("ra")
            ->from('\Fisdap\Entity\RequirementAssociation', "ra")
            ->andWhere("ra.requirement = ?1")
            ->andWhere("ra.site = ?2")
            ->andWhere($programClause)
            ->setParameters(array(1 => $requirementId, 2 => $siteId, 3 => $programId));

        $associations = $qb->getQuery()->getResult();

        return $associations;
    }

    public function getAttachmentSummariesByRequirement($requirementId, $programId, $networkOnly = false)
    {
        $requirementSummary = array();
        $program = $this->_em->getRepository('\Fisdap\Entity\ProgramLegacy')->findOneBy(array('id' => $programId));

        //Start by getting and caching the counts of every account type in the program
        if (empty(self::$accountTotals)) {
            $qb = $this->_em->createQueryBuilder();
            $qb->select("count(ur.id) as total, c.description")
                ->from('\Fisdap\Entity\StudentLegacy', "s")
                ->join("s.user_context", "ur")
                ->join("ur.certification_level", "c")
                ->andWhere("ur.program = ?1")
                ->andWhere("s.graduation_status = 1")
                ->andWhere("DATE_DIFF(ur.end_date, CURRENT_DATE()) >= -90")
                ->groupBy("c.description")
                ->setParameter(1, $programId);

            $certifications = $qb->getQuery()->getScalarResult();
            foreach ($certifications as $cert) {
                self::$accountTotals[$cert['description']] = $cert['total'];
            }

            $qb = $this->_em->createQueryBuilder();
            $qb->select("count(ur.id) as total")
                ->from('\Fisdap\Entity\UserContext', "ur")
                ->andWhere("ur.program = ?1")
                ->andWhere("ur.role = 2")
                ->setParameter(1, $programId);
            self::$accountTotals["Instructor"] = $qb->getQuery()->getSingleScalarResult();
            $accountTotals = self::$accountTotals;
        } else {
            $accountTotals = self::$accountTotals;
        }

        //Next, let's get counts of every field,clinical,lab site in the system
        if (empty(self::$siteTotals)) {
            $qb = $this->_em->createQueryBuilder();
            $qb->select("count(psa.id) as total, s.type")
                ->from('\Fisdap\Entity\SiteLegacy', 's')
                ->join("s.program_site_associations", "psa")
                ->andWhere("psa.program = ?1")
                ->andWhere("psa.active = 1")
                ->groupBy("s.type")
                ->setParameter(1, $programId);

            $sites = $qb->getQuery()->getResult();
            foreach ($sites as $site) {
                self::$siteTotals[$site['type']] = $site['total'];
            }
            $siteTotals = self::$siteTotals;
        } else {
            $siteTotals = self::$siteTotals;
        }

        //Now get current attachment info for active students
        $qb = $this->_em->createQueryBuilder();
        $qb->select("u.first_name, u.last_name, r.id as role, c.description, ra.id, ur.id as userContextId")
            ->from('\Fisdap\Entity\StudentLegacy', 's')
            ->join('s.user_context', 'ur')
            ->join('ur.requirement_attachments', 'ra')
            ->join("ur.user", "u")
            ->join("ur.role", "r")
            ->leftJoin("ur.certification_level", "c")
            ->andWhere("ra.requirement = ?1")
            ->andWhere("ur.program = ?2")
            ->andWhere("ra.archived = 0")
            ->andWhere("s.graduation_status = 1")
            ->andWhere("DATE_DIFF(ur.end_date, CURRENT_DATE()) >= -90")
            ->setParameters(array(1 => $requirementId, 2 => $programId))
            ->orderBy("u.last_name, u.first_name");
        $studentAttachments = $qb->getQuery()->getResult();

        //Now get current attachment info for instructors
        $qb = $this->_em->createQueryBuilder();
        $qb->select("u.first_name, u.last_name, r.id as role, c.description, ra.id, ur.id as userContextId")
            ->from('\Fisdap\Entity\RequirementAttachment', 'ra')
            ->join('ra.user_context', 'ur')
            ->join("ur.user", "u")
            ->join("ur.role", "r")
            ->leftJoin("ur.certification_level", "c")
            ->andWhere("ra.requirement = ?1")
            ->andWhere("ur.program = ?2")
            ->andWhere("ra.archived = 0")
            ->andWhere("r.id = 2")
            ->setParameters(array(1 => $requirementId, 2 => $programId))
            ->orderBy("u.last_name, u.first_name");
        $instructorAttachments = $qb->getQuery()->getResult();

        $attachments = array_merge($studentAttachments, $instructorAttachments);

        //Loop over results so that we can group by certification_level/account type
        $account_types = EntityUtils::getRepository('CertificationLevel')->getFormOptions($program->profession->id);
        $account_types[] = "Instructor";
        $attachmentSummary = array();
        // set up the array with the account types in the right order
        foreach ($account_types as $id => $opt) {
            $attachmentSummary[$opt] = array();
        }
        // add attachment info to the summary
        foreach ($attachments as $attachment) {
            $attachment['description'] = $attachment['description'] ? $attachment['description'] : "Instructor";
            $attachmentSummary[$attachment['description']][] = array("role" => $attachment['role'],"name" => $attachment['first_name'] . " " . $attachment['last_name'], "userContextId" => $attachment['userContextId']);
        }
        //Now check to see if the count matches the total, if so, don't give a full list
        foreach ($attachmentSummary as $cert => $summary) {
            if (count($summary) == $accountTotals[$cert] && count($summary) > 0) {
                $attachmentSummary[$cert] = "All active " . $cert . "s";
            }
        }
        $requirementSummary['current_attachments'] = $attachmentSummary;

        //Now let's get auto assign summary info
        $qb = $this->_em->createQueryBuilder();
        $qb->select("c.description")
            ->from('\Fisdap\Entity\RequirementAutoAttachment', 'aa')
            ->leftJoin("aa.certification_level", "c")
            ->andWhere("aa.program = ?1")
            ->andWhere("aa.requirement = ?2")
            ->setParameters(array(1 => $programId, 2 => $requirementId));

        $autoAttachments = $qb->getQuery()->getResult();
        $autoAttachmentSummary = array();
        foreach ($autoAttachments as $autoAttachment) {
            $autoAttachmentSummary[] = "New " . ($autoAttachment['description'] ? $autoAttachment['description'] : "Instructor") . "s";
        }
        $requirementSummary['auto_attachments'] = $autoAttachmentSummary;

        // now let's get site info
        if ($networkOnly) {
            $programClause = "ras.program != ?2";
        } else {
            $programClause = "ras.program = ?2";
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select("distinct ras.id, ras.active, ras.global, p.name as program, s.id as site_id, s.type, s.name, psa.active as active_site")
            ->from('\Fisdap\Entity\RequirementAssociation', "ras")
            ->leftJoin("ras.site", "s")
            ->leftJoin("ras.program", "p")
            ->leftJoin("s.program_site_associations", "psa", "WITH", "psa.program = ?2")
            ->andWhere("ras.requirement = ?1")
            ->andWhere($programClause)
            ->setParameters(array(1 => $requirementId, 2 => $programId))
            ->orderBy("s.type, s.name");

        if ($networkOnly) {
            $qb->andWhere("ras.global = 1");
        }

        $associations = $qb->getQuery()->getResult();

        $associationSummary = array();
        if (count($associations) == 1 && !$associations[0]["name"]) {
            $associationSummary['program'] = true;
            $associationSummary['active'] = $associations[0]["active"];
        } else {
            $associationSummary['program'] = false;
            $associationSummary['active'] = $associations[0]["active"];
            foreach ($associations as $association) {
                if ($association['active_site'] == 1) {
                    if ($association['global'] == 1) {
                        $associationSummary['global'] = true;
                    }
                    $associationSummary['sites'][$association['type']][] = array("name" => $association['name'], "global" => $association['global']);
                    $associationSummary['sitesById'][$association['site_id']]['name'] = $association['name'];
                    $associationSummary['sitesById'][$association['site_id']]['type'] = $association['type'];
                    $associationSummary['sitesById'][$association['site_id']]['programs'][] = $association['program'];
                }
            }
        }

        //Now check to see if the count matches the total, if so, don't give a full list
        if ($associationSummary['sites']) {
            foreach ($associationSummary['sites'] as $type => $summary) {
                if (count($summary) == $siteTotals[$type]) {
                    $associationSummary['sites'][$type] = "All active " . $type . " sites";
                }
            }
        }


        $requirementSummary['associations'] = $associationSummary;

        return $requirementSummary;
    }

    public function getFullAttachmentHistory($requirementAttachment)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("distinct h")
            ->from('\Fisdap\Entity\RequirementHistory', 'h')
            ->andWhere("h.requirement_attachment = ?1 OR (h.requirement = ?2 AND h.requirement_attachment IS NULL)")
            ->orderBy("h.timestamp")
            ->setParameters(array(1 => $requirementAttachment, 2 => $requirementAttachment->requirement));

        return $qb->getQuery()->getResult();
    }

    public function getNotificationSettings($program_id, $requirement_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("n")
            ->from('\Fisdap\Entity\RequirementNotification', "n")
            ->where("n.program = ?1")
            ->andWhere("n.requirement = ?2")
            ->setParameters(array(1 => $program_id, 2 => $requirement_id));

        $result = $qb->getQuery()->getResult();
        return (count($result) == 1) ? $result[0] : false;
    }

    public function getNotificationDefaultsByProgram($program_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("n")
            ->from('\Fisdap\Entity\RequirementNotification', "n")
            ->where("n.program = ?1")
            ->andWhere("n.requirement IS NULL")
            ->setParameters(array(1 => $program_id));

        $result = $qb->getQuery()->getResult();
        //var_dump($result);
        return (count($result) == 1) ? $result[0] : false;
    }

    public function getAutoAttachmentSettings($program_id, $requirement_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("aa")
            ->from('\Fisdap\Entity\RequirementAutoAttachment', "aa")
            ->andWhere("aa.program = ?1")
            ->andWhere("aa.requirement = ?2")
            ->setParameters(array(1 => $program_id, 2 => $requirement_id));

        $autoAttachments = $qb->getQuery()->getResult();
        return $autoAttachments;
    }

    public function getAutoAttachmentDefaultsByProgram($program_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("aa")
            ->from('\Fisdap\Entity\RequirementAutoAttachment', "aa")
            ->andWhere("aa.program = ?1")
            ->andWhere("aa.requirement IS NULL")
            ->setParameters(array(1 => $program_id));

        $autoAttachments = $qb->getQuery()->getResult();
        return $autoAttachments;
    }

    public function getGlobalNetworkAssocationIds($site_id, $network_program_ids)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("ras.id")
            ->from('\Fisdap\Entity\Requirement', "r")
            ->join("r.requirement_associations", "ras")
            ->andWhere("ras.site = ?1")
            ->andWhere("ras.global = 1")
            ->andWhere("ras.active = 1")
            ->setParameter(1, $site_id);

        // Only do this if we have a list of $network_program_ids...
        if (count($network_program_ids) > 0) {
            $qb->andWhere("ras.program IN (" . implode(",", $network_program_ids) . ")");
        }

        $res = $qb->getQuery()->getResult();
        $return_val = array();
        foreach ($res as $data) {
            $return_val[] = $data['id'];
        }

        return $return_val;
    }
}
