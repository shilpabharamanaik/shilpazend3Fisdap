<?php namespace Fisdap\Api\Users\UserContexts\Roles\Instructors;

use Fisdap\Entity\InstructorLegacy;
use Fisdap\Fractal\Transformer;

/**
 * Prepares instructor user role data for JSON output
 *
 * @package Fisdap\Api\Users\UserContexts\Roles\Instructors
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class InstructorTransformer extends Transformer
{
    /**
     * @param array|InstructorLegacy $instructor
     *
     * @return array
     */
    public function transform($instructor)
    {
        if ($instructor instanceof InstructorLegacy) {
            $instructor = $instructor->toArray();
        }

        $this->removeFields([
                'username',
                'first_name',
                'last_name',
                'email',
                'office_phone',
                'cell_phone',
                'pager'
            ], $instructor);

        return $instructor;
    }
}
