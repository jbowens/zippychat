<?php

namespace zc\lib\ad;

use \zc\lib\Ad;

/**
 * @author jbowens
 * @since 2013-01-07
 */
class ChitikaMediumRectangle implements Ad
{

    /**
     * See \zc\lib\Ad.getHtml()
     */
    public function getHtml()
    {
        return <<<'EOT'
        <script type="text/javascript">
        ch_client = "jbowens";
        ch_width = 300;
        ch_height = 250;
        ch_type = "mpu";
        ch_sid = "Chitika Default";
        ch_color_site_link = "205F9E";
        ch_color_title = "205F9E";
        ch_color_border = "FFFFFF";
        ch_color_text = "3B3B3B";
        ch_color_bg = "FFFFFF";
        </script>
            <script src="http://scripts.chitika.net/eminimalls/amm.js" type="text/javascript">
        </script>
EOT;
    } 

    public function getAdType()
    {
        return new \zc\lib\adtype\MediumRectangleAd();
    }

}
