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

  	class URL extends Object {

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

		public $rootPath;

	 /**
	  * The path, not including the root path
	  * @var \Framework\Core\Path
	  */

		public $path;

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
			$this->scheme = isset( $segments['scheme'] ) ? $segments['scheme'] : 'http';
			// Host
			if( isset( $segments['host'] ) ) {
				$this->host = $segments['host'];
			}
			elseif( isset( $_SERVER['HTTP_HOST'] ) && preg_match( '/^(https?)$/i', $this->scheme ) ) {
				$this->host = $_SERVER['HTTP_HOST'];
				if( isset( $_SERVER['PATH_INFO'] ) ) {
					$pos = strrpos( $_SERVER['REQUEST_URI'], $_SERVER['PATH_INFO'] );
					$this->rootPath = new Path( substr( $_SERVER['REQUEST_URI'], 0, $pos ) );
				}
				else {
					$pos = strlen( $_SERVER['DOCUMENT_ROOT'] )-1;
					$document_root = document_root();
					$this->rootPath = new Path( substr( $document_root, $pos, strlen( $document_root )-$pos ) );
				}
			}
			// Port
			if( isset( $segments['port'] ) ) {
				$this->port = $segments['port'];
			}
			elseif( isset( $_SERVER['SERVER_PORT'] ) ) {
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
				$this->path = new Path( $segments['path'] );
				// We only want the root path once
				if( $this->rootPath && $this->path->isSubpathOf( $this->rootPath ) ) {
					$this->rootPath = null;
				}
			}
			elseif( $this->host == $_SERVER['HTTP_HOST'] && isset( $_SERVER['PATH_INFO'] ) ) {
				$this->path = new Path( $_SERVER['PATH_INFO'] );
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
			elseif( ! isset( $segments ) && $_SERVER['QUERY_STRING'] ) {
				parse_str( $_SERVER['QUERY_STRING'], $this->query );
			}
	  	}

	 /**
	  * String Value
	  */

		public function asString() {
			return self::absoluteString();
	  	}

	 /**
	  * Debug Value
	  */

		public function __toDebug() {
			return self::__toString();
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
			if( count( $this->query ) ) {
				$query = '?'.http_build_query( $this->query );
			}
			// Compress the query string
			$fragment = null;
			if( count( $this->fragment ) ) {
				$fragment = '#'.$this->fragment;
			}
			// Path should either exist or not
			if( ( $path = self::fullPath() ) == '/' )
				$path = '';
			// Build a string
			return self::serverString().$path.$query.$fragment;
	  	}

	 /**
	  *
	  */

		public function baseString() {
			return self::serverString().$this->rootPath;
	  	}

	 /**
	  *
	  */

		public function extendedPath() {
			return new Path( $this->host.$this->rootPath.$this->path );
	  	}

	 /**
	  *
	  */

		public function fullPath() {
			return new Path( $this->rootPath.$this->path );
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
				return sprintf( './%@', self::lastPathComponent().$this->query.$this->fragment );
			}
			// Scheme, authentication, host or port isn't the same
			if( self::serverString() !== $current->serverString ) {
				return self::absoluteString();
			}
			// Array to collect relative components
			$relativeComponents = array();
			// Path isn't the same
			if( ( $fullPath = self::fullPath() ) !== $current->fullPath ) {
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
			return preg_replace('/^[\w]+:/i',null,self::absoluteString());
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
				if( $this->port && $this->port != 80 ) {
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
// Modifying the Query String
//

	 /**
	  *
	  */

		public function setQueryValueForKey( $key, &$value ) {
			$self->query[$key] = $value;
	  	}

	 /**
	  *
	  */

		public function queryValueForKey( $key ) {
			return $self->query[$key];
	  	}

	 /**
	  *
	  */

		public function deleteQueryValueForKey( $key ) {
			unset( $self->query[$key] );
	  	}

  	}