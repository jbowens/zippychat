<?php

namespace zc\views;

use \esprit\core\Response;

use \zc\lib\BaseView;

/**
 * The view for the /initialize-session command
 *
 * @author jbowens
 * @since 2012-08-27
 */
class InitializeSession extends BaseView {

    public function output(Response $response)
    {
        $this->setHeader('Content-Type', 'application/json');

        if( ! $response->keyExists('chatSession') || $response->get('chatSession') == null )
        {
            $output = array( 'status' => 'error' );
        }
        else
        {
            $chatSession = $response->get('chatSession');

            $chatSessionArr = array();
            $chatSessionArr['chatSessionId'] = (int) $chatSession->getChatSessionId();
            $chatSessionArr['username'] = $chatSession->getUsername();
            $chatSessionArr['loginTime'] = (int) $chatSession->getLoginTimeUTC();

            $output = array( 'status' => 'ok',
                             'chatSession' => $chatSessionArr );
        }
        
        print json_encode($output);
    }

}
