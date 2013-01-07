<?php

namespace zc\lib\ad;

use \zc\lib\Ad;

/**
 * A Chitika skyscraper advertisement.
 *
 * @author jbowens
 * @since 2013-01-04
 */
class ChitikaSkyscraper implements Ad
{

    /**
     * See \zc\lib\Ad.getHtml()
     */
    public function getHtml()
    {
        return <<<'EOT'
        <script type="text/javascript">
        ch_client = "jbowens";
        ch_width = 120;
        ch_height = 600;
        ch_type = "mpu";
        ch_sid = "ZippyChat Room";
        ch_color_site_link = "23437A";
        ch_color_title = "23437A";
        ch_color_border = "FFFFFF";
        ch_color_text = "474747";
        ch_color_bg = "FFFFFF";
        </script>
        <script src="http://scripts.chitika.net/eminimalls/amm.js" type="text/javascript">
        </script>        
EOT;
    } 

    public function getAdType()
    {
        return new \zc\lib\adtype\SkyscraperAd();
    }

}
