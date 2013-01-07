<?php

namespace zc\views;

use \esprit\core\Response;

class Room extends DefaultView
{

    protected function output(Response $response)
    {
        $this->set('sharable_url', $this->urlUtil->generateRoomPermalink($response->get('room')));
        $this->set('title_text', $response->get('room')->getTitle() . ' chat');
        $this->set('room_adtype', $response->get('ad') ? $response->get('ad')->getAdType() : null);
        $this->set('room_adoffset', $response->get('ad') ? $response->get('ad')->getAdType()->getWidth() + 10 : null);
        $response->set('smallLogo', true);
        $response->set('widePage', true);
        $response->set('includeMetaTags', false);
        $this->addScript('room.js');
        $this->addScript('overlays/overlays.js');
        $this->addScript('overlays/simple-dialog.js');
        $this->addScript('overlays/invite-others-dialog.js');
        $this->addScript('overlays/connecting-overlay.js');
        $this->addScript('overlays/backdrop.js');
        return parent::output($response);        
    }

}
