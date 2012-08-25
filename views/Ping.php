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
                'messageid' => $message->getMessageId(),
                'username' => $message->getUsername(),
                'timestamp' => $message->getDateSentUTC(),
                'content' => $message->getMessage());
            array_push( $messageArrs, $arr );
        } 

        $responseArr = array(
            'messages' => $messageArrs
        );

        print json_encode( $responseArr );
    }

}
