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
  * Scanner
  * Can be used for scanning and parsing strings quickly.
  */

  	class Scanner extends Object {

//
// Properties
//

	 /**
	  * The string to be scanned.
	  * @var string
	  */

	  	public $string;

	 /**
	  * The position of the scan.
	  * @var int
	  */

	  	public $scanLocation = 0;

	 /**
	  * Whether the scan distinguishes case in the characters it scans.
	  * @var boolean
	  */

	  	public $caseSensitive = false;

	 /**
	  * A selection of characters to skip when scanning
	  * @var string
	  */

	  	public $charactersToBeSkipped = ' \t\n\r\0\x0B';

//
// Magic methods
//

	 /**
	  * Constructor
	  */

	  	public function __construct( $string ) {
	  		// We only scan strings
		  	if( ! is_string( $string ) )
				throw new \InvalidArgumentException;
			// Store the string
			$this->string = $string;

	  	}

//
// Scanning utilities
//

	 /**
	  * Allows for fast enumeration through the characters in the scanner's string.
	  * @returns string The
	  */

	  	private function nextCharacter() {
	  		// If we're at the end of the string
	  		if( $this->isAtEnd() )
	  			return false;
	  		// Get the character at the current location
	  		$character = substr($this->string,$this->scanLocation,1);
	  		// Iterate the scanLocation
	  		$this->scanLocation++;
	  		// And return the character we fetched
	  		return $character;
	  	}

	 /**
	  * Allows for fast enumeration through the characters in the scanner's string.
	  * @returns string The
	  */

	  	private function character() {
		  	return $this->substringWithLength( 1 );
	  	}

	 /**
	  * Allows for fast enumeration through the characters in the scanner's string.
	  * @returns string The
	  */

	  	private function substringWithLength( $length ) {
		  	// Move back a character
		  	$scanLocation = ( $this->scanLocation <= 0 ) ? $this->scanLocation : $this->scanLocation - 1;
		  	// Return a substring
		  	return substr($this->string,$scanLocation,$length);
	  	}

	 /**
	  * Allows for fast enumeration through the characters in the scanner's string.
	  * @returns string The
	  */

	  	private function skipCharacters( $characters ) {
			// If we have no characters to skip
			if( ! $characters && strlen( $characters ) === 0 )
				return;
			// Skip until we match a character that doesn't match
			while( $character = $this->character() ) {
				// If the character is in our list of skippable characters
				if( strpos( $characters, $character ) === false )
					break;
		  		// Iterate the scanLocation
		  		$this->scanLocation++;
			}
	  	}

	 /**
	  * Allows for fast enumeration through the characters in the scanner's string.
	  * @returns string The
	  */

	  	private function stringsMatch( $str1, $str2 ) {
		  	// Compare the strings
		  	if( $this->caseSensitive ) {
			  	return ( strcmp( $str1, $str2 ) == 0 );
		  	}
		  	// Compare the strings, without comparing case
		  	else {
			  	return ( strcasecmp( $str1, $str2 ) == 0 );
		  	}
	  	}

//
// Scanning a string
//

	 /**
	  *
	  */

	  	public function scanCharactersIntoString( $characters, &$stringValue ) {
	  		// We only scan strings
		  	if( ! is_string( $characters ) )
				throw new \InvalidArgumentException;
			// Skip the preset characters
			$this->skipCharacters( $this->charactersToBeSkipped );
			// Capture the provided characters
			$capturedString = null;
			while( $character = $this->nextCharacter() ) {
				if( strpos( $characters, $character ) === false )
					break;
				$capturedString .= $character;
			}
			// Pass the captured string back
			$stringValue = $capturedString;
	  		// Iterate the scanLocation
	  		if( ! $this->isAtEnd() && strlen( $capturedString ) > 0 )
	  			$this->scanLocation++;
			// And let them know if we captured any characters
			return (bool) strlen( $capturedString );
	  	}

	 /**
	  *
	  */

	  	public function scanUpToCharactersIntoString( $stopCharacters, &$stringValue ) {
	  		// We only scan strings
		  	if( ! is_string( $stopCharacters ) )
				throw new \InvalidArgumentException;
			// Skip the preset characters
			$this->skipCharacters( $this->charactersToBeSkipped );
			// Capture the provided characters
			$capturedString = null;
			while( $character = $this->nextCharacter() ) {
				if( strpos( $stopCharacters, $character ) !== false )
					break;
				$capturedString .= $character;
			}
			// Pass the captured string back
			$stringValue = $capturedString;
	  		// Iterate the scanLocation
	  		if( ! $this->isAtEnd() && strlen( $capturedString ) > 0 )
	  			$this->scanLocation++;
			// And let them know if we captured any characters
			return (bool) strlen( $capturedString );
	  	}

	 /**
	  *
	  */

	  	public function scanStringIntoString( $string, &$stringValue ) {
	  		// We only scan strings
		  	if( ! is_string( $string ) )
				throw new \InvalidArgumentException;
			// Skip the preset characters
			$this->skipCharacters( $this->charactersToBeSkipped );
			// Capture the provided characters
			$capturedString = $this->substringWithLength( strlen( $string ) );
			// The strings don't match
			if( ! $this->stringsMatch( $capturedString, $string ) )
				return false;
			// Pass the captured string back
			$stringValue = $capturedString;
	  		// Iterate the scanLocation
	  		if( ! $this->isAtEnd() && strlen( $capturedString ) > 0 )
	  			$this->scanLocation = $this->scanLocation + ( strlen( $string ) - 1 );
			// And let them know if we captured any characters
			return (bool) strlen( $capturedString );
	  	}

	 /**
	  *
	  */

	  	public function scanUpToStringIntoString( $stopString, &$stringValue ) {
	  		// We only scan strings
		  	if( ! is_string( $stopString ) )
				throw new \InvalidArgumentException;
			// Get the first character of the given string
			$length = strlen( $stopString );
			$firstCharacter = substr( $stopString, 0, 1 );
			// Skip the preset characters
			$this->skipCharacters( $this->charactersToBeSkipped );
			// Capture the provided characters
			$capturedString = null;
			while( $character = $this->nextCharacter() ) {
				if( $this->stringsMatch( $character, $firstCharacter ) && $this->stringsMatch( $this->substringWithLength( $length ), $stopString ) )
					break;
				$capturedString .= $character;
			}
			// Pass the captured string back
			$stringValue = $capturedString;
	  		// Iterate the scanLocation
	  		if( ! $this->isAtEnd() && strlen( $capturedString ) > 0 )
	  			$this->scanLocation++;
			// And let them know if we captured any characters
			return (bool) strlen( $capturedString );
	  	}

	 /**
	  * Determine whether we've reached the end of the string.
	  * @return bool Flag indicating if we've reached the end of the string
	  */

	  	public function isAtEnd() {
	  		return ( $this->scanLocation == strlen( $this->string ) );
	  	}

  	}
