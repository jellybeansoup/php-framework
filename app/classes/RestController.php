<?php
 /**
  * App library
  */

	namespace Framework\App;

 /**
  * RESTful Controller class.
  *
  * Provides an interface for routing incoming method requests and generating the
  * response content, designed for building RESTful API endpoints.
  *
  * @package Framework\App
  * @author Daniel Farrelly <daniel@jellystyle.com>
  * @copyright 2014 Daniel Farrelly <daniel@jellystyle.com>
  * @license FreeBSD
  */

  	abstract class RestController extends \Framework\App\Controller {

	 /**
	  * Dynamic properties
	  *
	  * @see Framework\Core\Object::$_dynamicProperties
	  * @var array
	  */

		protected static $_dynamicProperties = array(
			'status',
			'format',
		);

//
// Response format
//

	 /**
	  * The format of the response.
	  *
	  * @internal
	  * @var string
	  */

	  	private $_format = 'json';

	 /**
	  * Fetch the format to controller will use for the response body.
	  *
	  * @return string
	  */

	  	public final function format() {
		  	return $this->_format;
	  	}

//
// Routing the method call
//

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
	  		// We can usually count on the URL being the first attachment
	  		$url = reset( $attachments );
			// We provide rendered responses for HTTP requests
			if( in_array( $url->scheme, array( 'http', 'https', 'exception' ) ) ) {
				// Get the content format
				$format = $url->path->extension ? $url->path->extension : $this->format;
				// JSON response
				if( $format === 'json' ) {
					$this->setHeader( 'Content-type', 'application/json' );
					return self::json_encode( self::primitiveOf( $body ) );
				}
				// XML Response
				else if( $format === 'xml' ) {
					$this->setHeader( 'Content-type', 'application/xml' );
					return self::xml_encode( self::primitiveOf( $body ), $url->path->filename );
				}
				// CSV Response
				else if( $format === 'csv' ) {
					$this->setHeader( 'Content-type', 'text/csv' );
					//$this->setHeader( 'Content-type', 'text/plain' );
					return self::csv_encode( self::primitiveOf( $body ) );
				}
				// PHP response
				else if( $format === 'php' ) {
					$this->setHeader( 'Content-type', 'application/x-httpd-php' );
					return '<?php return unserialize("'.serialize( $body ).'"); ?>';
				}
			}
			// For other requests, we provide the raw data
			return $body;
	  	}

		private function primitiveOf( $data ) {
			// If it's an object
			if( is_object( $data ) ) {
				$data = method_exists( $data, 'asArray' ) ? $data->asArray() : (array) $data;
			}
			// If it's not an array, we convert
			if( is_array( $data ) || is_object( $data ) ) {
				foreach( $data as $key => $value ) {
					$data[$key] = self::primitiveOf( $value );
				}
			}
			// Return the converted values
			return $data;
		}

//
// Encoding
//

	 /**
	  * Format the response data as a JSON document
	  *
	  * @param mixed $data The data to be formatted.
	  * @return string The formatted data.
	  */

		private function json_encode( $data ) {
			return json_encode( $data );
		}

	 /**
	  * Format the response data as an XML document
	  *
	  * @param mixed $data The data to be formatted.
	  * @return string The formatted data.
	  */

		private function xml_encode( $data, $rootNodeName='data', $xml=null ) {
			// turn off compatibility mode as simple xml throws a wobbly if you don't.
			if( ini_get('zend.ze1_compatibility_mode' ) == 1) {
				ini_set ('zend.ze1_compatibility_mode', 0);
			}
			$data_keys = array_keys( $data );
			if( count( $data ) == 1 && is_string( $data_keys[0] ) ) {
				$rootNodeName = $data_keys[0];
				$data = $data[$rootNodeName];
			}
			if( $xml == null ) {
				$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
			}
			// loop through the data passed in.
			foreach( $data as $key => $value ) {
				// no numeric keys in our xml please!
				if( is_numeric( $key ) ) {
					// make string key...
					$key = singular_of( $rootNodeName );
				}
				// replace anything not alpha numeric
				$key = preg_replace('/[^a-z]/i', '', $key);
				// if there is another array found recrusively call this function
				if( is_array( $value ) ) {
					$node = $xml->addChild($key);
					// recrusive call.
					self::xml_encode( $value, $rootNodeName, $node );
				}
				else {
					$value = htmlentities( (string) $value );
					$xml->addChild($key,$value);
				}
			}
			// pass back as string. or simple xml object if you want!
			return $xml->asXML();
		}

	 /**
	  * Format the response data as a CSV document
	  *
	  * @param mixed $data The data to be formatted.
	  * @return string The formatted data.
	  */

		private function csv_encode( $data ) {
			// If we have an associative object/array, put it in an array.
			if( \is_assoc( $data ) || is_object( $data ) ) {
				$data = array( $data );
			}
			// If the value is not an array
			else if( ! is_array( $data ) ) {
				return strval( $data );
			}
			// Iterate through all the rows
			$csv = array();
			$keys = array();
			foreach( $data as $i => $row ) {
				// Iterate through the row's fields
			    $output = array();
			    foreach( $row as $key => $field ) {
					// Null
			        if( $field === null ) {
			            $output[] = 'null';
			        }
					// Not a scalar value
			        else if( ! is_scalar( $field ) ) {
			        	continue;
			        }
			        // Enclose fields containing ;, " or whitespace
			        else if( preg_match( "/(?:,|\"|\s)/", $field ) ) {
			            $output[] = '"'.str_replace( '"', '""', $field ).'"';
			        }
					// Default
					else {
			            $output[] = strval( $field );
					}
					// Add the key
					if( $i == 0 ) {
						$keys[] = is_string( $key ) ? $key : '';
					}
			    }
			    // Add to the CSV array
			    $csv[] = implode( ',', $output );
			}
			// Add the keys
			array_unshift( $csv, implode( ',', $keys ) );
			// Return the formatted string
			return implode( "\r\n", $csv );
		}

  	}
