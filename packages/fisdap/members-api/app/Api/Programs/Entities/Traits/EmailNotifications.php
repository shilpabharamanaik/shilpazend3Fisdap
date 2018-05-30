<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\ORM\Mapping\Column;

/**
 * Class EmailNotifications
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait EmailNotifications
{
    /**
     * @var bool
     * @Column(name="BigBroStuReminders", type="boolean")
     */
    protected $send_late_shift_emails = true;

    /**
     * @var bool
     * @Column(name="SendStuEvents", type="boolean")
     */
    protected $send_critical_thinking_emails = true;


    /**
     * @return bool
     */
    public function getSendLateShiftEmails()
    {
        return $this->send_late_shift_emails;
    }


    /**
     * @param bool $send_late_shift_emails
     */
    public function setSendLateShiftEmails($send_late_shift_emails)
    {
        $this->send_late_shift_emails = $send_late_shift_emails;
    }


    /**
     * @return bool
     */
    public function getSendCriticalThinkingEmails()
    {
        return $this->send_critical_thinking_emails;
    }


    /**
     * @param bool $send_critical_thinking_emails
     */
    public function setSendCriticalThinkingEmails($send_critical_thinking_emails)
    {
        $this->send_critical_thinking_emails = $send_critical_thinking_emails;
    }
}
