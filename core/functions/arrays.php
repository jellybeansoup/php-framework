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
  * Determine whether an array contains any associative keys.
  *
  * @param array $array The array to check.
  * @param bool $strict If true, all of the keys in the array must be strings.
  * @return bool A flag indicating whether the array is associative (true) or not (false).
  */

	function is_assoc( $array, $strict=false ) {
		// Check the given parameters
		if( ! is_array( $array ) ) {
			throw new \InvalidArgumentException;
		}
		// We just want the keys, really
		$keys = array_keys( $array );
		// Filter the keys to just those that are strings
		$keys = array_filter( $keys, 'is_string' );
		// If there are keys left, the array is associative
		return ( ( $strict && count( $keys ) === count( $array ) ) || count( $keys ) > 0 );
	}

 /**
  * Determine whether an array is indexed.
  *
  * @param array $array The array to check.
  * @return bool A flag indicating whether the array is indexed (true) or not (false).
  */

	function is_indexed( $array ) {
		// Check the given parameters
		if( ! is_array( $array ) ) {
			throw new \InvalidArgumentException;
		}
		// We just want the keys, really
		$keys = array_keys( $array );
		// Filter the keys to just those that are strings
		$keys = array_filter( $keys, 'is_string' );
		// If there are keys left, the array is associative
		return ( count( $keys ) === 0 );
	}

 /**
  * Remove the given value from the given array
  *
  * @param array $array The array to remove values from.
  * @param mixed $value The value to remove from the given array.
  * @param bool $ignoreKeys Return the values of the array, ignoring the original keys. Defaults to false.
  * @return array The altered array with all instances of the given value removed.
  */

	function array_remove( $array, $value, $ignoreKeys=false ) {
		// Check the given parameters
		if( ! is_array( $array ) ) {
			throw new \InvalidArgumentException;
		}
		if( ! is_bool( $ignoreKeys ) ) {
			throw new \InvalidArgumentException;
		}
		// Find the key for the given value
		while( ( $key = array_search( $value, $array ) ) !== false ) {
			unset( $array[$key] );
		}
		if( $ignoreKeys === true && ! self::isAssociative( $array ) ) {
			$array = array_values( $array );
		}
		// Return the modified array
		return $array;
	}

 /**
  * Call a method on objects in an array that implement it.
  *
  * @param array $array The array of objects on which to call the given method.
  * @param string $methodName The name of the method to call on the provided objects.
  * @param array $args Arguments to pass to the objects' methods.
  * @return void
  */

	function array_call_method( &$array, $methodName, $args=null ) {
		// Check the given parameters
		if( ! is_string( $methodName ) ) {
			throw new \InvalidArgumentException;
		}
		if( $args !== null && ! is_array( $args ) ) {
			throw new \InvalidArgumentException;
		}
		// We need to ensure all the objects have the given method
		$filtered = array_filter( $array, function( $item ) use( $methodName ) {
			return ( is_object( $item ) && method_exists( $item, $methodName ) );
		});
		if( count( $filtered ) !== count( $array ) ) {
			throw new \InvalidArgumentException;
		}
		// Walk through and call themethod with the given name
		array_walk($array,function( &$n ) use ( $methodName, $args ) {
			call_user_func_array(array( $n, $methodName ),$args);
		});
	}
