<?php

namespace zc\commands;

use \esprit\core\Request as Request;
use \esprit\core\Response as Response;

use \zc\lib\BaseCommand;
use \zc\lib\RoomSource;
use \zc\lib\Room;

/**
 * The homepage command.
 *
 * @author jbowens
 */
class Command_Index extends BaseCommand {

    const COMMAND_NAME = "Index";

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function run(Request $request, Response $response) {

        // Handle create chat room request
        if( $request->getPost('chat_room_title') )
        {
            $roomSource = new RoomSource( $this->getDatabaseManager(), $this->getCache() );
            
            $roomBuilder = Room::getBuilder();
            $roomBuilder->title( $request->getPost('chat_room_title') )
                        ->description( $request->getPost('chat_room_desc') )
                        ->dateCreated( time() )
                        ->creatorIpAddress( $request->getIpAddress() );

            if( $request->getPost('chat_room_pass') )
            {
                $hash = RoomSource::getPasswordHasher()->hash( $request->getPost('chat_room_pass') );
                $rB->passwordHash( $hash );
            }

            $newRoom = $roomSource->createRoom( $roomBuilder );

            $response->set("newRoom", $newRoom);
        }


        return $response;
    } 

}

