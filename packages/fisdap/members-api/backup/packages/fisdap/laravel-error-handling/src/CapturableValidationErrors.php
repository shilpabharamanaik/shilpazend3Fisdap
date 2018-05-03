<?php namespace Fisdap\ErrorHandling;

use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;


/**
 * For use with FormRequest classes, this trait will allow validation exceptions to be handled by
 * Fisdap\ErrorHandling\ErrorHandler, instead of being caught by the Laravel router.
 *
 * @package Fisdap\ErrorHandling
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait CapturableValidationErrors
{
    protected function failedAuthorization()
    {
        throw new AccessDeniedHttpException();
    }


    /**
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new UnprocessableEntityHttpException($this->formatErrors($validator));
    }


    /**
     * @param Validator $validator
     *
     * @return string
     */
    protected function formatErrors(Validator $validator)
    {
        $errors = [];

        foreach ($validator->getMessageBag()->getMessages() as $field => $fieldErrors) {
                $errors[] = "$field " . implode(', ', $fieldErrors);

        }

        return 'Validation errors: ' . implode(', ', $errors);
    }
}