<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;


/**
 * Mailing List
 * 
 * @Entity
 * @Table(name="fisdap2_mailing_list")
 */
class MailingList extends Enumerated
{
    /**
     * @Column(type="string", nullable=true)
     */
    protected $mailchimp_name;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $mailchimp_id;
}