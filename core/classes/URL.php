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
  * URL Class
  */

	class URL extends Base {

//
// Properties
//

	 /**
	  * The scheme of the URL (usually 'http' or 'https')
	  * @var string
	  */

		public $scheme;

	 /**
	  * The host/domain part of the URL
	  * @var string
	  */

		public $host;

	 /**
	  * The port of the URL
	  * @var string
	  */

		public $port;

	 /**
	  * The username used for basic authentication
	  * @var string
	  */

		public $user;

	 /**
	  * The password used for basic authentication
	  * @var string
	  */

		public $password;

	 /**
	  * The part of the path that points to the root of the site
	  * @var \Framework\Core\Path
	  */

		private $_rootPath;

	 /**
	  * The path, not including the root path
	  * @var \Framework\Core\Path
	  */

		private $_path;

	 /**
	  * The query string, parsed as a URL
	  * @var array
	  */

		public $query = array();

	 /**
	  * The fragment part of the URL (comes after the hash '#')
	  * @var string
	  */

		public $fragment;

	 /**
	  * Dynamic properties
	  */

		protected static $_dynamicProperties = array(
			'path',
			'rootPath',
			'absoluteString',
			'baseString',
			'extendedPath',
			'fullPath',
			'lastPathComponent',
			'pathComponents',
			'pathExtension',
			'relativeString',
			'resourceSpecifier',
			'serverString'
		);

//
// Magic Methods
//

	 /**
	  * Constructor
	  */

		public function __construct( $string=null ) {
			$segments = array();
			// Handling a URL
			if( $string !== null ) {
				// Trim that string!
				$string = trim( $string );
				// Non-standard schemes
				$string = preg_replace( '/^(?!https?)([a-z][a-z0-9\+\.\-]+):\/\//i', '$1:', $string );
				// Exception URLs are an... exception.
				if( preg_match( '/^exception:(\/\/)?(\d+)$/i', $string, $match ) ) {
					$this->scheme = 'exception';
					$this->path = ( substr( $match[2], 0, 1 ) === '/' ) ? $match[2] : '/'.$match[2];
					return;
				}
				// Parse the string as a URL
				else {
					$segments = parse_url( trim( $string ) );
				}
			}
			// Scheme
			$server_scheme = false;
			if( isset( $segments['scheme'] ) ) {
				$this->scheme = $segments['scheme'];
			}
			else if( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ) {
				$server_scheme = true;
				$this->scheme = 'https';
			}
			else {
				$this->scheme = 'http';
			}
			// Host
			if( isset( $segments['host'] ) ) {
				$this->host = $segments['host'];
			}
			elseif( isset( $_SERVER['HTTP_HOST'] ) && preg_match( '/^(https?)$/i', $this->scheme ) ) {
				$this->host = $_SERVER['HTTP_HOST'];
				if( isset( $_SERVER['PATH_INFO'] ) ) {
					$pos = strrpos( $_SERVER['REQUEST_URI'], $_SERVER['PATH_INFO'] );
					$this->rootPath = substr( $_SERVER['REQUEST_URI'], 0, $pos );
				}
				else {
					$server_root = trim_slashes( $_SERVER['DOCUMENT_ROOT'] );
					$site_root = trim_slashes( document_root() );
					$this->rootPath = ( $site_root !== $server_root ) ? substr( $site_root, strlen( $server_root ) ) : null;
				}
			}
			// Port
			if( isset( $segments['port'] ) ) {
				$this->port = $segments['port'];
			}
			elseif( $server_scheme && isset( $_SERVER['SERVER_PORT'] ) ) {
				$this->port = $_SERVER['SERVER_PORT'];
			}
			// Basic Authentication
			if( isset( $segments['user'] ) ) {
				$this->user = $segments['user'];
			}
			elseif( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
				$this->user = $_SERVER['PHP_AUTH_USER'];
			}
			if( isset( $segments['pass'] ) ) {
				$this->password = $segments['pass'];
			}
			elseif( isset( $_SERVER['PHP_AUTH_PW'] ) ) {
				$this->password = $_SERVER['PHP_AUTH_PW'];
			}
			// Path
			if( isset( $segments['path'] ) ) {
				$segments['path'] = ( substr( $segments['path'], 0, 1 ) === '/' ) ? $segments['path'] : '/'.$segments['path'];
				$this->path = $segments['path'];
				// We only want the root path once
				if( $this->rootPath && $this->path->isSubpathOf( $this->rootPath ) ) {
					$this->rootPath = null;
				}
			}
			elseif( isset( $_SERVER['HTTP_HOST'] ) && $this->host == $_SERVER['HTTP_HOST'] && isset( $_SERVER['PATH_INFO'] ) ) {
				$this->path = new Path( $_SERVER['PATH_INFO'] );
			}
			elseif( isset( $_SERVER['HTTP_HOST'] ) && $this->host == $_SERVER['HTTP_HOST'] && isset( $_SERVER['REQUEST_URI'] ) ) {
				$pathinfo = $_SERVER['REQUEST_URI'];
				if( ( $pos = strpos( $pathinfo, '?' ) ) !== false ) {
					$pathinfo = substr( $pathinfo, 0, $pos );
				}
				$path = new Path( $pathinfo );
				if( $path->isSubpathOf( $this->rootPath ) ) {
					$path = $path->relativeTo( $this->rootPath );
					$path->root = null;
				}
				$this->path = $path;
			}
			elseif( $this->path === '/' ) {
				$this->path = null;
			}
			// Path
			if( isset( $segments['fragment'] ) ) {
				$this->fragment = $segments['fragment'];
			}
			// Query
			if( isset( $segments['query'] ) ) {
				parse_str( $segments['query'], $this->query );
			}
			elseif( $string === null && isset( $_SERVER['QUERY_STRING'] ) && strlen( $_SERVER['QUERY_STRING'] ) > 0 ) {
				parse_str( $_SERVER['QUERY_STRING'], $this->query );
			}
		}

	 /**
	  * String Value
	  *
	  * @return string
	  */

		public function asString() {
			return $this->absoluteString();
		}

	 /**
	  * Array Value
	  *
	  * @return array
	  */

		public function asArray() {
			return array(
				'scheme' => $this->scheme,
				'host' => $this->host,
				'port' => intVal( $this->port ),
				'user' => $this->user,
				'password' => $this->password,
				'path' => $this->path,
				'query' => $this->query,
				'fragment' => $this->fragment,
				'base' => $this->baseString,
				'rootPath' => $this->rootPath,
				'path' => $this->path,
			);
		}

//
// Accessing the Parts of the URL
//

	 /**
	  *
	  */

		public static function create( $string=null ) {
			return new self( $string );
		}

	 /**
	  *
	  */

		public static function current() {
			return self::create();
		}

	 /**
	  *
	  */

		public static function root() {
			return self::create( '/' );
		}

//
// Accessing the Parts of the URL
//

	 /**
	  *
	  */

		public function absoluteString() {
			// Compress the query string
			$query = null;
			if( !is_null( $this->query ) && count( $this->query ) > 0 ) {
				$query = '?'.http_build_query( $this->query );
			}
			// Compress the query string
			$fragment = null;
			if( !is_null( $this->fragment ) && strlen( $this->fragment ) > 0 ) {
				$fragment = '#'.$this->fragment;
			}
			// Path should either exist or not
			if( ( $path = $this->fullPath()->absoluteString() ) == '/' ) {
				$path = '';
			}
			// Build a string
			return $this->serverString().$path.$query.$fragment;
		}

	 /**
	  *
	  */

		public function baseString() {
			return $this->serverString().DIRECTORY_SEPARATOR.$this->rootPath;
		}

	 /**
	  *
	  */

		public function extendedPath() {
			$path = new Path( $this->host.DIRECTORY_SEPARATOR.$this->rootPath.DIRECTORY_SEPARATOR.$this->path );
			$path->root = null;
			return $path;
		}

	 /**
	  *
	  */

		public function fullPath() {
			$path = new Path( $this->rootPath.DIRECTORY_SEPARATOR.$this->path );
			$path->root = null;
			return $path;
		}

	 /**
	  *
	  */

		public function lastPathComponent() {
			return ( $this->path != null ) ? $this->path->lastComponent : null;
		}

	 /**
	  *
	  */

		public function pathComponents() {
			return ( $this->path != null ) ? $this->path->components : null;
		}

	 /**
	  *
	  */

		public function pathExtension() {
			return ( $this->path != null ) ? $this->path->extension : null;
		}

	 /**
	  *
	  */

		public function relativeString() {
			// Get the current URL
			$current = new Url();
			// If the two are Equal
			if( $this->isEqual( $current ) ) {
				$query = count( $this->query ) ? '?'.http_build_query( $this->query ) : null;
				return sprintf( './%@', $this->lastPathComponent().$query.$this->fragment );
			}
			// Scheme, authentication, host or port isn't the same
			if( $this->serverString() !== $current->serverString ) {
				return $this->absoluteString();
			}
			// Array to collect relative components
			$relativeComponents = array();
			// Path isn't the same
			if( ( $fullPath = $this->fullPath() ) !== $current->fullPath ) {
				$relativeComponents[] = $fullPath->relativeTo( $current->fullPath );
			}
			// Query isn't the same
			if( $this->query && ( $fullPath || $this->query !== $current->query ) ) {
				$relativeComponents[] = '?'.http_build_query( $this->query );
			}
			// Fragment isn't the same
			if( $this->fragment && ( $fullPath || $this->fragment !== $current->fragment ) ) {
				$relativeComponents[] = '#'.$this->fragment;
			}
			// And return
			return implode( null, $relativeComponents );
		}

	 /**
	  *
	  */

		public function resourceSpecifier() {
			return preg_replace('/^[\w]+:/i',null,$this->absoluteString());
		}

	 /**
	  *
	  */

		public function serverString() {
			// Host
			$host = null;
			if( $this->host ) {
				// User and password
				$user = null;
				if( $this->user ) {
					$password = null;
					if( $this->password )
						$password = ':'.$this->password;
					$user = $this->user.$password.'@';
				}
				// Port
				$port = null;
				if( $this->port && ( ( $this->scheme == 'http' && $this->port != 80 ) || ( $this->scheme == 'https' && $this->port != 443 ) ) ) {
					$port = ':'.$this->port;
				}
				// Combine the elements!
				$host = '//'.$user.$this->host.$port;
			}
			// Build a string
			return sprintf( '%s:%s', $this->scheme, $host );
		}

	 /**
	  * Find and strip matching XML/HTML tags from the provided content string.
	  * @param $path
	  * @param $relativeTo
	  * @return string The first path, relative to the second path.
	  */

		private static function _relativePath( $path, $relativeTo ) {
			// Clean the opening and closing slashes
			$path = trim( $path, "/ \t\n\r\0\x0B" );
			$relativeTo = trim( $relativeTo, "/ \t\n\r\0\x0B" );
			// Simple: $relativeTo is in $path
			if( strpos( $path, $relativeTo ) === 0 ) {
				$offset = strlen( $relativeTo ) + 1;
				return substr( $path, $offset );
			}
			// More difficult: $relativeTo is outside of $path
			$relative  = array();
			$pathParts = explode( '/', $path );
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
			return implode( '/', $relative );
		}

//
// Modifying the Path
//

	 /**
	  * Set the URL's root path, which points to the root of the site.
	  *
	  * @param \Framework\Core\Path|string $path The path you want to provide the URL.
	  * @return void
	  */

		public function setRootPath( $rootPath ) {
			// If we're given false, convert to null
			if( $rootPath === false ) {
				$rootPath = null;
			}
			// If we're given a Path object or null
			if( $rootPath instanceof Path || $rootPath === null ) {
				$this->_rootPath = $rootPath;
			}
			// If we're given a string
			else if( is_string( $rootPath ) ) {
				$this->_rootPath = path( $rootPath );
			}
			// If all else fails, throw an exception
			else {
				throw new \InvalidArgumentException;
			}
		}

	 /**
	  * Fetches the path that points to the root of the site.
	  *
	  * @return \Framework\Core\Path The root path.
	  */

		public function rootPath() {
			// We have null
			if( ! $this->_rootPath instanceof Path ) {
				return path( '/' );
			}
			// Return the actual stored path
			return $this->_rootPath;
		}

	 /**
	  * Set the URL's path, not including the root path.
	  *
	  * @param \Framework\Core\Path|string $path The path you want to provide the URL.
	  * @return void
	  */

		public function setPath( $path ) {
			// If we're given a Path object or null
			if( $path instanceof Path || $path === null ) {
				$this->_path = $path;
			}
			// If we're given a string
			else if( is_string( $path ) ) {
				$this->_path = path( $path );
			}
			// If all else fails, throw an exception
			else {
				throw new \InvalidArgumentException;
			}
		}

	 /**
	  * Fetches the path, not including the root path.
	  *
	  * @return \Framework\Core\Path The path.
	  */

		public function path() {
			// We have null
			if( ! $this->_path instanceof Path ) {
				return path( '/' );
			}
			// Return the actual stored path
			return $this->_path;
		}

//
// Modifying the Query String
//

	 /**
	  *
	  */

		public function withQueryValueForKey( $key, $value ) {
			$url = $this;
			$url->setQueryValueForKey( $key, $value );
			return $url;
		}

	 /**
	  *
	  */

		public function setQueryValueForKey( $key, $value ) {
			if( $value === null ) {
				$this->deleteQueryValueForKey( $key );
			}
			else {
				$this->query[$key] = $value;
			}
		}

	 /**
	  *
	  */

		public function queryValueForKey( $key ) {
			return isset( $this->query[$key] ) ? $this->query[$key] : null;
		}

	 /**
	  *
	  */

		public function deleteQueryValueForKey( $key ) {
			if( isset( $this->query[$key] ) ) {
				unset( $this->query[$key] );
			}
		}

	}
