<?php

namespace zc\lib\adserver;

use \esprit\core\LogAware;
use \esprit\core\Request;
use \esprit\core\util\Logger;

use \zc\lib\AdType;

/**
 * Defines an AdServer for ads provided through ViralAdNetwork.
 * With this ad server, we're limited by ViralAdNetwork provided
 * sizings. We also have no information about what advertisement
 * we're going to end up serving before we serve it.
 *
 * @author jbowens
 * @since 2013-01-07
 */
class ViralAdNetworkServer extends AbstractAdServer
{
    protected function loadAvailableAds()
    {     
       array_push($this->availableAds, array(
                                    'adtype' => new \zc\lib\adtype\MediumRectangleAd(),
                                    'advertisement' => new \zc\lib\ad\ViralAdNetworkFunUnit300x250() ) );   
    }
}
