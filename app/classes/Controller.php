<?php
 /**
  * App library
  */

	namespace Framework\App;

 /**
  * Controller class.
  *
  * Provides an interface for routing incoming method requests and generating the
  * response content.
  *
  * @package Framework\App
  * @author Daniel Farrelly <daniel@jellystyle.com>
  * @copyright 2014 Daniel Farrelly <daniel@jellystyle.com>
  * @license FreeBSD
  */

	abstract class Controller extends \Framework\Core\Object {

	 /**
	  * Dynamic properties
	  *
	  * @see Framework\Core\Object::$_dynamicProperties
	  * @var array
	  */

		protected static $_dynamicProperties = array(
			'status',
		);

//
// Loading controllers
//

	 /**
	  * Controller instances
	  *
	  * @internal
	  * @var array
	  */

		private static $_instances = array();

	 /**
	  * Private constructor
	  *
	  * @return self
	  */

		private final function __construct() {
			$this->initialize();
		}

	 /**
	  * Fetch an instance of the calling controller class.
	  *
	  * If no instances have been created, this method will create and store one.
	  *
	  * @return self
	  */

		public static final function instance() {
			// Get the class
			$class = self::className();
			// Return a new instance instance
			return new $class;
		}

	 /**
	  * Called when an instance of the class is initialized, to allow properties
	  * to be initialized as required.
	  *
	  * @return void
	  */

		public function initialize() { }

//
// Handling the response
//

	 /**
	  * Storage for the controller's response.
	  *
	  * @internal
	  * @var Framework\App\Response
	  */

		private $_response = null;

	 /**
	  * Storage for the controller's response.
	  *
	  * @var Framework\App\Response
	  */

		public final function response() {
			// Check for an existing instance
			if( $this->_response === null ) {
				$this->_response = new Response;
			}
			// Return the instance
			return $this->_response;
		}

	 /**
	  * Set the HTTP status on the response.
	  *
	  * Acts as an alias for `$this->response()->setStatus()`.
	  *
	  * @see Framework\App\Response::setStatus()
	  * @param int $code The desired HTTP status code for the response.
	  * @return void
	  */

		public final function setStatus( $code ) {
			$this->response()->setStatus( $code );
		}

	 /**
	  * Set a HTTP header on the response.
	  *
	  * Acts as an alias for `$this->response()->setHeader()`.
	  *
	  * @see Framework\App\Response::setHeader()
	  * @param string $key The key for the header you want to set the value of.
	  * @param string $value The value you want to give the header.
	  * @return void
	  */

		public final function setHeader( $key, $value ) {
			$this->response()->setHeader( $key, $value );
		}

	 /**
	  * Clear a HTTP header on the response.
	  *
	  * Acts as an alias for `$this->response()->clearHeader()`.
	  *
	  * @see Framework\App\Response::clearHeader()
	  * @param string $key The key for the header you want to clear.
	  * @return void
	  */

		public final function clearHeader( $key ) {
			$this->response()->clearHeader( $key );
		}

	 /**
	  * Shorthand function for redirecting to a given URL.
	  *
	  * Acts as an alias for `$this->response()->setHeader( 'Location', $location )`, and causes the headers to be sent.
	  *
	  * @see Framework\App\Response::setHeader()
	  * @param \Framework\Core\URL $location The URL you want to redirect to.
	  * @return void
	  */

		public final function redirect( \Framework\Core\URL $location ) {
			$this->response()->setHeader( 'Location', (string) $location );
			$this->response()->body = null;
			$this->response()->send();
		}

//
// Routing the method call
//

	 /**
	  * Allowed method prefixes.
	  *
	  * Defaults to the current request method (usually `GET`, `POST`, `PUT` or `DELETE`) and `action`.
	  *
	  * @internal
	  * @var string
	  */

		protected function _methodPrefixes() {
			return array( Request::server('REQUEST_METHOD','cli'), 'action' );
		}

	 /**
	  * Route a given method name, with provided attachments.
	  *
	  * The given method name is prefixed with either the request method or 'action' before calling. The
	  * controller will first try prefixing with the request method (GET, POST, etc.), and will fall back
	  * to prefixing with 'action' if no method is found.
	  *
	  * So if the given method name is `example_method`, and the HTTP request method is DELETE, the
	  * controller will first attempt to use `deleteExample_method` and then `actionExample_method`
	  * respectively.
	  *
	  * @param string $methodName The method name you want to call.
	  * @param \Framework\Core\URL $url The URL that's being routed.
	  * @param array $attachments A collection of attachments you want to send to the method as arguments.
	  * @return Framework\App\Response|null The response object generated with the method's return value
	  *   as the body, or null if the routing fails.
	  */

		public function route( $methodName, \Framework\Core\URL $url, $attachments ) {
			// Gather our prefixes
			foreach( $this->_methodPrefixes() as $prefix ) {
				// Prefix the given name
				$prefixedMethodName = strtolower( $prefix ).ucfirst( $methodName );
				// If a matching public method exists, make the call.
				if( $this->hasPublicMethod( $prefixedMethodName ) ) {
					// Indicate that we will route the call
					if( $this->hasMethod( 'willHandleURL' )  ) {
						$this->callMethod( 'willHandleURL', array( $url, $prefixedMethodName ) );
					}
					// The response body is the return value of the called method.
					$body = $this->callMethod( $prefixedMethodName, $attachments );
					// Grab a copy of the response object
					$response = $this->response();
					// We run it through a body for additional formatting.
					$response->body = $this->formatBody( $body, $attachments );
					// Clear the stored response object.
					unset( $this->_response );
					// Indicate that we have handled the call
					if( $this->hasMethod( 'didHandleURL' )  ) {
						$this->callMethod( 'didHandleURL', array( $url, $prefixedMethodName, $response ) );
					}
					// Return the response object.
					return $response;
				}
			}
			// We failed
			return null;
		}

	 /**
	  * Hook for pre-formatting the response body.
	  *
	  * This allows subclasses of `Framework\App\Controller` to handle the response format without having
	  * to override the entire routing mechanism.
	  *
	  * @param mixed $body The body to be formatted.
	  * @param array $attachments A collection of attachments you want to send to the method as arguments.
	  * @return string The formatted response body.
	  */

		public function formatBody( $body, $attachments ) {
			return strval( $body );
		}

	 /**
	  * Determine whether a given method name can be routed successfully.
	  *
	  * The given method name is prefixed with either the request method or 'action' before testing. The
	  * controller will first try prefixing with the request method (GET, POST, etc.), and will fall back
	  * to prefixing with 'action' if no method is found.
	  *
	  * So if the given method name is `example_method`, and the HTTP request method is DELETE, the
	  * controller will first attempt to use `deleteExample_method` and then `actionExample_method`
	  * respectively.
	  *
	  * @param string $methodName The method name you want to call.
	  * @return bool A flag indicating if the given method can be routed (true) or not (false).
	  */

		public function canRoute( $methodName ) {
			// Gather our prefixes
			foreach( $this->_methodPrefixes() as $prefix ) {
				// Prefix the given name
				$prefixedMethodName = strtolower( $prefix ).ucfirst( $methodName );
				// If a matching public method exists, return true.
				if( $this->hasPublicMethod( $prefixedMethodName ) ) {
					return true;
				}
			}
			// No matching methods
			return false;
		}

	}
