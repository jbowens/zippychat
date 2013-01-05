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
class GoogleAdSenseServer implements AdServer
{
    use LogAware;
    
    protected $logger;
    protected $availableAds = array();

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;

        array_push($this->availableAds, array(
                                    'adtype' => new \zc\lib\adtype\SkyscraperAd(),
                                    'advertisement' => new \zc\lib\ad\GoogleAdSenseSkyscraper() ) );
    }

    /**
     * @see AdServer.canServe();
     */
    public function canServe( Request $request, AdType $adType )
    {
        foreach( $this->availableAds as $adTypeArr )
        {
            $availableAdType = $adTypeArr['adtype'];
            $ad = $adTypeArr['advertisement'];
            if( $availableAdType->withinType( $adType ) )
            {
                return true;
            }
        }
        return false;
    }

    /**
     * @see AdServer.getAd();
     */
    public function getAd( Request $request, AdType $adType )
    {
        // An easy way of enforcing the AdServer contract that if canServe()
        // returns false, getAd() must return null.
        if( ! $this->canServe( $request, $adType ) )
        {
            return null;
        }

        // Find the available ad
        foreach( $this->availableAds as $adTypeArr )
        {
            $availableAdType = $adTypeArr['adtype'];
            $ad = $adTypeArr['advertisement'];
            if( $availableAdType->withinType( $adType ) )
            {
                return $ad;
            }
        }

        // Impossible!
        $this->error("Violation of the contract AdServer. getAd() returned null while canServe() returned true");
        return null;
    }

}
