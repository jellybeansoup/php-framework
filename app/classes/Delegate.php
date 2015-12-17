<?php
 /**
  * App library
  */

	namespace Framework\App;

 /**
  * Delegate class.
  *
  * Extensions of the `Framework\App\Delegate` class manage the activity of an
  * app by implementing a set of methods, and using controllers to generate
  * a `Framework\App\Response` object from the options provided when the
  * delegate is bootstrapped.
  *
  * Defining the `__construct` method allows the app to manipulate the options
  * once, as well as load any required libraries prior to use within the app's
  * controllers.
  *
  * @package Framework\App
  * @author Daniel Farrelly <daniel@jellystyle.com>
  * @copyright 2014 Daniel Farrelly <daniel@jellystyle.com>
  * @license FreeBSD
  */

	abstract class Delegate extends \Framework\Core\Object {

//
// Singleton
//

	 /**
	  * Collection of objects that extend `Framework\App\Delegate`.
	  *
	  * @var array
	  */

		private static $_applications = array();

	 /**
	  * Constructor method.
	  *
	  * The constructor is called when the delegate is bootstrapped. The
	  * options provided when bootstrapping are passed on for additional use.
	  *
	  * @param array $options Options provided when the delegate was bootstrapped.
	  * @return void
	  */

		abstract protected function __construct( $options );

	 /**
	  * Returns the stored instance of the delegate.
	  *
	  * @return void
	  */

		public static final function instance() {
			// Trying to call on the abstract class
			self::_throwExceptionIfAbstract();
			// If the class hasn't been instantiated yet, return null.
			$className = self::className();
			if( ! isset( self::$_applications[$className] ) ) {
				return null;
			}
			// Return the class instance
			return self::$_applications[$className];
		}

//
// Bootstrapping
//

	 /**
	  * The options array passed when bootstrapping the delegate.
	  *
	  * @var array
	  */

		private $_options = null;

	 /**
	  * Returns an array of default options for bootstrapping this app.
	  *
	  * @return array The default options array.
	  */

		protected static function defaultOptions() {
			return array(
				'url' => \Framework\Core\URL::create(),
			);
		}

	 /**
	  * Initiate the app with the given options.
	  *
	  * @param array $options Additional options for loading the app.
	  * @return Framework\App\Response The response from the app.
	  */

		public static final function bootstrap( $options=array() ) {
			// Trying to call on the abstract class
			self::_throwExceptionIfAbstract();
			// Get the class name
			$className = self::className();
			// Marge the provided options with the default options
			$options = array_merge( self::defaultOptions(), $options );
			// The first thing we do is instantiate the class, and store the options
			$object = new $className( $options );
			$object->_options = $options;
			// Store the object in the instances collection
			self::$_applications[$className] = $object;
			// Generate the response for the URL given in the options
			if( isset( $object->_options['url'] ) && $object->_options['url'] instanceof \Framework\Core\URL ) {
				try {
					return $object->responseForURL( $object->_options['url'] );
				}
				catch( \Exception $e ) {
					return $object->responseForException( $e );
				}
			}
		}

//
// Fetching content
//

	 /**
	  * The name of the default controller class.
	  *
	  * Defaults to `MainController`, unless overridden by the application's delegate.
	  *
	  * @var string
	  */

		protected $_defaultController = 'MainController';

	 /**
	  * Run filtering on the provided URL. This allows you to route a given URL to something
	  * different within the code, or to allow for varable segments in the URI.
	  *
	  * This method exists to be overridden by subclasses to provide an easy method for
	  * routing a given URL to a non-matching controller or method.
	  *
	  * The default implementation will route the home page (no path components) to
	  * `/MainController/index` and exceptions to `/MainController/exceptions`. All other
	  * `URL` objects are left as is.
	  *
	  * @param \Framework\Core\URL $url The `URL` to filter.
	  * @return \Framework\Core\URL The filtered `URL`.
	  */

		protected function filterURL( \Framework\Core\URL $url ) {
			// Home page default
			if( count( $url->path->components ) === 0 ) {
				$url->path = path( '/'.$this->_defaultController );
			}
			// Exception default
			else if( $url->scheme === 'exception' && count( $url->path->components ) === 1 ) {
				$url->path = path( '/'.$this->_defaultController.'/exception' );
			}
			// Return the URL
			return $url;
		}

	 /**
	  * Generate a response based on a given URL using a controller.
	  *
	  * The first two components of the URL's path are used in this implementation to determine
	  * the controller and the method, in that order. The first is matched to a controller's class
	  * (using the delegate's namespace) in a case insensitive manner. The second is passed to the
	  * controller's `canRoute` and `route` methods to first determine that the method exists and
	  * then to actually call the method.
	  *
	  * As such, this implementation requires that the URL has at least one component in the path,
	  * as the second component will default to 'index' if it's not a valid string.
	  *
	  * This implementation can be overriden by subclasses to provide an alternative method of
	  * routing the given `URL` to generate a `Response`.
	  *
	  * @uses \Framework\App\Delegate::filterURL() to reroute the given URL to a valid controller.
	  * @uses \Framework\App\Controller::canRoute() to determine if the required method name exists.
	  * @uses \Framework\App\Controller::route() to reroute the given URL to a valid controller.
	  *
	  * @throws \Framework\App\HTTPNotFoundException if the filtered URL can't be matched to a
	  *   controller or method.
	  *
	  * @param \Framework\Core\URL $url The URL to generate content for.
	  * @param array $attachments A collection of attachments to be passed to the controller's method.
	  *   Must be an indexed array (any string keys will be stripped).
	  * @return \Framework\App\Response The HTTP response generated for the given `URL`.
	  */

		public function responseForURL( \Framework\Core\URL $url, $attachments=array() ) {
			// Attachments must be an index array
			$attachments = array_values( $attachments );
			// Add the URL as an attachment
			array_unshift( $attachments, clone $url );
			// Filter the URL
			$filteredURL = $this->filterURL( clone $url );
			$filteredPath = $filteredURL->path->pathByDeletingExtension;
			// Find the controller for the first URI component
			if( ( $first = $filteredPath->componentAtIndex( 0 ) ) === null ) {
				throw new HTTPNotFoundException;
			}
			// Get the controllers
			$controllers = $this->controllers();
			// Loop through the components
			$className = null;
			$index = 0;
			foreach( $filteredPath->components as $component ) {
				// Build a class name
				$className = $className !== null ? $className.'\\'.$component : $component;
				// Find a metching controller
				$controller = null;
				foreach( $controllers as $name => $path ) {
					// If this round doesn't give us the right class, move on.
					if( strtolower( $name ) !== strtolower( $className ) ) {
						continue;
					}
					// Load the file while we're here
					require_once( $path );
					// Get the controller class name
					$className = self::classNamespace().'\\'.$name;
					// Load the controller instance if we can
					if( is_subclass_of( $className, 'Framework\\App\\Controller' ) ) {
						$controller = $className::instance();
					}
				}
				// Iterate the index
				$index++;
				// If we have our controller instance
				if( $controller !== null ) {
					break;
				}
			}
			// If we have no controller, throw an exception
			if( $controller === null ) {
				throw new HTTPNotFoundException;
			}
			// Find the method for the second URI component
			if( ( $method = $filteredPath->componentAtIndex( $index ) ) === null ) {
				$method = 'index'; // Default to index
			}
			// If we can't route the method, throw an exception
			if( ! $controller->canRoute( $method ) ) {
				throw new HTTPNotFoundException;
			}
			// Add any additional attachments
			$attachments = array_merge( $attachments, $this->_attachments );
			// Otherwise we're done here. BOOM.
			$reponse = $controller->route( $method, $url, $attachments );
			// And return
			return $reponse;
		}

	 /**
	  * Generate a response based on a given exception using a controller.
	  *
	  * A URL is generated based on the given exception, with the pattern exception:/{code},
	  * which the controller attempts to use to generate a response using `responseForURL`,
	  * passing the exception itself as an attachment.
	  *
	  * If this fails, a default response is generated with the details of the exception.
	  *
	  * In both cases, the code of the exception is used as the HTTP status code in the
	  * returned response, even if the code has been specifically set by the controller method
	  * found by `responseForURL`.
	  *
	  * This implementation can be overriden by subclasses to provide an alternative method of
	  * routing the given `URL` to generate a `Response`.
	  *
	  * @uses \Framework\App\Controller::responseForURL() to attempt to route a generated URL.
	  *
	  * @param \Exception $exception The exception to generate content for.
	  * @return \Framework\App\Response The HTTP response generated for the given `Exception`.
	  */

		protected function responseForException( \Exception $exception ) {
			// Try using a generated URL
			try {
				$url = url( 'exception:/'.$exception->getCode() );
				$response = $this->responseForURL( $url, array( $exception ) );
				$response->status = $exception->getCode();
			}
			// If routing the URL generates an exception, fall back to a default response.
			catch( \Exception $e ) {
				$response = new \Framework\App\Response;
				$response->status = $exception->getCode();
				$response->body = (string) $exception;
				$response->send();
			}
			// Return the response
			return $response;
		}

	 /**
	  * The name of the default controller class.
	  *
	  * Defaults to `MainController`, unless overridden by the application's delegate.
	  *
	  * @var string
	  */

		private $_attachments = array();

	 /**
	  * Attach a value to the response call.
	  *
	  * @param mixed $attachment The value to be sent to the responding method.
	  * @return void
	  */

		protected final function attach( $attachment ) {
			$this->_attachments[] = $attachment;
		}

//
// Library details
//

	 /**
	  * Throw an exception if the class being called the `Framework\App\Delegate`.
	  *
	  * @return void
	  */

		private final function library() {
			return \Library::forNamespace( self::classNamespace() );
		}

	 /**
	  * Throw an exception if the class being called the `Framework\App\Delegate`.
	  *
	  * @return void
	  */

		private final function controllers() {
			// Collections
			$folders = array( '/controllers' );
			$controllers = array();
			// Go through the folders we have to find all the files
			$library = $this->library();
			$library_path = path( $library->path );
			for( $i=0; $i<count($folders); $i++ ) {
				$pattern = $folders[$i].'/*';
				// Get all the children of the folder
				$children = $library->glob( $pattern );
				foreach( $children as $child ) {
					$child_path = path( $child );
					$relative_path = $child_path->relativeTo( $library_path );
					if( $child_path->isFolder() ) {
						$folders[] = $relative_path;
					}
					else if( $child_path->isFile() && $child_path->extension() === 'php' ) {
						$key = str_replace( '/', '\\', $relative_path->pathByDeletingComponent( 1 )->pathByDeletingExtension() );
						$controllers[$key] = $child;
					}
				}
			}
			// Return the controllers
			return $controllers;
		}

//
// Internal utilities
//

	 /**
	  * Throw an exception if the class being called the `Framework\App\Delegate`.
	  *
	  * @return void
	  */

		private static final function _throwExceptionIfAbstract() {
			if( self::className() === 'Framework\App\Delegate' ) {
				throw new \Exception( 'This method must be called on an extension of `Framework\App\Delegate`, and must not be called directly.' );
			}
		}

	}

 /**
  * HTTP Status exception.
  *
  * This exception can be thrown to cause a HTTP message page to be shown, and the
  * appropriate HTTP Status to be sent.
  *
  * @package Framework\App
  * @author Daniel Farrelly <daniel@jellystyle.com>
  * @copyright 2014 Daniel Farrelly <daniel@jellystyle.com>
  */

	class HTTPStatusException extends \Exception {

	 /**
	  * The reason given for the HTTP status exception.
	  *
	  * @var string
	  */

		protected $reason;

	 /**
	  * Exception constructor
	  *
	  * @param int $code A valid HTTP status code. Defaults to 500 (Internal Server Error)
	  *   if the value isn't an `int`, or can't be translated using `http_translate_code`.
	  * @return self
	  */

		public function __construct( $code=500, $reason=null ) {
			// If we can't translate the code, revert to 500.
			if( ! is_int( $code ) || ! http_translate_code( $code ) ) {
				$code = 500;
			}
			// Store the code and the translation
			$this->code = $code;
			$this->message = http_translate_code( $code );
			$this->reason = $reason;
		}

	 /**
	  * Public getter for accessing the given reason.
	  *
	  * @return string The reason given for the HTTP status exception.
	  */

		public function getReason() {
			return ! empty( $this->reason ) ? $this->reason : $this->getMessage();
		}

	}

 /**
  * HTTP Not Found exception.
  *
  * This status can be thrown to cause an exception screen to be shown with a 404 Not
  * Found message. This exception is simply thrown with no additional parameters.
  *
  * @package Framework\App
  * @author Daniel Farrelly <daniel@jellystyle.com>
  * @copyright 2014 Daniel Farrelly <daniel@jellystyle.com>
  */

	class HTTPNotFoundException extends HTTPStatusException {

	 /**
	  * Exception constructor
	  *
	  * @return self
	  */

		public function __construct() {
			parent::__construct( 404 );
		}

	}
