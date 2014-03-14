<?php
 /**
  * Framework Core
  *
  * @package Framework\Core
  * @author Daniel Farrelly <daniel@jellystyle.com>
  * @copyright 2014 Daniel Farrelly <daniel@jellystyle.com>
  * @license FreeBSD
  */

	namespace Framework\Core;

 /**
  * Arr
  */

	class Date extends Object implements DebugValue {

	 /**
	  * The timestamp representing the date.
	  */

		protected $stamp;

	 /**
	  * Constructor
	  * @param mixed $stamp A timestamp or string representation of a date.
	  */

		public function __construct( $stamp=false ) {
			// If the provided stamp is a date object...
			if( $stamp instanceof Date )
				$this->stamp = (int) $stamp->stamp;
			// If the provided stamp is numeric...
			elseif( is_numeric( $stamp ) )
				$this->stamp = (int) $stamp;
			// If the stamp is a string...
			elseif( is_string( $stamp ) )
				$this->stamp = (int) strtotime( $stamp );
			// If no stamp was provided...
			$this->stamp = ( $this->stamp ) ? $this->stamp : time();
		}

	 /**
	  * String representation of the date object.
	  */

		public function asString() {
			return (string) $this->format( 'Y-m-d H:i:s' );
		}

	 /**
	  * Debug Value
	  */

		public function __toDebug() {
			return self::__toString();
	  	}

	 /**
	  * Fetch the timestamp representing the date object.
	  * @return int Fetch the timestamp representing the date object.
	  */

		public function stamp() {
			return $this->stamp;
		}

	 /**
	  * Fetch a formatted version of the date object.
	  * @param string $format Format to output the date in.
	  * @return String representation of the date object, following the format provided.
	  */

		public function format( $format ) {
			return date( $format, $this->stamp );
		}

	 /**
	  * Fetch the amount of time since the object's date.
	  * @return string The amount of time since the object's date.
	  */

		public function since() {
			// If the current time is greater than the object's timestamp
			if( time() > $this->stamp ) :
				// Get the time difference
				$difference = (int)( time() - $this->stamp );
				// Fetch the difference as a string
				return $this->difference( $difference );
			// If the difference is less than 30 seconds.
			elseif( time() - $this->stamp < 30 ) :
				return 'less than 30 seconds';
			endif;
			// Default to false
			return 'less than 30 seconds';
		}

	 /**
	  * Fetch the amount of time until the object's date.
	  * @return string The amount of time until the object's date.
	  */

		public function until() {
			// If the current time is greater than the object's timestamp
			if( time() < $this->stamp ) :
				// Get the time difference
				$difference = (int)( $this->stamp - time() );
				// Fetch the difference as a string
				return $this->difference( $difference );
			// If the difference is less than 30 seconds.
			elseif( $this->stamp - time() < 30 ) :
				return 'less than 30 seconds';
			endif;
			// Default to false
			return 'less than 30 seconds';
		}

	 /**
	  * Fetch the difference (as a string) between now and the object's date.
	  * @param int $seconds The amount of time between now and the object's timestamp.
	  * @return String representation of the time difference.
	  */

		private function difference( $seconds ) {
			// Create an array for output
		 	$since = array();
			// Weeks
			$weeks = (int)( $seconds / 604800 );
			if( $weeks >= 1 && count( $since ) < 2 ) :
				$since[] = sprintf( '%s week%s', $weeks, ( $weeks != 1 ) ? 's' : false );
				$seconds = (int)( $seconds - ( $weeks * 604800 ) );
			endif;
			// Days
			$days = (int)( $seconds / 86400 );
			if( $days >= 1 && count( $since ) < 2 ) :
				$since[] = sprintf( '%s day%s', $days, ( $days != 1 ) ? 's' : false );
				$seconds = (int)( $seconds - ( $days * 86400 ) );
			endif;
			// Hours
			$hours = (int)( $seconds / 3600 );
			if( $hours >= 1 && count( $since ) < 2 ) :
				$since[] = sprintf( '%s hour%s', $hours, ( $hours != 1 ) ? 's' : false );
				$seconds = (int)( $seconds - ( $hours * 3600 ) );
			endif;
			// Minutes
			$minutes = (int)( $seconds / 60 );
			if( $minutes >= 1 && count( $since ) < 2 ) :
				$since[] = sprintf( '%s minute%s', $minutes, ( $minutes != 1 ) ? 's' : false );
				$seconds = (int)( $seconds - ( $minutes * 60 ) );
			endif;
			// Seconds
			$seconds = (int)( $seconds );
			if( $seconds >= 1 && count( $since ) < 2 ) :
				$since[] = sprintf( '%s second%s', $seconds, ( $seconds != 1 ) ? 's' : false );
			endif;
			// Output
			return implode( ', ', $since );
		}

	}