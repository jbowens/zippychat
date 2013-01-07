<?php

namespace zc\lib\ad;

use \zc\lib\Ad;

/**
 * A Clicksor skyscraper advertisement.
 *
 * @author jbowens
 * @since 2013-01-04
 */
class ClicksorSkyscraper implements Ad
{

    /**
     * See \zc\lib\Ad.getHtml()
     */
    public function getHtml()
    {
        return <<<'EOT'
        <script type="text/javascript">
        //interstitial ad
        clicksor_enable_inter = false; clicksor_maxad = -1;   
        clicksor_hourcap = -1; clicksor_showcap = 2;
        //default banner house ad url 
        clicksor_default_url = '';
        clicksor_banner_border = '#ffffff'; clicksor_banner_ad_bg = '#FFFFFF';
        clicksor_banner_link_color = '#0069aa'; clicksor_banner_text_color = '#666666';
        clicksor_banner_image_banner = true; clicksor_banner_text_banner = true;
        clicksor_layer_border_color = '#ffffff';
        clicksor_layer_ad_bg = '#FFFFFF'; clicksor_layer_ad_link_color = '#0069aa';
        clicksor_layer_ad_text_color = '#666666'; clicksor_text_link_bg = '';
        clicksor_text_link_color = ''; clicksor_enable_text_link = false;
        clicksor_layer_banner = false;
        </script>
        <script type="text/javascript" src="http://ads.clicksor.com/newServing/showAd.php?nid=1&amp;pid=281875&amp;adtype=4&amp;sid=458896"></script>
        <noscript><a href="http://www.yesadvertising.com">affiliate marketing</a></noscript>        
EOT;
    } 

    public function getAdType()
    {
        return new \zc\lib\adtype\SkyscraperAd();
    }

}
