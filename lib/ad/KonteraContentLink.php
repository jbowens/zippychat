<?php

namespace zc\lib\ad;

use \zc\lib\Ad;

/**
 * Kontera javascript contennt link ad that scans the page, converting
 * text into link advertisements
 *
 * @author jbowens
 * @since 2013-01-04
 */
class KonteraContentLink implements Ad
{

    /**
     * See \zc\lib\Ad.getHtml()
     */
    public function getHtml()
    {
        return <<<'EOT'
        <!-- Kontera(TM);-->
        <script type='text/javascript'>
        var dc_AdLinkColor = '#0072bc' ; 
        var dc_PublisherID = 219317 ; 
         
        </script>
        <script type='text/javascript' src='http://kona.kontera.com/javascript/lib/KonaLibInline.js'>
        </script>
        <!-- end Kontera(TM) -->        
EOT;
    } 

    public function getAdType()
    {
        // TODO: Create a new ad type for this type of ad
        return null;
    }

}
