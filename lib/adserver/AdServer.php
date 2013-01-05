<?php

namespace zc\lib\adserver;

use \esprit\core\Request;

use \zc\lib\AdType;

/**
 * An interface defining AdServers.
 *
 * @author jbowens
 */
interface AdServer {

    /**
     * Determines if this AdServer is capable of satisfying the given
     * request and ad type combination.
     */
    public function canServe( Request $request, AdType $adType );

    /**
     * Returns an ad that can be served to the given request in the
     * given format.
     */
    public function getAd( Request $request, AdType $adType );

}
