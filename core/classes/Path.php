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
  * Class Manager
  */

  	class Path extends Object {

//
// Properties
//

	 /**
	  * The root path string
	  *
	  * @var string
	  */

	  private $_root = null;

	 /**
	  * The path string
	  *
	  * @var string
	  */

	  private $_path = null;

	 /**
	  * Dynamic properties
	  *
	  * @var array
	  */

		protected static $_dynamicProperties = array(
			'absolutePath',
			'root',
			'lastComponent',
			'pathByDeletingLastComponent',
			'components',
			'filename',
			'extension',
			'pathByDeletingExtension',
			'trimmed',
			'contents',
		);

//
// Magic Methods
//

	 /**
	  * Constructor
	  *
	  * @return void
	  */

		public function __construct( $path ) {
			// Capture the file the path was created in if the path is relative
			if( ! ( $startsWithSlash = preg_match( '/^\s*\//i', $path ) ) ) {
				$backtrace = debug_backtrace();
				foreach( $backtrace as $call ) {
					if( isset( $call['file'] ) && $call['file'] !== __FILE__ ) {
						$this->_root = pathinfo( $call['file'], PATHINFO_DIRNAME );
						break;
					}
				}
			}
			// Clean the path
			$cleanPath = trim( $path, "/ \t\n\r\0\x0B" );
			$cleanPath = preg_replace( '/[\/]+/', '/', $cleanPath );
			$cleanPath = $startsWithSlash ? '/'.$cleanPath : $cleanPath;
			$this->_path = $cleanPath ? $cleanPath : '/';
	  	}

	 /**
	  * String Value
	  *
	  * @return
	  */

		public function asString() {
			return $this->_path;
	  	}

//
// Accessing the Parts of the URL
//

	 /**
	  *
	  * @return
	  */

		public static function create( $string=null ) {
			return new self( $string );
	  	}

//
// Accessing the Parts of the URL
//

	 /**
	  * The last component of the path
	  *
	  * @return string The segment following the final backslash in the path
	  */

		public function absolutePath() {
			return $this->root()->pathByAddingComponent( $this->_path );
	  	}

	 /**
	  *
	  *
	  * @return string
	  */

		public function root() {
			$root = new Path( '' );
			if( $this->_root !== null ) {
				$root->_path = DIRECTORY_SEPARATOR.$this->_root;
			}
			else if( strpos( document_root(), $this->_path ) === 0 ) {
				$root->_path = DIRECTORY_SEPARATOR.document_root();
			}
			return $root;
	  	}

	 /**
	  *
	  *
	  * @return string
	  */

		public function setRoot( $root ) {
			$this->_root = $root;
	  	}

	 /**
	  * The last component of the path
	  *
	  * @return string The segment following the final backslash in the path
	  */

		public function lastComponent() {
			return pathinfo( $this->_path, PATHINFO_BASENAME );
	  	}

	 /**
	  * The path after removing the final component
	  *
	  * @return string The path up until the final backslash in the string
	  */

		public function pathByDeletingComponent( $number ) {
			// Fetch the components
			$components = $this->components();
			// If there's no component for that number, let's not bother
			if( ! isset( $components[$number-1] ) ) {
				return $this;
			}
			// Remove the numbered component
			unset( $components[$number-1] );
			// Return the other components as a new path
			$path = new Path( implode( DIRECTORY_SEPARATOR, $components ) );
			$path->_root = $this->_root;
			return $path;
	  	}

	 /**
	  * The path with a new component
	  *
	  * @return string The path up until the final backslash in the string
	  */

		public function pathByAddingComponent( $component ) {
			$path = new Path( $this->_path.DIRECTORY_SEPARATOR.$component );
			$path->_root = $this->_root;
			return $path;
	  	}

	 /**
	  * The path after removing the final component
	  *
	  * @return string The path up until the final backslash in the string
	  */

		public function pathByDeletingLastComponent() {
			// If there's no last component, let's not bother
			if( ! $this->lastComponent() ) {
				return $this;
			}
			// Find the start of the last component and strip it out in a new Path
			$len = 0 - ( strlen( $this->lastComponent() ) + 1 );
			$path = new Path( substr( $this->_path, 0, $len ) );
			$path->_root = $this->_root;
			return $path;
	  	}

	 /**
	  * The path components as an indexed array
	  *
	  * @return array The segments of the path
	  */

		public function components() {
			$trimmed = $this->trimmed();
			// We split by the '/'
			if( strpos( $trimmed, '/' ) !== false ) {
				return explode( '/', $trimmed );
			}
			// Only one component
			else if( strlen( $trimmed ) > 0 ) {
				return array( $trimmed );
			}
			// Default is an empty array
			return array();
	  	}

	 /**
	  * Fetch the component at the given index.
	  *
	  * @param int $index The index of the component you want to fetch.
	  * @return string|null The component at the given index, or null if no component exists at that index.
	  */

		public function componentAtIndex( $index ) {
			return isset( $this->components[$index] ) ? $this->components[$index] : null;
	  	}

	 /**
	  * The filename
	  *
	  * @return string The characters following the final backslash until the last period (.) in the string
	  */

		public function filename() {
			return pathinfo( $this->_path, PATHINFO_FILENAME );
	  	}

	 /**
	  * The extension of the path
	  *
	  * @return string The characters following the last period (.) in the string
	  */

		public function extension() {
			return pathinfo( $this->_path, PATHINFO_EXTENSION );
	  	}

	 /**
	  * The path after removing the extension
	  *
	  * @return string The path up until the last period (.) in the string
	  */

		public function pathByAddingExtension( $extension ) {
			$path = new Path( $this->_path.'.'.$extension );
			$path->_root = $this->_root;
			return $path;
	  	}

	 /**
	  * The path after removing the extension
	  *
	  * @return string The path up until the last period (.) in the string
	  */

		public function pathByDeletingExtension() {
			// If there's no extension, let's not bother
			if( ! $this->extension() ) {
				return $this;
			}
			// Find the start of the extension and strip it out in a new Path
			$len = 0 - ( strlen( $this->extension() ) + 1 );
			$path = new Path( substr( $this->_path, 0, $len ) );
			$path->_root = $this->_root;
			return $path;
	  	}

	 /**
	  * The extension of the path
	  *
	  * @return string The characters following the last period (.) in the string
	  */

		public function trimmed() {
			return trim( $this->_path, DIRECTORY_SEPARATOR." \t\n\r\0\x0B" );
	  	}

//
// Comparing paths
//

	 /**
	  * Determine whether the path is a sub path of a given path.
	  *
	  */

		public function isSubpathOf( Path $path ) {
			return ( strpos( $this->_path, (string) $path ) === 0 );
	  	}

	 /**
	  * Fetch the current path, relative to the given path.
	  *
	  */

		public function relativeTo( Path $relativeTo ) {
			// Get the path strings
			$path = $this->_path;
			$relativeTo = $relativeTo->_path;
			// Super simple: $relativeTo equals $path
			if( $path === $relativeTo ) {
				return $this;
			}
			// Simple: $relativeTo is in $path
			if( strpos( $path, $relativeTo ) === 0 ) {
				return new Path( substr( $path, strlen( $relativeTo ) ) );
			}
			// More difficult: $relativeTo is outside of $path
			$relative  = array();
			$pathParts = explode( DIRECTORY_SEPARATOR, $path );
			$relativeToParts = explode( '/', $relativeTo );
			foreach( $relativeToParts as $index => $part ) {
				if( isset( $pathParts[$index] ) && $pathParts[$index] == $part ) {
					continue;
				}
				$relative[] = '..';
			}
			foreach( $pathParts as $index => $part ) {
				if( isset( $relativeToParts[$index] ) && $relativeToParts[$index] == $part ) {
					continue;
				}
				$relative[] = $part;
			}
			return new Path( implode( DIRECTORY_SEPARATOR, $relative ) );
	  	}

//
// Working with file contents
//

	 /**
	  *
	  * @return string
	  */

		public function exists() {
			return file_exists( $this->absolutePath() );
	  	}

	 /**
	  *
	  * @return string
	  */

		public function isFile() {
			return is_file( $this->absolutePath() );
	  	}

	 /**
	  *
	  * @return string
	  */

		public function isFolder() {
			return is_dir( $this->absolutePath() );
	  	}

	 /**
	  *
	  * @return string
	  */

		public function contents() {
			if( is_file( $absolutePath = $this->absolutePath() ) ) {
				return file_get_contents( $absolutePath );
			}
			return null;
	  	}

	 /**
	  *
	  * @return string
	  */

		public function setContents( $value ) {
			if( is_file( $absolutePath = $this->absolutePath() ) ) {
				return file_put_contents( $absolutePath, $value );
			}
			return false;
	  	}

	 /**
	  *
	  * @return mixed
	  */

		public function inc( $once=true ) {
			if( is_file( $absolutePath = $this->absolutePath() ) ) {
				return ( $once === true ) ? include_once $absolutePath : include $absolutePath;
			}
			return null;
	  	}

	 /**
	  *
	  * @return mixed
	  */

		public function req( $once=true ) {
			if( is_file( $absolutePath = $this->absolutePath() ) ) {
				return ( $once === true ) ? require_once $absolutePath : require $absolutePath;
			}
			return null;
	  	}

  	}