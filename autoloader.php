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
		$backtrace = debug_backtrace();
		$call = end( $backtrace );
		define('__BASEDIR__',dirname($call['file']));
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

		private function __construct( $path, $namespace ) {
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
			$this->_namespace = $namespace;
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

		private static function load( $path, $namespace ) {
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
			return self::$_libraries[$path] = new self( $path, $namespace );
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
			// Framework libraries
			if( count( $segments ) === 2 && $segments[0] === 'Framework' ) {
				$segments[0] = dirname(__FILE__);
				$segments[1] = strtolower( $segments[1] );
			}
			// Site libraries
			else if( count( $segments ) === 2 && $segments[0] === 'Site' ) {
				$segments[0] = 'sites';
				$segments[1] = strtolower( $segments[1] );
			}
			// General libraries
			else {
				$namespace = $segments[0];
				$segments = array( strtolower( $namespace ) );
				// Extension libraries
				if( is_dir( $basedir.'/library/'.$segments[0] ) ) {
					array_unshift( $segments, 'library' );
				}
				// Site libraries
				else if( is_dir( $basedir.'/sites/'.$segments[0] ) ) {
					array_unshift( $segments, 'sites' );
				}
				// Add the basedir
				array_unshift( $segments, $basedir );
			}
			// Turn the segments into a path string
			if( ! is_dir( $path = implode( DIRECTORY_SEPARATOR, $segments ) ) ) {
				throw new \Exception( 'Library `'.$segments[0].'` does not exist.' );
			}
			// Fetch and return the library
			return new self( $path, $namespace );
		}

	 /**
	  *
	  * @param string $class Class you wish to load.
	  * @return bool Flag indicating whether the class was successfully loaded.
	  */

		public function loadClass( $class ) {
			// Find the class without the library namespace
			$true_class = trim( strtolower( $class ), "\\ \t\n\r\0\x0B" );
			if( substr( $true_class, 0, strlen( $this->namespace ) ) === strtolower( $this->namespace ) ) {
				$true_class = trim( substr( $true_class, strlen( $this->namespace ) ), "\\ \t\n\r\0\x0B" );
			}
			// Find a class file
			foreach( $this->classes() as $name => $path ) {
				if( strtolower( $name ) === $true_class && is_file( $this->path.$path ) ) {
					require_once $this->path.$path;
					break;
				}
			}
			// Return the results of class_exists
			return ( class_exists( $class, false ) || interface_exists( $class, false ) );
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
	  * The full path to the library's directory.
	  *
	  * @var string
	  */

		private $_namespace = null;

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
			  	case 'namespace' :
			  		return $this->_namespace;
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
			$this->_classes = $this->glob( '/classes/*.php', true );
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
	  * @param bool $recursive Flag indicating whether to glob subdirectories (true) or not (false). Defaults to false.
	  * @return array A collection of avaliable files.
	  */

		public function glob( $relativePath, $recursive=false ) {
			// Split the relative path
			$path = $this->_path;
			$pattern = $relativePath;
			if( ( $pos = strrpos( $pattern, '/' ) ) !== false ) {
				$path = $this->_path.substr( $pattern, 0, $pos );
				$pattern = substr( $pattern, $pos+1 );
			}
			// We're probably going to collect some folders
			$folders = array( $path );
			// If we're searching recursively, find folders!
			if( $recursive ) {
				$folder_path = $path;
				while( $found_folders = glob( $folder_path.'/*', GLOB_ONLYDIR ) ) {
					$folder_path .= '/*';
					$folders = array_merge( $folders, $found_folders );
				}
			}
			// Go through and find our files
			$files = array();
			foreach( $folders as $folder ) {
				// Find all the files
				foreach( glob( $folder.'/'.$pattern ) as $file ) {
					$relative = str_replace( $path.'/', '', $file );
					$name = pathinfo( str_replace( '/', '\\', $relative ), PATHINFO_FILENAME );
					if( substr( pathinfo( $file, PATHINFO_FILENAME ), 0, 1 ) !== '_' ) {
						$files[$name] = $file;
					}
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
  		// Try and get a library!
  		try {
	  		$library = Library::forClass( $className );
  		}
  		catch ( Exception $e ) {
	  		throw $e;
  		}
  		// No library found. Break everything.
  		if( ! isset( $library ) || ! $library instanceof Library ) {
			throw new \Exception( 'Library does not exist for `'.$className.'`.' );
		}
  		// Try loading the class
  		if( ! $library->loadClass( $className ) ) {
			throw new \Exception( 'Class `'.$className.'` is not available.' );
		}
	}
