<?php
 /**
  * App library
  */

	namespace Framework\App;

 /**
  * Representation of a HTTP response.
  *
  * This class is designed to allow you to build a HTTP response before sending it to the
  * client (i.e. the browser). You can do so by creating an instance and applying headers,
  * a valid HTTP 1.0 status code and a response body.
  *
  * The response can then be sent to the client in one call using `send`, or manually using
  * either `headers` or `sendHeaders` and the `$body` property.
  *
  * ```php
  * $response = new \Framework\App\Response;
  * $response->status = 200;
  * $response->setHeader( "X-Powered-By", "JellyStyle/1.0" );
  * $response->body = "This is the response's content.";
  * $response->send();
  * ```
  *
  * @property int $status The HTTP status code for this response. Defaults to 200 (OK).
  * @property-read array $headers The collection of HTTP headers for this response.
  *
  * @package Framework\App
  * @author Daniel Farrelly <daniel@jellystyle.com>
  * @copyright 2014 Daniel Farrelly <daniel@jellystyle.com>
  * @license FreeBSD
  */

	class Response extends \Framework\Core\Object {

	 /**
	  * Dynamic properties
	  *
	  * @see Framework\Core\Object::$_dynamicProperties
	  * @var array
	  */

		protected static $_dynamicProperties = array(
			'headers',
			'status',
			'body',
		);

//
// Magic Methods
//

	 /**
	  * Constructor
	  *
	  * @return self
	  */

		public final function __construct( $body=null, $code=200 ) {
			$this->_body = $body;
			$this->_status = $code;
		}

//
// HTTP status code
//

	 /**
	  * Storage for the HTTP status code.
	  *
	  * Defaults to 200 (OK).
	  *
	  * @internal
	  * @var int
	  */

		private $_status = 200;

	 /**
	  * Returns the object's HTTP status code.
	  *
	  * @return int The object's HTTP status code.
	  */

		public function status() {
			return $this->_status;
		}

	 /**
	  * Set the HTTP status code for the response.
	  *
	  * @throws InvalidArgumentException if the given `$code` is not a integer.
	  *
	  * @param int $code The desired HTTP status code for the response.
	  * @return void
	  */

		public function setStatus( $code ) {
			// Check the given parameters
			if( ! is_int( $code )  ) {
				throw new \InvalidArgumentException( 'Status is expected to be an integer.' );
			}
			// If the code cannot be translated, default to 500 (Internal Server Error).
			if( http_translate_code( $code ) === null ) {
				$code = 500;
			}
			// Store the code value
			$this->_status = $code;
		}

//
// HTTP headers
//

	 /**
	  * Storage for the HTTP headers.
	  *
	  * Defaults to an empty array.
	  *
	  * @internal
	  * @var array
	  */

		private $_headers = array();

	 /**
	  * Fetches an array of HTTP headers for this response.
	  *
	  * @return array The collection of the object's HTTP headers.
	  */

		public function headers() {
			// Get the stored header array
			$headers = $this->_headers;
			// Let's include the status header
			array_unshift( $headers, sprintf( "HTTP/1.0 %d %s", $this->_status, http_translate_code( $this->_status ) ) );
			// Return the complete array
			return $headers;
		}

	 /**
	  * Attempts to send the response's headers using the `header` function.
	  *
	  * @return void
	  */

		public function sendHeaders() {
			// Headers already sent
			if( headers_sent( $file, $line ) ) {
				return;
			}
			// Iterate through
			foreach( $this->headers as $key => $value ) {
				if( is_string( $key ) ) {
					header( $key.': '.$value );
				}
				else {
					header( $value );
				}
			}
		}

	 /**
	  * Set the value of a header matching the given key.
	  *
	  * @param string $key The key for the header you want to set the value of.
	  * @param string $value The value you want to give the header.
	  * @return void
	  */

		public function setHeader( $key, $value ) {
			// Check the given parameters
			if( ! is_string( $key ) || $key === null ) {
				throw new \InvalidArgumentException( 'Header key is expected to be a string, received `'.gettype($value).'` instead.' );
			}
			if( $value === null ) {
				throw new \InvalidArgumentException( 'Header value cannot be null.' );
			}
			if( ! ( is_scalar( $value ) || ( is_object( $value ) && method_exists( $value, '__toString' ) ) ) ) {
				throw new \InvalidArgumentException( 'Header value is expected to be a scalar value, received `'.gettype($value).'` instead.' );
			}
			// Store the header value
			$this->_headers[$key] = strval( $value );
		}

	 /**
	  * Fetches the value of a header matching the given key.
	  *
	  * @param string $key The key for the header you want the value of.
	  * @return string|null The value of the header matching the given key, or null if the header is not set.
	  */

		public function header( $key ) {
			// Check the given parameters
			if( ! is_string( $key ) || $key === null ) {
				throw new \InvalidArgumentException( 'Header key is expected to be a string.' );
			}
			// Return the value of the requested header if possible
			return isset( $this->_headers[$key] ) ? $this->_headers[$key] : null;
		}

	 /**
	  * Clears the header that matches the given key.
	  *
	  * @param string $key The key for the header you want to clear.
	  * @return void
	  */

		public function clearHeader( $key ) {
			// Check the given parameters
			if( ! is_string( $key ) || $key === null ) {
				throw new \InvalidArgumentException( 'Header key is expected to be a string.' );
			}
			// Clear the given header if possible
			if( isset( $this->_headers[$key] ) ) {
				unset( $this->_headers[$key] );
			}
		}

//
// Response body
//

	 /**
	  * Storage for the response's body
	  *
	  * This value does not have to be a string, but must be able to be converted to a string.
	  *
	  * @internal
	  * @var mixed
	  */

		private $_body;


	 /**
	  * Returns the response body in it's pure form.
	  *
	  * @return mixed The response body
	  */

		public function body() {
			return $this->_body;
		}

	 /**
	  * Set the body for the response.
	  *
	  * @throws InvalidArgumentException if the given `$body` is not a string.
	  *
	  * @param mixed $body The response body. Must either be a scalar value, or an object with the `__toString`
	  *   magic method defined.
	  * @return void
	  */

		public function setBody( $body ) {
			// Check the given parameters
			if( ! ( is_null( $body ) || is_scalar( $body ) || ( is_object( $body ) && method_exists( $body, '__toString' ) ) ) ) {
				throw new \InvalidArgumentException( 'The response body is expected to be a scalar value.' );
			}
			// Store the code value
			$this->_body = $body;
		}

//
// Sending
//

	 /**
	  * Send the response to the browser.
	  *
	  * Sending the response using this method will cause PHP to exit, and as such this method should be the last
	  * thing done within your code. The idea is that you generate a response from `index.php` and then use this
	  * method to complete your code.
	  *
	  * If you want to handle this an alternate way, use the `sendHeaders` functions and print the body, like so:
	  *
	  * ```php
	  * // Set the headers
	  * $response->applyHeaders();
	  * // Print the body as a string
	  * echo (string) $response->body;
	  * ```
	  *
	  * @return void
	  */

		public function send() {
			// Set the headers
			$this->sendHeaders();
			// Exit and print the body as a string
			exit( (string) $this->body );
		}

	}
