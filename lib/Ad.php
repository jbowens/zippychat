<?php

namespace zc\lib;

/**
 * Defines an interface for an advertisement.
 *
 * @author jbowens
 * @since 2012-08-26
 */
interface Ad
{

    /**
     * Returns the HTML rendering of the advertisement.
     */
    public function getHtml();

    /**
     * Returns the AdType of the ad this is.
     */
    public function getAdType();

}
