<?php

namespace zc\lib;

/**
 * A base command that adds some additional access to ZippyChat
 * related resources.
 *
 * @author jbowens
 */
abstract class BaseCommand extends \esprit\core\BaseCommand {

    protected $chatSessionSource = null;
    protected $roomSource = null;

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
                                                $this->getCache() );
        }
        return $this->roomSource;
    }

}
