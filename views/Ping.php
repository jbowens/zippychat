<?php

namespace zc\views;

use \zc\lib\BaseView;

use \esprit\core\Response;

/**
 * Prints ping output as json
 *
 * @author jbowens
 * @since 2012-08-24
 */
class Ping extends BaseView {

    const LOG_SOURCE = "views\\Ping";

    /**
     * Constructs and prints the JSON response to give to the client.
     */
    public function output( Response $response )
    {
        $this->setHeader('Content-Type', 'application/json');

        $messages = $response->get('messages');
        $messageArrs = array();
        foreach( $messages as $message )
        {
            // Create an associative array with the relevant data
            $arr = array(
                'messageid' => (int) $message->getMessageId(),
                'username' => $message->getUsername(),
                'timestamp' => (int) $message->getDateSentUTC(),
                'content' => $message->getMessage());
            array_push( $messageArrs, $arr );
        }

        if( $response->get('noSession') )
        {
            $responseArr = array(
                'status' => 'error',
                'noSession' => true
            );
        }
        else
        {
            $responseArr = array(
                'messages' => $messageArrs
            );
        }

        print json_encode( $responseArr );
    }

}
