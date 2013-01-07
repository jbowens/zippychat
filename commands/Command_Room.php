<?php

namespace zc\commands;

use \esprit\core\Request as Request;
use \esprit\core\Response as Response;
use \esprit\core\exceptions\PageNotFoundException;

use \zc\lib\adserver\BidvertiserServer;
use \zc\lib\adserver\ChitikaServer;
use \zc\lib\adserver\ClicksorServer;
use \zc\lib\adserver\GoogleAdSenseServer;
use \zc\lib\adserver\ViralAdNetworkServer;
use \zc\lib\adtype\MediumRectangleAd;
use \zc\lib\adtype\SkyscraperAd;
use \zc\lib\BaseCommand;
use \zc\lib\ChatSessionSource;
use \zc\lib\ChatSession;
use \zc\lib\RoomSource;
use \zc\lib\Room;

/**
 * The command for requests to chat rooms. 
 *
 * @author jbowens
 * @since 2012-08-19
 */
class Command_Room extends BaseCommand {

    const COMMAND_NAME = "Room";
    const NUM_OLD_MESSAGES_TO_DISPLAY = 20;

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function generateResponse(Request $request, Response $response) {

        // Determine which chat room is being requested
        $room = $this->getRoomFromRequest($request);
        if( $room == null )
            throw new PageNotFoundException();
        $response->set("room", $room);

        $ad = $this->getAd( $request );
        if( $ad != null )
            $response->set('ad', $ad);

        return $response;

    }

    /**
     * Extract the room from the request.
     *
     * @return the Room object indicated through the url
     */
    public function getRoomFromRequest(Request $request)
    {
        $url = $request->getUrl();
        if( $url->getPathLength() < 2 || ! $url->getPathPiece(1) )
            return null;
        $roomIdentifier = $url->getPathPiece( 1 );

        // Get the room from the cache or database
        $roomSource = $this->getRoomSource();
        $room = $roomSource->getRoomByIdentifier( $roomIdentifier );
        return $room;
    }

    /**
     * Determines if advertisements should be enabled for the given request.
     *
     * @param $request  the request being serviced
     * @return true iff ads should be shown to this user's request, if applicable
     */
    public function adsEnabled(Request $request)
    {
        return ($this->config->get('serve_ads') == true);
    }

    /**
     * Retrieves the advertisement that we should display in the room.
     * 
     * @param $request  the request being serviced
     * @return an Ad object
     */
    public function getAd(Request $request)
    {
        // Make sure ads are enabled
        if( ! $this->adsEnabled($request) )
            return null;

        $adType = null;
        $adSource = null;
        if( $this->getConfig()->get('viral_ad_network') )
        {
            // We're currently displaying 300x250 medium rectangles
            $adType = new MediumRectangleAd();
            $adSource = new ViralAdNetworkServer( $this->logger );
        } else {
            // Fallback to Chitika if we're not showing viral ad network ads
            $adType = new SkyscraperAd();
            $adSource = new ChitikaServer( $this->logger );
        }

        if( ! $adSource->canServe( $request, $adType ) )
        {
            $this->error("Unable to find servable ad", $request);
            return null;
        }

        return $adSource->getAd( $request, $adType );
    }

}

