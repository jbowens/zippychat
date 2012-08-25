<?php

namespace zc\views;

use \esprit\core\Response;

class Room extends DefaultView
{

    protected function output(Response $response)
    {
        $response->set('smallLogo', true);
        $response->set('widePage', true);
        return parent::output($response);        
    }

}