<?php
 /**
  * App library
  */

	namespace Framework\App;

 /**
  * Representation of a HTTP request.
  */

	class Request extends \Framework\Core\Object {

	 /**
	  * Fetch the request's SERVER variables
	  *
	  * @return
	  */

		public static function server( $key, $default=null ) {
			return ( is_array( $_SERVER ) && isset( $_SERVER[$key] ) ) ? $_SERVER[$key] : $default;
		}

	 /**
	  * Fetch the request's GET variables
	  *
	  * @return
	  */

		public static function get( $key, $default=null ) {
			return ( is_array( $_GET ) && isset( $_GET[$key] ) ) ? $_GET[$key] : $default;
		}

	 /**
	  * Fetch the request's POST variables
	  *
	  * @return
	  */

		public static function post( $key, $default=null ) {
			return ( is_array( $_POST ) && isset( $_POST[$key] ) ) ? $_POST[$key] : $default;
		}

	 /**
	  * Fetch the request's POST variables
	  *
	  * @return
	  */

		public static function files( $key, $default=null ) {
			return ( is_array( $_FILES ) && isset( $_FILES[$key] ) ) ? $_FILES[$key] : $default;
		}

	 /**
	  * Fetch the request's POST variables
	  *
	  * @return
	  */

		public static function request( $key, $default=null ) {
			return ( is_array( $_REQUEST ) && isset( $_REQUEST[$key] ) ) ? $_REQUEST[$key] : $default;
		}

	 /**
	  * Fetch the request's POST variables
	  *
	  * @return
	  */

		public static function session( $key, $default=null ) {
			return ( is_array( $_SESSION ) && isset( $_SESSION[$key] ) ) ? $_SESSION[$key] : $default;
		}

	 /**
	  * Fetch the request's POST variables
	  *
	  * @return
	  */

		public static function environment( $key, $default=null ) {
			return ( is_array( $_ENV ) && isset( $_ENV[$key] ) ) ? $_ENV[$key] : $default;
		}

	 /**
	  * Fetch the request's POST variables
	  *
	  * @return
	  */

		public static function cookie( $key, $default=null ) {
			return ( is_array( $_COOKIE ) && isset( $_COOKIE[$key] ) ) ? $_COOKIE[$key] : $default;
		}

	}
