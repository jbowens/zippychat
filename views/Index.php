<?php

namespace zc\views;

use \zc\lib\BaseView;

use \esprit\core\Response;

class Index extends BaseView {

    const TEMPLATE = "Index";
    const LOG_SOURCE = "views\\Index";

    public function output( Response $response )
    {
        $this->templateParser->loadResponse( $response );

        // If they just created a new room, redirect them to their room
        if( $response->keyExists('newRoom') )
        {
            $room = $response->newRoom;
            $this->redirect('/room/' . $room->getUrlIdentifier());
        }

        $response->set('includeMetaTags', true);

        // Display the template
        $this->templateParser->displayTemplate( self::TEMPLATE );
    }

}
