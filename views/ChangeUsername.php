<?php

namespace zc\views;

use \esprit\core\Response;

use \zc\lib\BaseView;

/**
 * A view for the /change-username ajax command. Prints a json
 * response conveying whether the request successed.
 *
 * @author jbowens
 * @since 2012-08-28
 */
class ChangeUsername extends BaseView {

    public function output(Response $response) 
    {
        $this->setHeader('Content-Type', 'application/json');

        if( $response->get('error') )
        {
            $output = array( 
                'error' => true
            );
        } elseif ( ! $response->get('changeUsernameSuccess') )
        {
            $output = array(
                'error' => false,
                'success' => false,
                'badUsername' => true
            );
        } else
        {
            $output = array(
                'error' => false,
                'success' => true
            );
        }

        print json_encode($output);
    }

}
