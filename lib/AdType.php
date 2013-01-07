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

    const DEFAULT_IDENTIFIER = "catchall_ad_type";

    /**
     * Returns a string identifier of the ad type. The identifier should follow
     * all the restrictions of CSS class names so that it may be used as a 
     * class name.
     */
    public function getIdentifier()
    {
        return self::DEFAULT_IDENTIFIER;
    }


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

    /**
     * Returns true iff this criteria falls within the argument's given
     * criteria.
     *
     * @param  another AdType object
     * @return true iff this object is contained with the other adtypes set
     *         of possible advertisements
     */
    public function withinType(AdType $otherAdType)
    {
        return ($otherAdType->isVideo() == $this->isVideo() || $otherAdType->isVideo() == null) &&
               ($otherAdType->isTextual() == $this->isTextual() || $otherAdType->isTextual() == null) &&
               ($otherAdType->isImage() == $this->isImage() || $otherAdType->isImage() == null) &&
               ($otherAdType->getWidth() == $this->getWidth() || $otherAdType->getWidth() == null) &&
               ($otherAdType->getHeight() == $this->getHeight() || $otherAdType->getHeight() == null);
    }

}
