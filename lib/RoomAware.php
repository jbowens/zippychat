<?php

namespace zc\lib;

use \esprit\core\Request;

/**
 * A trait for commands. This class defines methods useful
 * for commands that a room-specific, including the ability
 * to extract the requested room from the request. At the time
 * of writing, it was intended for the /Ping and /Post-Message
 * commands.
 *
 * @author jbowens
 * @since 2012-08-25
 */
trait RoomAware {

    /**
     * The implementing class be of \zc\lib\BaseCommand
     * so that the RoomSource is available.
     */
    abstract function getRoomSource();

    /**
     * Extracts the room that was requested from the request.
     *
     * @param $request  the incoming request
     * @return the Room specified in the request, or null
     */
    public function getRequestedRoom(Request $request) {
        $roomId = $request->getGet('r');

        if( ! $roomId )
            return null;

        return $this->getRoomSource()->getRoomById($roomId);
    }

}
