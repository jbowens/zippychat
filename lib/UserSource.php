<?php

namespace zc\lib;

use \esprit\core\db\DatabaseManager;

class UserSource {

    protected $dbm;
    protected $cache;

    public function __construct( DatabaseManager $dbm, Cache $cache )
    {
        $this->dbm = $dbm;
        $this->cache = $cache;
    }


}

