<?php

namespace zc\lib\adtype;

use \zc\lib\AdType;

/**
 * Defines an ad type for medium rectangle ads. These are 300x250 pixels. 
 * 
 * @author jbowens
 * @since 2012-01-07
 */
class SkyscraperAd extends AdType
{

    const SKYSCRAPER_WIDTH_PX = 300;
    const SKYSCRAPER_HEIGHT_PX = 250;

    public function getWidth()
    {
        return self::SKYSCRAPER_WIDTH_PX;
    }

    public function getHeight()
    {
        return self::SKYSCRAPER_HEIGHT_PX;
    }

}
