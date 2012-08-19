<?php

namespace zc\lib;

use \esprit\core\db\DatabaseManager;
use \esprit\core\util\Logger;
use \esprit\core\Cache;

/**
 * A class useful for retrieving Message objects. Whenever you need to get
 * messages from the database, you should likely be using this class.
 *
 * @author jbowens
 * @since 2012-08-19
 */
class MessageSource {

    const MESSAGES_CACHE_NAMESPACE = "messages";

    protected $dbm;
    protected $logger;
    protected $messageCache;

    public function __construct(DatabaseManager $dbm, Logger $logger, Cache $cache)
    {
        $this->dbm = $dbm;
        $this->logger = $logger;
        $this->messageCache = $cache->accessNamespace( self::MESSAGES_CACHE_NAMESPACE );
    }

}
