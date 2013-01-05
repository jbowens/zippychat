<?php

namespace zc\views;

use \esprit\core\Response;

class Room extends DefaultView
{

    protected function output(Response $response)
    {
        $response->set('smallLogo', true);
        $response->set('widePage', true);
        $response->set('includeMetaTags', false);
        $this->addScript('room.js');
        $this->addScript('overlays/overlays.js');
        $this->addScript('overlays/simple-dialog.js');
        $this->addScript('overlays/invite-others-dialog.js');
        $this->addScript('overlays/backdrop.js');
        return parent::output($response);        
    }

}
