<?php

namespace zc\lib;

use \esprit\core\Request;

/**
 * This interface is for a server of advertisements. Classes that
 * implement this interface contain information about a strategy
 * for displaying particular ads to particular portions of traffic,
 * or only specific ads, etc.
 *
 * @author jbowens
 * @since 2012-08-26
 */
interface AdServer
{

    /**
     * Determines if this ad server can service the given request and
     * ad type tuple. If this method returns false, getAd() with the
     * same arguments should return null.
     *
     * @param $request  the request to be serviced
     * @param $adType  the type of ad needed
     * 
     * @return true iff this ad server is capable of servicing the given request
     */
    public function canServe( Request $request, AdType $adType );

    /**
     * Gets the Ad object to display on the page.
     *
     * @param $request  the request being serviced
     * @param $adType  the type of ad to display
     * @return an Ad object
     */
    public function getAd( Request $request, AdType $adType );

}
