<?php

namespace zc\commands;

use \esprit\core\Request as Request;
use \esprit\core\Response as Response;
use \esprit\core\exceptions\PageNotFoundException;

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

        $chatSession = $this->getChatSession( $request, $room );
        $response->set('chatSession', $chatSession);

        $messages = $this->getMessages( $room, $chatSession );
        $response->set('messages', $messages);

        $activeChatSessions = $this->getActiveChatSessions( $room );
        $response->set('chatSessions', $activeChatSessions);

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
     * Gets the ChatSession object for this user. This will create a new chat session
     * if necessary.
     *
     * @param $request the Request being serviced
     * @param $room the Room the user is connecting to
     * @return the user's ChatSession
     */
    public function getChatSession(Request $request, Room $room) {
        $session = $request->getSession();
        $chatSessionSource = $this->getChatSessionSource();

        $chatSession = null;

        // Are they already in this chat room?
        if( $session->keyExists( ChatSessionSource::CHAT_SESSION_VARIABLE_PREFIX . $room->getRoomId() ) )
        {
            $chatSessionId = $session->get( ChatSessionSource::CHAT_SESSION_VARIABLE_PREFIX . $room->getRoomId() );
            $chatSession = $chatSessionSource->getChatSessionById( $chatSessionId );
        }

        // If we still don't have a chat session...
        if( $chatSession == null )
        {
            // Create a new chat session
            $chatSession = $chatSessionSource->createSession( $room );
            $session->set( ChatSessionSource::CHAT_SESSION_VARIABLE_PREFIX . $room->getRoomId(), $chatSession->getChatSessionId() );
            $this->logger->info( "Created new chat session with id " . $chatSession->getChatSessionId(), $this->getName() );
        }
        
        return $chatSession;
    }

    /**
     * Gets the initial messages that should be displayed on the page.
     *
     * @param $room  the room to get the messages from
     * @param $chatSession  the chat session of the user in the room
     * @return a MessageList object
     */
    public function getMessages(Room $room, ChatSession $chatSession)
    {
        if( $room->getRoomId() != $chatSession->getRoomId() )
        {
            $this->logger->error("getMessages() called with conflicting room ids (".$room->getRoomId()." and ".$chatSession->getRoomId().")", $this->getName());
            return array();
        }
        return $this->getMessageSource()->getMostRecentMessages($room, self::NUM_OLD_MESSAGES_TO_DISPLAY);
    }

    /**
     * Returns an array of all the active chat sessions for the given
     * room.
     *
     * @param $room  the room to query
     * @return an array of ChatSession objects
     */
    public function getActiveChatSessions(Room $room)
    {
        return $this->getChatSessionSource()->getActiveChatSessions($room); 
    }

}

