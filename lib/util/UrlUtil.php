<?php

namespace zc\lib\util;

use \esprit\core\Site;

/**
 * Defines utilities for generating urls.
 *
 * @author jbowens
 * @since 2012-08-24
 */
class UrlUtil 
{
    protected $site;

    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Generates the canonical, permalink for the given room.
     *
     * @param $room  the room to generate the url for
     */
    public function generateRoomPermalink( Room $room )
    {
        return 'http://' . $this->site->getDomain() . '/room/' . $room->getUrlIdentifier();
    }

}
