<?php

namespace zc\lib\ad;

use \zc\lib\Ad;

/**
 * A ViralAdNetwork 300x250 Fun Unit
 *
 * @author jbowens
 * @since 2013-01-07
 */
class ViralAdNetworkFunUnit300x250 implements Ad
{

    /**
     * See \zc\lib\Ad.getHtml()
     */
    public function getHtml()
    {
        return <<<'EOT'
        <script type="text/javascript">
        var vaunit_unit_type=0;
        var vaunit_width=300;
        var vaunit_height=250;
        var vaunit_id=6186;
        var vaunit_bgcolor="FFFFFF";
        var vaunit_linkcolor="3B698C";
        </script>
        <script type="text/javascript" src="http://syndication1.viraladnetwork.net/getad/"></script>
EOT;
    } 

}
