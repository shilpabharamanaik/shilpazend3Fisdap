<?php namespace Fisdap\Attachments\Http\Requests;

use Fisdap\Attachments\Exceptions\InvalidAttachment;
use Fisdap\ErrorHandling\CapturableValidationErrors;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreAttachmentRequest
 *
 * @package Fisdap\Attachments\Http\Requests
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class StoreAttachmentRequest extends FormRequest
{
    use CapturableValidationErrors;


    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'userContextId' => 'alpha_num', // todo - make this required
            'attachment' => 'required|mimetype_not_blacklisted'
        ];
    }


    public function messages()
    {
        return [
            'required' => 'field is required',
            'mimetype_not_blacklisted' => 'uploaded file type is not allowed (MIME type is on blacklist).'
        ];
    }


    /**
     * @param Validator $validator
     *
     * @return void
     * @throws InvalidAttachment
     */
    protected function failedValidation(Validator $validator)
    {
        throw new InvalidAttachment($this->formatErrors($validator));
    }
}
