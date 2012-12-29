<?php

namespace zc\views;

use \esprit\core\Response;

use \zc\lib\BaseView;

/**
 * A view for the /email-room-invite command.
 *
 * @author jbowens
 * @since 2012-12-29
 */
class EmailRoomInvite extends BaseView
{

    public function output(Response $response)
    {
        $this->setHeader('Content-Type', 'application/json');

        if( $response->keyExists( 'error' ) )
        {
            $output = array(
                'status' => 'error',
                'field' => $response->get('field')
            );
        }
        else
        {
            $output = array(
                'status' => 'ok'
            );
        }

        print json_encode($output);
    }

}
