<?php

namespace zc\lib\ad;

use \zc\lib\Ad;

/**
 * A Bidvertiser skyscraper advertisement.
 *
 * @author jbowens
 * @since 2013-01-05
 */
class BidvertiserSkyscraper implements Ad
{

    /**
     * See \zc\lib\Ad.getHtml()
     */
    public function getHtml()
    {
        return <<<'EOT'
         <!-- Begin BidVertiser code -->
        <SCRIPT LANGUAGE="JavaScript1.1" SRC="http://bdv.bidvertiser.com/BidVertiser.dbm?pid=506132&bid=1259577" type="text/javascript"></SCRIPT>
        <noscript><a href="http://www.bidvertiser.com/bdv/BidVertiser/bdv_publisher_toolbar_creator.dbm">toolbar</a></noscript>
        <!-- End BidVertiser code -->        
EOT;
    } 

}
