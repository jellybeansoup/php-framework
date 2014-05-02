<?php
 /**
  * Utility functions for the App library.
  *
  * @package Framework\App
  * @author Daniel Farrelly <daniel@jellystyle.com>
  * @copyright 2014 Daniel Farrelly <daniel@jellystyle.com>
  * @license FreeBSD
  */

 /**
  * Fetch the default message string for a HTTP status code.
  *
  * @throws InvalidArgumentException if the given `$code` is not a integer.
  *
  * @param int $code The HTTP status code to translate.
  * @return string The default HTTP status message.
  */

	function http_translate_code( $code ) {
  		// Check the given parameters
	  	if( ! is_int( $code )  ) {
			throw new \InvalidArgumentException( 'Status is expected to be an integer.' );
	  	}
	  	// Return the string matching the given code.
		switch( $code ) {
			case 100: return 'Continue'; break;
			case 101: return 'Switching Protocols'; break;
			case 200: return 'OK'; break;
			case 201: return 'Created'; break;
			case 202: return 'Accepted'; break;
			case 203: return 'Non-Authoritative Information'; break;
			case 204: return 'No Content'; break;
			case 205: return 'Reset Content'; break;
			case 206: return 'Partial Content'; break;
			case 300: return 'Multiple Choices'; break;
			case 301: return 'Moved Permanently'; break;
			case 302: return 'Moved Temporarily'; break;
			case 303: return 'See Other'; break;
			case 304: return 'Not Modified'; break;
			case 305: return 'Use Proxy'; break;
			case 400: return 'Bad Request'; break;
			case 401: return 'Unauthorized'; break;
			case 402: return 'Payment Required'; break;
			case 403: return 'Forbidden'; break;
			case 404: return 'Not Found'; break;
			case 405: return 'Method Not Allowed'; break;
			case 406: return 'Not Acceptable'; break;
			case 407: return 'Proxy Authentication Required'; break;
			case 408: return 'Request Time-out'; break;
			case 409: return 'Conflict'; break;
			case 410: return 'Gone'; break;
			case 411: return 'Length Required'; break;
			case 412: return 'Precondition Failed'; break;
			case 413: return 'Request Entity Too Large'; break;
			case 414: return 'Request-URI Too Large'; break;
			case 415: return 'Unsupported Media Type'; break;
			case 500: return 'Internal Server Error'; break;
			case 501: return 'Not Implemented'; break;
			case 502: return 'Bad Gateway'; break;
			case 503: return 'Service Unavailable'; break;
			case 504: return 'Gateway Time-out'; break;
			case 505: return 'HTTP Version not supported'; break;
		}
		// Default is null
		return null;
  	}
