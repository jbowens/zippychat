<?php

namespace zc\lib;

/**
 * An explict class-level enum for ad types. PHP does not yet support
 * actual enums so this is as good as it gets if we want something close
 * to type safety.
 *
 * If you instantiate this exact class, it will create an AdType instance
 * that describes all possible ad types (unrestrictive in all attributes)
 *
 * @author jbowens
 * @since 2012-08-26
 */
class AdType 
{

    /**
     * Returns the width, in pixels, that this ad type requires or null
     * if it imposes no restrictions on the width of the ad display.
     */
    public function getWidth()
    {
        return null;
    }

    /**
     * Returns the height, in pixels, that this ad type requires or null
     * if it imposes no restrictions on the height of the ad display.
     */
    public function getHeight()
    {
        return null;
    }

    /**
     * Returns true iff the ad type is an image, or null this ad type
     * imposes no restrictions on whether the ad is an image.
     */
    public function isImage()
    {
        return null;
    }

    /**
     * Returns true iff the ad type is textual, or null if it imposes
     * no restriction on if an image is textual.
     */
    public function isTextual()
    {
        return null;
    }

    /**
     * Returns true iff the ad incorporates video, or null if it imposes
     * no restriction on if an image is a video.
     */
    public function isVideo()
    {
        return null;
    }

}
