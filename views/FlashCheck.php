<?php

namespace zc\views;

use \esprit\core\Response;

use \zc\lib\BaseView;

/**
 * A view for the /flash-check command.
 *
 * @author jbowens
 * @since 2013-01-08
 */
class FlashCheck extends BaseView
{

    public function output(Response $response)
    {
        $this->setHeader('Content-Type', 'application/json');

        if( $response->keyExists( 'error' ) )
        {
            $output = array(
                'status' => 'error'
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
