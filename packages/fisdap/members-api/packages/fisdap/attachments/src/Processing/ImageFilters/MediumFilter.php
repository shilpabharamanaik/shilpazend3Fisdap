<?php namespace Fisdap\Attachments\Processing\ImageFilters;

use Intervention\Image\Constraint;
use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image;

/**
 * Class MediumFilter
 *
 * @package Fisdap\Attachments\Processing\ImageFilters
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class MediumFilter implements FilterInterface
{
    const DEFAULT_WIDTH = 740;


    /**
     * @var int
     */
    private $width;


    /**
     * @param int|null $width
     */
    public function __construct($width = null)
    {
        $this->width = is_numeric($width) ? intval($width) : self::DEFAULT_WIDTH;
    }


    /**
     * Applies filter to given image
     *
     * @param  Image $image
     *
     * @return Image
     */
    public function applyFilter(Image $image)
    {
        $image->widen($this->width, function (Constraint $constraint) {
            $constraint->upsize();
        });

        return $image;
    }
}
