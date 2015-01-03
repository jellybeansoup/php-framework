<?php

	namespace Framework\Core;

 /**
  * Object base.
  *
  * This class provides a foundation of methods and properties useful to any class within the
  * JellyStyle Framework. It provides some default implementations of magic methods such as
  * `__get` and `__set`, and functionality for generating object identifiers and determining
  * object state.
  *
  * It is suggested that all classes that are designed to be instantiated as objects should
  * extend `Object`.
  *
  * @property string $identifier The object identifier. This is generated when the property is
  *	  accessed, or when the object is serialized. A custom value can also be provided.
  * @property-read string $hash Hash reflecting the object's current state. This is generated when
  *   the property is accessed, when a dynamic property's value is set, or when the object is
  *	serialized.
  *
  * @package Framework\Core
  * @author Daniel Farrelly <daniel@jellystyle.com>
  * @copyright 2014 Daniel Farrelly <daniel@jellystyle.com>
  * @license FreeBSD
  */

	abstract class Object {

//
// Dynamic properties
//

	 /**
	  * Array of properties that act as aliases of methods
	  *
	  * This property is designed to be implemented by subclasses of `Object`, providing details about
	  * dynamic properties such as getter and setter methods. These definitions can be a string name, or
	  * an array of options with the name as the element key:
	  *
	  * ```php
	  * $_dynamicProperties = array(
	  *		'property',
	  * 	'differentProperty' => array(
	  * 		'get' => 'getDifferentProperty',
	  * 		'set' => 'setDifferentProperty',
	  * 	),
	  * );
	  * ```
	  *
	  * The default value is an empty array.
	  *
	  * @var array
	  */

		protected static $_dynamicProperties = array();

	 /**
	  * Get the value of a property.
	  * This method expects that a method matching the name of the property exists, i.e. propertyName -> propertyName()
	  *
	  * @internal
	  * @return mixed The value of the requested property.
	  */

		public function __get( $propertyName ) {
			// If the property is public
			if( $this->propertyIsPublic( $propertyName ) ) {
				return $this->valueOfProperty( $propertyName );
			}
			// If the property and the method exist, call the method.
			else if( $methodName = $this->__getMethodName( $propertyName ) ) {
				return $this->{$methodName}();
			}
			// Default to false
			return null;
		}

	 /**
	  * Determine the name of the getter method for a dynamic property using the property's name.
	  *
	  * * If a method and defined property exist with the given name, the given name is used.
	  * * If a dynamic property has been defined in `Framework\Core\Object::$_dynamicProperties` with the given name and
	  * 	a method has been defined with the `get` key, use the value of this element. Otherwise, if the dynamic
	  *		property is defined, and a method exists with the same name, the defined name is used instead.
	  *
	  *	@internal
	  * @used-by Framework\Core\Object::__get()
	  * @param string $propertyName The name of the property to determine the get method name for.
	  * @return string|bool The name of the method for fetching the property value. False if no method can be determined.
	  */

		protected final function __getMethodName( $propertyName ) {
			// If the property and the method exist, call the method.
			if( $this->hasProperty( $propertyName ) && $this->hasMethod( $propertyName ) ) {
				return $propertyName;
			}
			// Fetch the current class
			$class = self::className();
			// If the property is dynamic, call the listed method.
			if( array_key_exists( $propertyName, $class::$_dynamicProperties ) ) {
				$propertyDetails = $class::$_dynamicProperties[$propertyName];
				if( array_key_exists( 'get', $propertyDetails ) && $this->hasMethod( $propertyDetails['get'] ) ) {
					return $propertyDetails['get'];
				}
				// Default to the property name
				else if( $this->hasMethod( $propertyName ) ) {
					return $propertyName;
				}
			}
			else if( in_array( $propertyName, $class::$_dynamicProperties ) && $this->hasMethod( $propertyName ) ) {
				return $propertyName;
			}
			// Default to false
			return false;
		}

	 /**
	  * Set the value of a property.
	  * This method expects that a method matching the name of the property exists, i.e. propertyName -> setPropertyName()
	  *
	  * @internal
	  * @return void
	  */

		public function __set( $propertyName, $value ) {
			// If the property is public
			if( $this->propertyIsMutable( $propertyName ) ) {
				$this->setValueOfProperty( $propertyName, $value );
			}
			// If the property and the method exist, call the method.
			else if( $methodName = $this->__setMethodName( $propertyName ) ) {
				$this->{$methodName}( $value );
			}
			// Refresh the hash
			$this->hash();
		}

	 /**
	  * Determine the name of the setter method for a dynamic property using the property's name.
	  *
	  * The first letter of the given name is automatically capitalized and the result prefixed with 'set' before
	  * performing any of the following logic, i.e. `example_property` would become `setExample_property`.
	  *
	  * * If a method and defined property exist with the prefixed name, the prefixed name is used.
	  * * If a dynamic property has been defined in `Framework\Core\Object::$_dynamicProperties` with the un-prefixed
	  * 	given name and a method has been defined with the `set` key, use the value of this element. Otherwise, if
	  * 	the dynamic property is defined, and a method exists with the prefixed name, the prefixed name is used
	  * 	instead.
	  *
	  *	@internal
	  * @used-by Framework\Core\Object::__set()
	  * @param string $propertyName The name of the property to determine the setter method name for.
	  * @return string|bool The name of the method for setting the property value. False if no method can be determined.
	  */

		protected final function __setMethodName( $propertyName ) {
			// Create the method name
			$methodName = sprintf( 'set%s', ucfirst( $propertyName ) );
			// If the property exists...
			if( $this->hasProperty( $propertyName ) && $this->hasMethod( $methodName ) ) {
				return $methodName;
			}
			// Fetch the current class
			$class = self::className();
			// If the property is dynamic, call the listed method.
			if( array_key_exists( $propertyName, $class::$_dynamicProperties ) ) {
				$propertyDetails = $class::$_dynamicProperties[$propertyName];
				if( array_key_exists( 'set', $propertyDetails ) && $this->hasMethod( $propertyDetails['set'] ) ) {
					return $propertyDetails['set'];
				}
				// Default to the property name
				else if( $this->hasMethod( $methodName ) ) {
					return $methodName;
				}
			}
			else if( in_array( $propertyName, $class::$_dynamicProperties ) && $this->hasMethod( $methodName ) ) {
				return $methodName;
			}
			// Default to false
			return false;
		}

//
// Object state
//

	 /**
	  * Storage for the object identifier.
	  *
	  * @internal
	  * @var string
	  */

		private $_identifier;

	 /**
	  * Fetch the identifier for the object.
	  *
	  * @return string An identifier hash for the object.
	  */

		protected function identifier() {
			// Set the identifier
			if( ! $this->_identifier ) {
				$this->_identifier = md5( sprintf( '%s::%s::%s::%s', serialize($_ENV), self::className(), time(), microtime() ) );
			}
			// Return the class
			return $this->_identifier;
		}

	 /**
	  * Fetch the identifier for the object.
	  *
	  * @return string An identifier hash for the object.
	  */

		protected function setIdentifier( $value ) {
			$this->_identifier = $value;
		}

	 /**
	  * Storage for the hash reflecting the object's current state.
	  *
	  * @internal
	  * @var string
	  */

		private $_hash;

	 /**
	  * Returns a hash representing the current state of the object.
	  *
	  * @return string A hash representing the current state of the object.
	  */

		protected function hash() {
			return $this->_hash = md5( json_encode( $this->__properties() ) );
		}

//
// Object's class
//

	 /**
	  * Fetch the class name for the current object.
	  *
	  * This method can be called from within subclass methods to determine the
	  * class name of the object it is called from.
	  *
	  * @return string The class name for the current object.
	  */

		protected final static function className() {
			return get_called_class();
		}

	 /**
	  * Fetch the namespace for the current object.
	  *
	  * This method can be called from within subclass methods to determine the
	  * namespace the object it is called from belongs to.
	  *
	  * @return string The namespace for the current object.
	  */

		protected final static function classNamespace() {
			$class = trim( self::className(), "\\ \t\n\r\0\x0B" );
			// Find the namespace
			if( $pos = strrpos( $class, '\\' ) ) {
				return substr( $class, 0, $pos );
			}
			// No namespace
			return null;
		}

//
// Object state
//

	 /**
	  * Fetches all non-dynamic properties for the current object.
	  * Excludes the `$hash` and `$identifier` properties.
	  *
	  * @return array The properties for the current object.
	  */

		public function __properties() {
			// Fill out the properties
			$this->identifier();
			// Fetch the object variables
			$properties = get_object_vars( $this );
			// Remove the cache and object context
			unset( $properties['_hash'], $properties['_identifier'] );
			// Return an array with object properties
			return $properties;
		}

	 /**
	  * Fetch the list of properties that act as aliases of methods.
	  *
	  * @return array The dynamic properties for the current object.
	  */

		public function __dynamicProperties() {
			$properties = array();
			// Fetch the dynamic properties
			$class = self::className();
			foreach( $class::$_dynamicProperties as $key ) {
				$properties[$key] = $this->__get($key);
			}
			// Return an array with object properties
			return $properties;
		}

	 /**
	  * Fetch the methods for the current object's class.
	  *
	  * @return array The properties for the current object's class.
	  */

		public final function __methods() {
			// Create an array for the output
			$methods = array();
			// Fetch the a reflection class
			$reflect = new \ReflectionClass( self::className() );
			// Iterate through the $methods and filter out private ones
			foreach( $reflect->getMethods() as $method ) {
				if( $method->isPublic() ) {
					$methods[] = $method->name;
				}
			}
			// Return the array
			return $methods;
		}

	 /**
	  * Flag indicating if the object has the named property (true) or not (false).
	  *
	  * @param $property string The name of the property.
	  * @return bool Flag indicating if the object has the named property.
	  */

		public function hasProperty( $property ) {
			// Fetch the a reflection class
			$reflect = $this->_reflection();
			// If the property is public
			return $reflect->hasProperty( $property );
		}

	 /**
	  * Flag indicating if the named property is mutable (true) or not (false).
	  *
	  * @param $property string The name of the property.
	  * @return bool Flag indicating if the property is mutable.
	  */

		public function propertyIsMutable( $property ) {
			// Fetch the a reflection class
			$reflect = $this->_reflection();
			// If the property doesn't exist
			if( ! $reflect->hasProperty( $property ) ) {
				return false;
			}
			// If the property is public
			return $reflect->getProperty( $property )->isPublic();
		}

	 /**
	  * Flag indicating if the named property is public (true) or not (false).
	  *
	  * @param $property string The name of the property.
	  * @return bool Flag indicating if the property is public.
	  */

		public function propertyIsPublic( $property ) {
			// Fetch the a reflection class
			$reflect = $this->_reflection();
			// If the property doesn't exist
			if( ! $reflect->hasProperty( $property ) ) {
				return false;
			}
			// If the property is public
			return $reflect->getProperty( $property )->isPublic();
		}

	 /**
	  * Fetch the value of a given property.
	  *
	  * @param $property string The name of the property.
	  * @return mixed The value for the property. Null if the property does not exist.
	  */

		public function valueOfProperty( $property ) {
			// If the property exists, set the value.
			if( $this->propertyIsPublic( $property ) ) {
				return $this->{$property};
			}
			// Default to null
			return null;
		}

	 /**
	  * Set the value of a given property.
	  *
	  * @param $property string The name of the property.
	  * @param $value mixed The value to give the property.
	  * @return void
	  */

		public function setValueOfProperty( $property, $value ) {
			// If the property exists, set the value.
			if( $this->propertyIsMutable( $property ) ) {
				$this->{$property} = $value;
			}
		}

	 /**
	  * Determine if the object has a method matching the given name (true) or not (false).
	  *
	  * @return bool Flag indicating if the object has a method matching the given name.
	  */

		public final function hasMethod( $method ) {
			return ( $this->_reflectionForMethod( $method ) !== null );
		}

	 /**
	  * Determine if the object has a public method matching the given name (true) or not (false).
	  *
	  * @return bool Flag indicating if the object has a public method matching the given name.
	  */

		public final function hasPublicMethod( $method ) {
			// Fetch the method reflection
			$reflect = $this->_reflectionForMethod( $method );
			// If the property is public
			if( $reflect === null ) {
				return false;
			}
			// If the method is public
			return $reflect->isPublic();
		}

	 /**
	  * Call the method with the given name if it exists.
	  *
	  * @return mixed The results of the method call.
	  */

		public final function callMethod( $method, $args=array() ) {
			// Fetch the method reflection
			$reflect = $this->_reflectionForMethod( $method );
			// If the method doesn't exist, or is not public
			if( $reflect === null || ! $reflect->isPublic() ) {
				return null;
			}
			// Call the method
			try {
				return $reflect->invokeArgs( $this, $args );
			}
			catch( \ReflectionException $e ) {
				return null;
			}
		}

	 /**
	  * Determine if the object is equal to the provided object.
	  *
	  * @return bool Flag indicating if the objects are equal.
	  */

		public function isEqual( Object $object ) {
			if( get_class( $this ) !== get_class( $object ) ) {
				return false;
			}
			return ( $this->hash() === $object->hash() );
		}

//
// Reflection
//

	 /**
	  * Determine if the object is equal to the provided object.
	  *
	  * @return bool Flag indicating if the objects are equal.
	  */

		protected final function _reflection() {
			try {
				return new \ReflectionObject( $this );
			}
			catch( \ReflectionException $e ) {
				return null;
			}
		}

	 /**
	  * Determine if the object is equal to the provided object.
	  *
	  * @return bool Flag indicating if the objects are equal.
	  */

		protected final function _reflectionForMethod( $method ) {
			try {
				return new \ReflectionMethod( $this, $method );
			}
			catch( \ReflectionException $e ) {
				return null;
			}
		}

//
// Object conversion
//

	 /**
	  * Return the string value of the object.
	  *
	  * @internal
	  * @return string The object as a string.
	  */

		public final function __toString() {
			return $this->asString();
		}

	 /**
	  * Convert the object to a string.
	  *
	  * @return string The serialized object.
	  */

		public function asString() {
			return serialize( $this );
		}

	 /**
	  * Convert the object to an array.
	  *
	  * @return array The properties of the object as an array.
	  */

		public function asArray() {
			return array_merge( $this->__properties(), $this->__dynamicProperties() );
		}

//
// Serializing objects
//

	 /**
	  * Fetch a list of opject properties for serialization.
	  *
	  * @internal
	  * @return array The keys for the elements to serialize.
	  */

		public final function __sleep() {
			return array_keys( $this->__properties() );
		}

	 /**
	  * Unserialize an object using an array with the properties.
	  *
	  * @internal
	  * @return self The unserialized instance of the object.
	  */

	    public static function __set_state( $array ) {
			$class = self::className();
			$instance = new $class;
			foreach( $array as $key => $value ) {
				$instance->setValueOfProperty( $key, $value );
			}
			return $instance;
	    }

	}
