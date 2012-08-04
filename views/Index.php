<?php

namespace zc\views;

use \esprit\core\AbstractView;
use \esprit\core\Response;

class Index extends AbstractView {

    const TEMPLATE = "Index";
    const LOG_SOURCE = "views\\Index";

    public function generateOutput( Response $response )
    {
        $this->templateParser->loadResponse( $response );

        // If they just created a new room, redirect them to their room
        if( $response->keyExists('newRoom') )
        {
            $room = $response->newRoom;
            $this->redirect('/room/' . $room->getUrlIdentifier());
        }

        // Display the template
        $this->templateParser->displayTemplate( self::TEMPLATE );
    }

}
