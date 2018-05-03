<?php namespace Fisdap\Attachments\Http\Requests;

use Fisdap\ErrorHandling\CapturableValidationErrors;
use Fisdap\ErrorHandling\Exceptions\ContentTypeMustBeJson;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateAttachmentRequest
 *
 * @package Fisdap\Attachments\Http\Requests
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class UpdateAttachmentRequest extends FormRequest
{
    use CapturableValidationErrors;


    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'nickname' => 'sometimes|string',
            'notes' => 'sometimes|string',
            'categories' => 'sometimes|array'
        ];
    }


    public function messages()
    {
        return [
            'string' => 'must be a string',
            'array' => 'must be an array'
        ];
    }


    public function validate()
    {
        if (! $this->isJson()) {
            throw new ContentTypeMustBeJson();
        }

        parent::validate();
    }
}
