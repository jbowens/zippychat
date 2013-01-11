<?php

namespace zc\views;

use \esprit\core\Response;

class ForWebsites extends DefaultView {

    const LOG_SOURCE = "views\\ForWebsites";

    public function output( Response $response )
    {
        $this->templateParser->loadResponse( $response );

        $this->addScript('overlays/overlays.js');
        $this->addScript('overlays/simple-dialog.js');
        $this->addScript('overlays/backdrop.js');
        $this->addScript('overlays/websites-sign-up-overlay.js');
        $this->addScript('for-websites.js'); 

        $this->set('masthead_tag', ': for websites');
        $this->set('bodyClass', 'forWebsites');
        $this->set('widePage', true);

        if( strtolower($response->getRequest()->getUrl()->getPath()) == "/for-websites" )
        {
            $this->set('full_promo', true);
        }

        // Display the template
        parent::output( $response );
    }

}
