<?php

namespace zc\views;

use \zc\lib\BaseView;

use \esprit\core\Response;

/**
 * The view that handles 404 pages.
 *
 * @author jbowens
 */
class FourOhFour extends BaseView
{

    const TEMPLATE = "FourOhFour";

    public function output( Response $response )
    {

        $this->setStatus( new \esprit\core\HttpStatusCodes\FileNotFound() ); 
        $this->templateParser->loadResponse( $response );
        $this->templateParser->displayTemplate( self::TEMPLATE );

    }

}
