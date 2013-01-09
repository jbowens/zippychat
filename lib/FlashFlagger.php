<?php

namespace zc\lib;

use \esprit\core\Request;
use \esprit\core\RequestFlagger;

/**
 * A RequestFlagger for determining whether or not the user's browser supports
 * Flash. This requires coordination with the View, through inclusion of some
 * Javascript. Once it's determined whether or not the user has flash, the
 * verdict will be stored in the associated Session object, so that the 
 * javascript file only needs to be included once.
 *
 * @since January 8 2013
 * @author jbowens
 */
class FlashFlagger implements RequestFlagger
{

    public function processRequest(Request $request)
    {
        $session = $request->getSession();
        
        if( $session->keyExists('flashEnabled') )
        {
            // If we've already determined if this thread has flash enabled,
            // then we should just use that value.
            $request->setFlag('flashEnabled', $session->get('flashEnabled'));
        }
        else
        {
            // Otherwise, we should denote that the view should attempt to identify whether
            // flash is enabled asap.
            $request->setFlag('flashIndeterminate', true);
        }
    }

}
