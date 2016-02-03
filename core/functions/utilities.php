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
  * Determines the root folder in which the framework is running from.
  *
  * This is determined using the directory path for the initial script that
  * loads the framework, usually `index.php`.
  *
  * @return \Framework\Core\Path The path for the document root.
  */

	function document_root() {
		// We store the value in a constant
		if( ! defined( '__DOCUMENT_ROOT' ) ) {
			$backtrace = debug_backtrace();
			$call = end( $backtrace );
			/** @internal */
			define( '__DOCUMENT_ROOT', dirname( $call['file'] ) );
		}
		// Return the constant
		return \Framework\Core\Path::create( __DOCUMENT_ROOT );
	}

 /**
  * Create a `URL` object with an optional relative path.
  *
  * The given path is always relative to the base URL, regardless of whether it
  * is prefixed with a seperator (i.e. '/') or not.
  *
  * @param string $path The relative path to create a `URL` object with.
  * @return \Framework\Core\URL The URL created using the given relative path.
  */

	function url( $path=null ) {
		return \Framework\Core\URL::create( $path );
	}

 /**
  * Create a `Path` object with an optional relative path.
  *
  * If the relative path provided is prefixed with a seperator (i.e. '/'), the
  * `Path` created is relative to the document root. If it is defined without one,
  * the `Path` is created relative to the current file.
  *
  * @param string $path The relative path to create a `Path` object with.
  * @return \Framework\Core\Path The path created using the given relative path.
  */

	function path( $path=null ) {
		// Capture the file the path was created in if the path is relative
		if( ! ( $startsWithSlash = preg_match( '/^\s*\//i', $path ) ) && ( $calling_path = calling_file() ) !== null ) {
			$path = dirname( $calling_path ).DIRECTORY_SEPARATOR.$path;
		}
		return \Framework\Core\Path::create( $path );
	}

 /**
  * Print given arguments in a HTML `pre` tag.
  *
  * @param mixed $var One or more values to be printed.
  * @return \Framework\Core\Path The path created using the given relative path.
  */

	function dump() {
		if( count( $args = func_get_args() ) ) {
			echo '<pre>';
			call_user_func_array( 'var_dump', $args );
			echo '</pre>';
		}
	}

 /**
  *
  * @return \Framework\Core\Path The path created using the given relative path.
  */

	function calling_file() {
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 );
		$offset = 1;

		$call = $backtrace[$offset];
		if( isset( $call['function'] ) && substr( $call['function'], 0, 6 ) === '__call' ) {
			$offset += 1;
		}

		$call = $backtrace[$offset];
		return isset( $call['file'] ) ? $call['file'] : null;
	}

 /**
  *
  * @return \Framework\Core\Path The path created using the given relative path.
  */

	function calling_class() {
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 );
		$offset = 1;

		$call = $backtrace[$offset];
		if( isset( $call['function'] ) && substr( $call['function'], 0, 6 ) === '__call' ) {
			$offset += 1;
		}

		// We skip an entry to get the calling class
		$offset += 1;

		$call = $backtrace[$offset];

		if( isset( $call['class'] ) ) {
			return $call['class'];
		}
		else return null;
	}

 /**
  *
  * @return \Framework\Core\Path The path created using the given relative path.
  */

	function calling_function() {
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 );
		$offset = 1;

		$call = $backtrace[$offset];
		if( isset( $call['function'] ) && substr( $call['function'], 0, 6 ) === '__call' ) {
			$offset += 1;
		}

		// We skip an entry to get the calling class
		$offset += 1;

		$call = $backtrace[$offset];

		if( isset( $call['class'] ) && isset( $call['function'] ) ) {
			$type = ! empty( $call['type'] ) ? $call['type'] : '::';
			return $call['class'].$type.$call['function'].'()';
		}
		else if( isset( $call['function'] ) ) {
			return $call['function'].'()';
		}
		else return null;
	}
