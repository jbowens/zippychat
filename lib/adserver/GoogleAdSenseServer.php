<?php

namespace zc\lib\adserver;

use \esprit\core\LogAware;
use \esprit\core\Request;
use \esprit\core\util\Logger;

use \zc\lib\AdType;

/**
 * Defines an AdServer for ads provided through Google AdSense.
 * With this ad server, we're limited by Google AdSense provided
 * sizings. We also have no information about what advertisement
 * we're going to end up serving before we serve it.
 *
 * @author jbowens
 * @since 2012-08-26
 */
class GoogleAdSenseServer extends AbstractAdServer
{

    protected function loadAvailableAds()
    {
        array_push($this->availableAds, array(
                                    'adtype' => new \zc\lib\adtype\SkyscraperAd(),
                                    'advertisement' => new \zc\lib\ad\GoogleAdSenseSkyscraper() ) );
    }

}
