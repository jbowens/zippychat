<?php

namespace zc\lib;

use \esprit\core\Request;
use \esprit\core\Response;

/**
 * A base command that adds some additional access to ZippyChat
 * related resources.
 *
 * @author jbowens
 */
abstract class BaseCommand extends \esprit\core\BaseCommand {

    protected $chatSessionSource = null;
    protected $roomSource = null;
    protected $messageSource = null;

    /**
     * Gets a ChatSessionSource object that can be used to retrieve chat
     * sessions. This is the preferred method of getting or creating a
     * ChatSessionSource.
     *
     * @return a ChatSessionSource object
     */
    public function getChatSessionSource()
    {
        if( $this->chatSessionSource == null )
        {
            $this->chatSessionSource = new ChatSessionSource( $this->getDatabaseManager(),
                                                              $this->getLogger(),
                                                              $this->getCache() );
        }
        return $this->chatSessionSource;
    }

    /**
     * Returns a RoomSource object that can be used to retrieve chat room
     * objects. This is the preferred method of getting or creating a 
     * RoomSource.
     *
     * @return a RoomSource object
     */
    public function getRoomSource()
    {
        if( $this->roomSource == null )
        {
            $this->roomSource = new RoomSource( $this->getDatabaseManager(),
                                                $this->getLogger(),
                                                $this->getCache() );
        }
        return $this->roomSource;
    }

    /**
     * Returns a MessageSource object that can be used to retrieve 
     * Message objects. This is the preferred method of getting or creating
     * a MessageSource.
     *
     * @return a MessageSource object
     */
    public function getMessageSource()
    {
        if( $this->messageSource == null )
        {
            $this->messageSource = new MessageSource($this->getDatabaseManager(),
                                                     $this->getLogger(),
                                                     $this->getCache() );
        }
        return $this->messageSource;
    }

    /**
     * Executes the command.
     */
    public function run(Request $request, Response $response)
    {
        $response->set('site', $request->getSite());

        return $this->generateResponse( $request, $response );
    }

    /**
     * Subclasses should implement this to do their command work.
     *
     * @param $request  the esprit request
     * @param $response  the esprit response
     * @return an \esprit\core\Response object
     */
    abstract function generateResponse( Request $request, Response $response );

}
