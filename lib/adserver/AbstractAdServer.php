<?php

namespace zc\lib\adserver;

use \esprit\core\LogAware;
use \esprit\core\Request;
use \esprit\core\util\Logger;

use \zc\lib\AdServer;
use \zc\lib\AdType;

/**
 * Defines an abstract ad server. This ad server allows mappings between ad types and
 * ads and will display matching ads if available. It should be subclassed.
 *
 * @author jbowens
 * @since 2013-01-05
 */
abstract class AbstractAdServer implements AdServer
{
    use LogAware;
    
    protected $logger;
    protected $availableAds = array();

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->loadAvailableAds();
    }

    /**
     * Loads any available ads. This should be implemented by
     * the subclass.
     */
    protected abstract function loadAvailableAds();

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
