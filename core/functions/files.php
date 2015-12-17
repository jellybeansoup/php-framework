<?php
 /**
  * Framework Core
  *
  * @package Framework\Core
  * @author Daniel Farrelly <daniel@jellystyle.com>
  * @copyright 2014 Daniel Farrelly <daniel@jellystyle.com>
  * @license FreeBSD
  */

 /**
  * Format bytes (like for file sizes) into a more human readable format.
  *
  * Usage: `format_bytes( 326549 );`
  *
  * @since UnderSwell 1.0
  * @return string The formatted byte string.
  */

	function format_bytes( $bytes, $precision = 2 ) {
	    $units = array('B', 'KB', 'MB', 'GB', 'TB');

	    $bytes = max($bytes, 0);
	    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	    $pow = min($pow, count($units) - 1);

	    // Uncomment one of the following alternatives
	    $bytes /= (1 << (10 * $pow));

	    return round($bytes, $precision) . $units[$pow];
	}
