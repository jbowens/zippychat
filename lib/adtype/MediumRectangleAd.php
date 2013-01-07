<?php

namespace zc\lib\adtype;

use \zc\lib\AdType;

/**
 * Defines an ad type for medium rectangle ads. These are 300x250 pixels. 
 * 
 * @author jbowens
 * @since 2012-01-07
 */
class MediumRectangleAd extends AdType
{

    const ADTYPE_IDENTIFIER = "mediumRectangleAd";
    const AD_WIDTH_PX = 300;
    const AD_HEIGHT_PX = 250;

    public function getIdentifier()
    {
        return self::ADTYPE_IDENTIFIER;
    }

    public function getWidth()
    {
        return self::AD_WIDTH_PX;
    }

    public function getHeight()
    {
        return self::AD_HEIGHT_PX;
    }

}
