<?php

namespace zc\lib;

use \esprit\core\db\DatabaseReference;
use \esprit\core\Request;

/**
 * A class for logging 404 errors.
 *
 * @author jbowens
 * @since January 5, 2013
 */
class FourOhFourLogger
{

    const SQL_INSERT_RECORD = "INSERT INTO 404logs (path, whenAccessed, ip) VALUES(?, NOW(), INET_ATON(?))"; 

    protected $dbref;

    /**
     * Constructs a new DatabaseManager from a 
     */
    public function __construct(DatabaseReference $dbref)
    {
        $this->dbref = $dbref;
    }

    /**
     * Log the given request as a 404 request.
     */
    public function logRequest(Request $request)
    {
        $pstmt = $this->dbref->deref()->prepare( self::SQL_INSERT_RECORD );
        $pstmt->execute(array( $request->getUrl()->getPath(),
                               $request->getServer('REMOTE_ADDR') ));
    }

}
