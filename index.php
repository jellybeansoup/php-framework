<?php
 /**
  * By default the framework runs with error reporting set to ALL. For security
  * reasons you are encouraged to change this when your site goes live.
  * For more info visit:  http://www.php.net/error_reporting.
  *
  * Recommended settings are as follows:
  * Development: E_ALL, display_errors 1.
  * Production: 0, display_errors 0.
  */

	error_reporting( E_ALL );
	ini_set( 'display_errors', 1 );

 /**
  * Prep the library autoloader.
  */

	require_once 'autoloader.php';

 /**
  * From here
  */

    // Bootstrap a delegate and get a response object
    $response = \JellyStyle\AppDelegate::bootstrap();

    // If we get a valid
    if( $response instanceof Framework\App\Response ) {
        $response->send();
    }
    else {
        var_dump( $response );
    }
