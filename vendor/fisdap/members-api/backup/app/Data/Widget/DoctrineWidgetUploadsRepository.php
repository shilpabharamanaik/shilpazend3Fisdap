<?php namespace Fisdap\Data\Widget;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineWidgetUploadsRepository
 *
 * @package Fisdap\Data\Widget
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineWidgetUploadsRepository extends DoctrineRepository implements WidgetUploadsRepository
{
    public function getUploadedFilesForProgram($program, $user)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('partial u.{id, original_name, educators_allowed}, partial c.{id, name, description}, partial usr.{id, first_name, last_name}, partial ur.{id}, partial p.{id}')
            ->from('\Fisdap\Entity\WidgetUploads', 'u')
            ->join('u.uploader', 'usr')
            ->leftJoin('u.certification_levels', 'c')
            ->join('usr.userContexts', 'ur')
            ->join('u.program', 'p')
            ->where('p.id = ?1')
            ->setParameter(1, $program)
            ->orderBy('u.created', 'DESC');
        $uploads = $qb->getQuery()->getResult();

        $cleanUploads = array();

        foreach($uploads as $upload){
            if($user->isInstructor()){
                // An instructor should always be able to see their own uploaded files..
                if($user->id == $upload->uploader->id){
                    $cleanUploads[] = $upload;
                    // Otherwise only show the upload to instructors if the permission was set on the file...
                }elseif($upload->educators_allowed){
                    $cleanUploads[] = $upload;
                }
            }else{
                $cert = $user->getCurrentRoleData()->getCertification();
                if ($upload->certification_levels->contains($cert)) {
                    $cleanUploads[] = $upload;
                }
            }
        }

        return $cleanUploads;
    }
}
