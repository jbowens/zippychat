<?php

namespace zc\lib\ad;

use \zc\lib\Ad;

/**
 * A Google AdSense skyscraper advertisement.
 *
 * @author jbowens
 * @since 2012-08-26
 */
class GoogleAdSenseSkyscraper implements Ad
{

    /**
     * See \zc\lib\Ad.getHtml()
     */
    public function getHtml()
    {
        return <<<'EOT'
        <script type="text/javascript">//<!--
            google_ad_client = "ca-pub-8101954213892678";
        /* ZippyChat new */
        google_ad_slot = "9144706335";
        google_ad_width = 120;
        google_ad_height = 600;
        //-->
        //</script>
        <script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
EOT;
    } 

    public function getAdType()
    {
        return new \zc\lib\adtype\SkyscraperAd();
    }

}
