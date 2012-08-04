<?php

namespace zc\views;

use \esprit\core\AbstractView;
use \esprit\core\Response;

/**
 * The view that handles 404 pages.
 *
 * @author jbowens
 */
class FourOhFour extends AbstractView
{

    const TEMPLATE = "FourOhFour";

    public function generateOutput( Response $response )
    {

        $this->templateParser->loadResponse( $response );
        $this->templateParser->displayTemplate( self::TEMPLATE );

    }

}
