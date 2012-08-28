<?php

namespace zc\views;

use \esprit\core\Response;

use \zc\lib\BaseView;

/**
 * A view for the /post-message command.
 *
 * @author jbowens
 * @since 2012-08-27
 */
class PostMessage extends BaseView
{

    public function output(Response $response)
    {
        $this->setHeader('Content-Type', 'application/json');

        if( $response->keyExists( 'errorCode' ) )
        {
            $output = array(
                'status' => 'error',
                'errorCode' => $response->get('errorCode')
            );
        }
        else
        {
            $message = $response->get('newMessage');
            $output = array(
                'status' => 'ok',
                'messageId' => $message->getMessageId()
            );
        }

        print json_encode($output);
    }

}
