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
     * Gets the html to display an advertisement.
     *
     * @param $request  the request being serviced
     * @param $adType  the type of ad to display
     * @return a string containing the html to display the advertisement
     */
    public function getAd( Request $request, AdType $adType );

}
