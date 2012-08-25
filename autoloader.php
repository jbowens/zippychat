<?php

namespace zc;

/**
 * This file adds an autoloader for all zippy chat classes, assuming their naming
 * and directory structure match our conventions. This could be expanded in the
 * future but that really shouldn't be necessary as long as we continue making
 * sub-namespaces of zc.
 *
 * @author jbowens
 * @since June 2012
 */
function autoload( $class ) {
    $classPieces = explode("\\", $class);

    if( count($classPieces) == 0 )
        return false;

    // If it's not in the zc namespace, we don't know how to
    // import it.
    if( $classPieces[0] != 'zc' )
        return false;

    // Remove the zc
    unset($classPieces[0]);

    $file = implode('/', $classPieces).'.php';

    if( @file_exists( __DIR__ . DIRECTORY_SEPARATOR . $file ) )
    {
        require_once( __DIR__ . DIRECTORY_SEPARATOR . $file );
        return true;
    }
}


spl_autoload_register(__NAMESPACE__.'\autoload', true);

