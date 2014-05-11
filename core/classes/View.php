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
  * View Class
  */

  	class View extends Object {

//
// Properties
//

	 /**
	  *
	  * @var string
	  */

		private $_file = null;

	 /**
	  *
	  * @var string
	  */

		private $_data = array();

	 /**
	  *
	  * @var string
	  */

		private $_encode = false;

	 /**
	  * Dynamic properties
	  */

		protected static $_dynamicProperties = array(
			'file',
			'data',
			'encode',
			'body',
		);

//
// Loading
//

	 /**
	  *
	  * @return
	  */

	  	public function __construct( $file=null, $data=array(), $encode=false ) {
	  		$this->setFile( $file );
	  		$this->setData( $data );
	  		$this->setEncode( $encode );
	  	}

	 /**
	  *
	  * @return
	  */

	  	public static function create( $file=null, $data=array(), $encode=false ) {
	  		return new self( $file, $data, $encode );
	  	}

//
// Properties
//

	 /**
	  *
	  * @return
	  */

	  	public function file() {
	  		return $this->_file;
	  	}

	 /**
	  *
	  * @return
	  */

	  	public function setFile( $file ) {
	  		// Non-paths should be relative to the current app
	  		if( ! is_a( $file, '\\Framework\\Core\\Path' ) ) {
		  		$app = AppManager::currentApp();
		  		$file = Path::create( '/views/'.$file );
		  		$file->root = $app->path;
	  		}
	  		// Add an extension if necessary
	  		if( $file->exists() ) {
	 	 		$this->_file = $file;
	  		}
	  		else if( $file->pathByAddingExtension('php')->exists() ) {
	 	 		$this->_file = $file->pathByAddingExtension('php');
	  		}
	  		// File doesn't exist
	  		else {
		  		throw new \Exception( 'The view ('.$file.') doesn\'t exist.' );
	  		}
	  	}

	 /**
	  *
	  * @return
	  */

	  	public function data() {
	  		return $this->_data;
	  	}

	 /**
	  *
	  * @return
	  */

	  	public function setData( $data ) {
	  		// File doesn't exist
	  		if( count( $data ) && ! \is_assoc( $data ) ) {
		  		throw new \Exception( 'Data passed to a view must be in the form of an associative array.');
	  		}
	  		// Set the data
	  		$this->_data = $data;
	  	}

	 /**
	  *
	  * @return
	  */

	  	public function encode() {
	  		return $this->_encode;
	  	}

	 /**
	  *
	  * @return
	  */

	  	public function setEncode( $encode ) {
	  		$this->_encode = $encode;
	  	}

//
// Rendering
//

	 /**
	  * Render the output of the view and return
	  * @return
	  */

	  	public function asString() {
	  		return $this->render();
	  	}

	 /**
	  * Render the output of the view and return
	  * @return
	  */

	  	public function render() {
	  		// Extract the data variables
	  		extract( $this->_data, EXTR_OVERWRITE );
	  		// Require the file and buffer the output
	  		ob_start();
	  		require_once $this->_file->absolutePath;
			$bufferContents = ob_get_contents();
	  		ob_end_clean();
	  		// Return the content
	  		return $bufferContents;
	  	}

	 /**
	  * Render the output of the view as a file with the provided filename
	  * @return
	  */

	  	public function renderTo( $file ) {
	  	}

	}
