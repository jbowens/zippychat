<?php

namespace zc\lib\adtype;

use \zc\lib\AdType;

/**
 * Defines an ad type for skyscraper ads. Sky scraper ads are generally 
 * tall, thin image or text ads. At the time of writing, this ad type
 * was used on /Room.
 * 
 * @author jbowens
 * @since 2012-08-26
 */
class SkyscraperAd extends AdType
{

    const SKYSCRAPER_IDENTIFIER = "skyscraperAd";
    const SKYSCRAPER_WIDTH_PX = 120;
    const SKYSCRAPER_HEIGHT_PX = 600;

    public function getIdentifier()
    {
        return self::SKYSCRAPER_IDENTIFIER;
    }

    public function getWidth()
    {
        return self::SKYSCRAPER_WIDTH_PX;
    }

    public function getHeight()
    {
        return self::SKYSCRAPER_HEIGHT_PX;
    }

    public function isVideo()
    {
        // Who wants to watch a video at 120x600?
        return false;
    }

}
