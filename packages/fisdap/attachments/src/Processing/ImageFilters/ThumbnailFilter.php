<?php namespace Fisdap\Attachments\Processing\ImageFilters;

use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image;

/**
 * Class ThumbnailFilter
 *
 * @package Fisdap\Attachments\Processing\ImageFilters
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ThumbnailFilter implements FilterInterface
{
    const DEFAULT_FIT_SIZE = 50;


    /**
     * @var int
     */
    private $fitSize;


    /**
     * @param int|null $fitSize
     */
    public function __construct($fitSize = null)
    {
        $this->fitSize = is_numeric($fitSize) ? intval($fitSize) : self::DEFAULT_FIT_SIZE;
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
        $image->fit($this->fitSize);

        return $image;
    }
}
