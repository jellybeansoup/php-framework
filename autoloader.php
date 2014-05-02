<?php
 /**
  * Library Autoloading
  *
  * This takes care of loading libraries on-the-fly, reducing the need for including
  * files manually. Simply reference a class within a library to include it, or load
  * manually using `Library::load( 'libraryName' );` as required.
  *
  * @author Daniel Farrelly <daniel@jellystyle.com>
  * @copyright 2014 Daniel Farrelly <daniel@jellystyle.com>
  * @license FreeBSD
  */

	// Determine the base directory
	if( ! defined('__BASEDIR__') ) {
		$backtrace = end( debug_backtrace() );
		define('__BASEDIR__',dirname($backtrace['file']));
	}

 /**
  * Library class.
  *
  * Represents an individual library, as defined by the `path` provided when loading
  * the library.
  *
  * Loading a library is done using the `load` method, which will automatically include
  * all the libraries functions files. Library classes are available by default using the
  * `__autoload` function at the base of this file, and if it hasn't been already, the
  * library will be loaded prior to autoloading the class.
  */

	class Library {

//
// Loading libraries
//

	 /**
	  * Loaded instances of `Library` objects.
	  *
	  * @var array
	  */

		private static $_libraries = array();

	 /**
	  * Constructor
	  *
	  * @throws InvalidArgumentException if the given `$path` is not a string.
	  * @throws InvalidLibraryException if the given `$path` doesn't point to an existing directory.
	  *
	  * @param string $path The full path to the library on the server.
	  * @return void
	  */

		private function __construct( $path ) {
	  		// Check the given parameters
		  	if( ! is_string( $path ) ) {
				throw new \InvalidArgumentException( 'Path is expected to be a string.' );
		  	}
			// Check that the library exists
			if( ! file_exists( $path ) || ! is_dir( $path ) ) {
				throw new \InvalidLibraryException( 'Library does not exist at '.$path.'.' );
			}
			// Store the path
			$this->_path = $path;
			// Load the functions automatically
			foreach( $this->functions() as $function ) {
				require_once( $path.$function );
			}
		}

	 /**
	  * Load a library at the given path.
	  *
	  * @throws InvalidArgumentException if the given `$path` is not a string.
	  * @throws InvalidLibraryException if the given `$path` doesn't point to an existing directory.
	  *
	  * @param string $path Path to the library to be loaded.
	  * @return Library An object representing the loaded library.
	  */

		public static function load( $path ) {
	  		// Check the given parameters
		  	if( ! is_string( $path ) ) {
				throw new \InvalidArgumentException( 'Path is expected to be a string.' );
		  	}
			// Check that the library exists
			if( ! file_exists( $path ) || ! is_dir( $path ) ) {
				throw new \InvalidLibraryException( 'Library does not exist at '.$path.'.' );
			}
			// Determine the real path
			$path = realpath( $path );
			// Library already loaded.
			if( isset( self::$_libraries[$path] ) && self::$_libraries[$path] instanceof self ) {
				return self::$_libraries[$path];
			}
			// Create and return the library object
			return self::$_libraries[$path] = new self( $path );
		}

	 /**
	  *
	  * @param string $path Path to the library to be loaded.
	  * @return Library An object representing the loaded library.
	  */

		public static function forNamespace( $namespace ) {
		  	// Get the base directory
	  		$basedir = defined('__BASEDIR__')?__BASEDIR__:dirname(__FILE__);
	  		// Check the given parameters
		  	if( ! is_string( $namespace ) ) {
				throw new \InvalidArgumentException( 'Namespace is expected to be a string.' );
		  	}
			// Divide up the namespace
			$segments = explode( '\\', trim( $namespace, "\\ \t\n\r\0\x0B" ) );
			// General libraries
			if( count( $segments ) === 1 ) {
				$segments[0] = strtolower( $segments[0] );
				// Extension libraries
				if( is_dir( $basedir.'/library/'.$segments[0] ) ) {
					array_unshift( $segments, 'library' );
				}
				// Site libraries
				else if( is_dir( $basedir.'/sites/'.$segments[0] ) ) {
					array_unshift( $segments, 'sites' );
				}
				// Can't find the library
				else if( ! is_dir( $basedir.'/'.$segments[0] ) ) {
					throw new \Exception( 'Library `'.$segments[0].'` does not exist.' );
				}
				array_unshift( $segments, $basedir );
			}
			// Framework libraries
			else if( count( $segments ) === 2 && $segments[0] === 'Framework' ) {
				$segments[0] = 'library';
				$segments[1] = strtolower( $segments[1] );
				array_unshift( $segments, dirname(__FILE__) );
			}
			// Site libraries
			else if( count( $segments ) === 2 && $segments[0] === 'Site' ) {
				$segments[0] = 'sites';
				$segments[1] = strtolower( $segments[1] );
				array_unshift( $segments, $basedir );
			}
			// Invalid library namespace
			else {
				throw new \InvalidLibraryException( 'The namespace `'.$namespace.'` does not match any available libraries.' );
			}
			// Turn the segments into a path string
			$path = implode( DIRECTORY_SEPARATOR, $segments );
			// Fetch and return the library
			return new self( $path );
		}

	 /**
	  *
	  * @param string $path Path to the library to be loaded.
	  * @return Library An object representing the loaded library.
	  */

		public static function forClass( $class ) {
	  		// Check the given parameters
		  	if( ! is_string( $class ) ) {
				throw new \InvalidArgumentException( 'Class name is expected to be a string.' );
		  	}
			// Divide up the namespace
			$segments = explode( '\\', trim( $class, "\\ \t\n\r\0\x0B" ) );
			// General libraries
			if( count( $segments ) === 2 ) {
				$namespace = array_slice( $segments, 0, 1 );
				return self::forNamespace( implode( '\\', $namespace ) );
			}
			// Framework libraries
			else if( count( $segments ) === 3 ) {
				$namespace = array_slice( $segments, 0, 2 );
				return self::forNamespace( implode( '\\', $namespace ) );
			}
			// Invalid library namespace
			else {
				throw new \InvalidLibraryException( 'The namespace `'.$namespace.'` does not match any available libraries.' );
			}
		}

//
// Library details
//

	 /**
	  * The full path to the library's directory.
	  *
	  * @var string
	  */

		private $_path = null;

	 /**
	  * Get magic method.
	  *
	  * @param string $property The name of the property to return the value for.
	  * @return mixed The value for the requested property. Defaults to null.
	  */

	  	public function __get( $property ) {
		  	switch( $property ) {
			  	case 'path' :
			  		return $this->_path;
			  	default:
			  		return null;
		  	}
	  	}


	 /**
	  * Collection of functions files.
	  *
	  * @var array
	  */

		private $_functions = null;

	 /**
	  * Returns a collection of functions files.
	  * Does not include files that begin with an underscore (_).
	  * Caches the collection in the `$_functions` property.
	  *
	  * @return array A collection of avaliable functions files.
	  */

		public function functions() {
			// We've already cached the list
			if( $this->_functions !== null && is_array( $this->_functions ) ) {
				return $this->_functions;
			}
			// Find all the directories in the library folder
			$this->_functions = $this->glob( '/functions/*.php' );
			foreach( $this->_functions as $name => $path ) {
				$this->_functions[$name] = str_replace( $this->_path, '', $path );
			}
			// Return the array
			return $this->_functions;
		}

	 /**
	  * Collection of class files.
	  *
	  * @var array
	  */

		private $_classes = null;

	 /**
	  * Returns a collection of class files.
	  * Does not include files that begin with an underscore (_).
	  * Caches the collection in the `$_classes` property.
	  *
	  * @return array A collection of avaliable class files.
	  */

		public function classes() {
			// We've already cached the list
			if( $this->_classes !== null && is_array( $this->_classes ) ) {
				return $this->_classes;
			}
			// Find all the directories in the library folder
			$this->_classes = $this->glob( '/classes/*.php' );
			foreach( $this->_classes as $name => $path ) {
				$this->_classes[$name] = str_replace( $this->_path, '', $path );
			}
			// Return the array
			return $this->_classes;
		}

	 /**
	  * Returns a collection of class matching the given `$relativePath` in the library folder.
	  *
	  * @param string $relativePath The path/pattern you want to match.
	  * @return array A collection of avaliable files.
	  */

		public function glob( $relativePath ) {
			// Find all the files
			$glob_paths = glob( $this->_path.$relativePath );
			$files = array();
			foreach( $glob_paths as $path ) {
				$name = pathinfo( $path, PATHINFO_FILENAME );
				if( substr( $name, 0, 1 ) !== '_' ) {
					$files[$name] = $path;
				}
			}
			// Return the array
			return $files;
		}

	}

 /**
  * Invalid library exception.
  * This exception is thrown when a requested library is invalid.
  *
  * @api
  */

	class InvalidLibraryException extends Exception {}

 /**
  * Set up the class autoloader.
  *
  * This implements PHP's autoloading functionality in conjunction with the `Library`
  * class above. The given class' library will be loaded prior to loading the class
  * itself, allowing library classes to use the functions that are part of the library.
  *
  * @ignore
  * @param string $className The name of the class to be loaded.
  * @return void
  */

  	function __autoload( $className ) {
	  	// Get the base directory
  		$basedir = defined('__BASEDIR__')?__BASEDIR__:dirname(__FILE__);
 		// Check the given parameters
	  	if( ! is_string( $className ) ) {
			throw new \InvalidArgumentException( 'Class name is expected to be a string.' );
	  	}
		// Divide up the class name
		$segments = explode( '\\', trim( $className, "\\ \t\n\r\0\x0B" ) );
		// This will be the library path
		$library_segments = array();
		$class_segments = array();
		// General libraries
		if( count( $segments ) === 2 ) {
			$segments[0] = strtolower( $segments[0] );
			// Extension libraries
			if( is_dir( $basedir.'/library/'.$segments[0] ) ) {
				array_unshift( $segments, 'library' );
				$library_segments = array_slice( $segments, 0, 2 );
				$class_segments = array_slice( $segments, 2 );
			}
			// Site libraries
			else if( is_dir( $basedir.'/sites/'.$segments[0] ) ) {
				array_unshift( $segments, 'sites' );
				$library_segments = array_slice( $segments, 0, 2 );
				$class_segments = array_slice( $segments, 2 );
			}
			// Custom libraries
			else if( is_dir( $basedir.'/'.$segments[0] ) ) {
				$library_segments = array_slice( $segments, 0, 1 );
				$class_segments = array_slice( $segments, 1 );
			}
			// Can't find the library
			else {
				throw new \Exception( 'Library `'.$segments[0].'` is invalid.' );
			}
			// Add the base directory
			array_unshift( $library_segments, $basedir );
		}
		// Framework libraries
		else if( count( $segments ) === 3 && $segments[0] === 'Framework' ) {
			$segments[0] = dirname(__FILE__);
			$segments[1] = strtolower( $segments[1] );
			$library_segments = array_slice( $segments, 0, 2 );
			$class_segments = array_slice( $segments, 2 );
		}
		// Site libraries
		else if( count( $segments ) === 3 && $segments[0] === 'Site' ) {
			$segments[0] = 'sites';
			$segments[1] = strtolower( $segments[1] );
			$library_segments = array_slice( $segments, 0, 2 );
			$class_segments = array_slice( $segments, 2 );
			array_unshift( $library_segments, $basedir );
		}
		// Custom libraries
		else if( count( $segments ) >= 3 ) {
			$library_segments = array_slice( $segments, 0, 1 );
			$class_segments = array_slice( $segments, 1 );
			array_unshift( $library_segments, $basedir );
		}
		// Classes go in the classes folder
		array_unshift( $class_segments, 'classes' );
		// Turn the paths into strings
		$library_path = implode( DIRECTORY_SEPARATOR, $library_segments );
		$class_path = DIRECTORY_SEPARATOR.implode( DIRECTORY_SEPARATOR, $class_segments ).'.php';
		// Can't find the library
		if( ! file_exists( $library_path ) || ! is_dir( $library_path ) ) {
			var_dump( $library_path );
			throw new \Exception( 'Library does not exist for `'.$className.'`.' );
		}
		// Fetch the library
		$library = Library::load( $library_path );
		// If the library has the class
		if( ! in_array( $class_path, $library->classes() ) ) {
			throw new \Exception( 'Class `'.$className.'` is not available.' );
		}
		// Include the file for the given class
		require_once( $library_path.$class_path );
	}
