<?php

namespace Tomaj\Image\Helper;

use Tomaj\Image\ImageService;

/**
 * Staticka funkcia vyuzitelna pre nette helper na obrazky
 *
 * @package Tomaj\Image\Helper
 */
class Image
{
    public static function thumb(ImageService $imageService, $identifier, $size = null)
    {
        return $imageService->url($identifier, $size);
    }
}
