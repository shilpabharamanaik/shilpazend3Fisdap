<?php namespace Fisdap\Api\Contact\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Illuminate\Mail\Mailer;
use libphonenumber\PhoneNumberFormat;
use Swagger\Annotations as SWG;

/**
 * A Job (Command) for sending the provided information to support and
 * responding back to the API user if it was sent or not.
 *
 * Class SendSupportEmail
 * @package Fisdap\Api\Contact\Jobs
 * @author  Isaac White <iwhite@fisdap.net>
 *
 * @SWG\Definition(
 *     definition="Contact-Us",
 *     required={"firstName", "lastName", "userRoleId", "email", "messageBody"}
 * )
 */
final class SendSupportEmail extends Job implements RequestHydrated
{
    /**
     * @var string
     * @SWG\Property(type="string", example="King")
     */
    public $firstName;

    /**
     * @var string
     * @SWG\Property(type="string", example="Kong")
     */
    public $lastName;

    /**
     * @var int
     * @SWG\Property(type="integer", example=1)
     */
    public $userRoleId;

    /**
     * @var string|null
     * @SWG\Property(type="string", example="This is some example test")
     */
    public $organization = null;

    /**
     * @var string
     * @SWG\Property(type="string", example="test.bot@fisdap.net")
     */
    public $email;

    /**
     * @var string|null
     * @SWG\Property(type="string", example="641-357-8111")
     */
    private $phone = null;

    /**
     * *** THIS MUST NOT BE NAMED 'message' - the email API uses it (reserved) ***
     * @var string
     * @SWG\Property(example="You guys are my best friends.")
     */
    public $messageBody;
    
    public function handle(Mailer $mailer)
    {
        $from = $this->email;
        $title = $this->getDisplayTitle();

        $mailer->queue(
            'contact_us',
            [
                'messageBody' => $this->messageBody,
                'phone' => $this->phone
            ],
            function ($m) use ($from, $title) {
                $m->to('support@fisdap.net', 'Fisdap')->subject('Support');
                $m->from($from, $title);
            }
        );

        return $this->toArray();
    }

    private function getDisplayTitle()
    {
        $organization = $this->firstName . ' ' . $this->lastName;
        $organization = $this->organization ? $organization . ' @ ' . $this->organization : $organization;
        return $organization;
    }

    public function setPhone($phone)
    {
        $this->phone = phone_format($phone, 'US', PhoneNumberFormat::INTERNATIONAL);
    }

    /**
     * Validation rules for request.
     * @return array
     */
    public function rules()
    {
        $rules = [
            'firstName'    => 'required|string',
            'lastName'     => 'required|string',
            'userRoleId'   => 'required|integer',
            'email'        => 'required|email',
            'organization' => 'string',
            'phone'        => 'numeric',
            'messageBody'  => 'required|string',
        ];

        return $rules;
    }

    /**
     * This is used to help format the response body
     * @return array
     */
    private function toArray()
    {
        return [
            'firstName'    => $this->firstName,
            'lastName'     => $this->lastName,
            'userRoleId'   => $this->userRoleId,
            'organization' => $this->organization,
            'email'        => $this->email,
            'phone'        => $this->phone,
            'messageBody'  => $this->messageBody
        ];
    }
}
