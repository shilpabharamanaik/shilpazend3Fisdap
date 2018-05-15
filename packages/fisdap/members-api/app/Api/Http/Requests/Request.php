<?php namespace Fisdap\Api\Http\Requests;

use Fisdap\ErrorHandling\CapturableValidationErrors;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class Request
 *
 * @package Fisdap\Api\Http\Requests
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class Request extends FormRequest
{
    use CapturableValidationErrors;


    public function authorize()
    {
        return true;
    }


    /**
     * @return array
     */
    abstract public function rules();
}
