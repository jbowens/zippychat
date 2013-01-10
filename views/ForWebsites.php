<?php

namespace zc\views;

use \zc\lib\BaseView;

use \esprit\core\Response;

class ForWebsites extends BaseView {

    const TEMPLATE = "ForWebsites";
    const LOG_SOURCE = "views\\ForWebsites";

    public function output( Response $response )
    {
        $this->templateParser->loadResponse( $response );

        //$response->set('includeMetaTags', true);
        $this->set('masthead_tag', ': for websites');
        $this->set('bodyClass', 'forWebsites');
        $this->set('widePage', true);

        // Display the template
        $this->templateParser->displayTemplate( self::TEMPLATE );
    }

}
