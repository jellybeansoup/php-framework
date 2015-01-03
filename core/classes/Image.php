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

	use \Framework\Core\Path;

 /**
  * Class Manager
  */

	class Image extends Object {

	 /**
	  * Dynamic properties
	  *
	  * @see Framework\Core\Object::$_dynamicProperties
	  * @var array
	  */

		protected static $_dynamicProperties = array(
			'mime',
			'size',
		);

//
// Properties
//

	 /**
	  * The path to the original image file.
	  *
	  * @var \Framework\Core\Path
	  */

		private $_path = NULL;

	 /**
	  * The current image width.
	  *
	  * @var int
	  */

		private $_width = 0;

	 /**
	  * The current image height.
	  *
	  * @var int
	  */

		private $_height = 0;

	 /**
	  * The image's mime type, e.g. image/png.
	  *
	  * @var string
	  */

		private $_mime = null;

	 /**
	  * The image resource link for the current canvas.
	  *
	  * @var resource
	  */

		private $_resource = NULL;

	 /**
	  * Create a new instance of JS_Image, referencing a given image file.
	  *
	  * ``` php
	  * $path = new Path( '' );
	  * $image = new Image( '/path/to/image.png' );
	  * ```
	  *
	  * @param \Framework\Core\Path $path Server-side path to the image file.
	  * @return self
	  */

		public function __construct( Path $path ) {
			// Increase the memory allocation for this page
			// Mostly so we can deal with super large images
			ini_set('memory_limit', '128M');
			// Store the path
			$this->_path = $path;
			// Reset the image
			self::reset();
		}

	 /**
	  * Return the mime type for the current image.
	  *
	  * @return bool Flag indicating whether the save was successful.
	  */

		public function mime() {
			return $this->_mime;
		}

	 /**
	  * Fetch an object with the height and with of the current image.
	  *
	  * @return bool Flag indicating whether the save was successful.
	  */

		public function size() {
			return (object) array( 'width' => $this->_width, 'height' => $this->_height );
		}

//
// IMAGE OUTPUT
// These methods do some extra calulations to make common tasks easier.
//

	 /**
	  * Return the content of the manipulated image.
	  *
	  * ``` php
	  * $image = new JS_Image( '/path/to/image.png' );
	  * echo (string) $image;
	  * ```
	  *
	  * @return string The content of the manipulated image.
	  */

		public function asString() {
			// Turn on output buffering
			ob_start();
			// PNG source
			self::save( null );
			// Put the contents of the output buffer into the content variable
			$content = trim( ob_get_contents() );
			// Clean (erase) the output buffer and turn off output buffering
			ob_end_clean();
			// Output
		 	return (string) $content;
		}

	 /**
	  * Save the manipulated image.
	  *
	  * @param string $path The path to save the image file to. The path must be writable to successfully save the file.
	  *	You can also optionally pass a null value to output the content directly to the browser.
	  * @param bool $interlace Flag indicating whether to save as an interlaced image(true) or not (false). Defaults to false.
	  *	If true, PNG and GIF images are saved as interlaced images, while JPEGs are saved as progressive.
	  * @return bool Flag indicating whether the save was successful (true) or not (false).
	  *	If a path was specified, the path to the new file is returned in place of the true value.
	  */

		public function save( $path, $interlace=false ) {
			// If the path doesn't have any slashes...
			if( is_string( $path ) ) {
				$path = $this->_path->pathByDeletingLastComponent->pathByAddingComponent( $path )->pathByAddingExtension( $this->_path->extension );
			}
			// Save as an interlaced GIF/PNG or a progressive JPEG
			imageinterlace( $this->_resource, $interlace ? 1 : 0 );
			// PNG source
			if( $this->_mime == 'image/png' )
				$status = imagepng( $this->_resource, $path );
			// GIF source
			if( $this->_mime == 'image/gif' )
				$status = imagegif( $this->_resource, $path );
			// JPEG source
			if( $this->_mime == 'image/jpeg' || $this->_mime == 'image/jpg' )
				$status = imagejpeg( $this->_resource, (string) $path, 100 );
			// If a path is set and the save was successful
			if( $path && $status )
				return $path;
			// Default to returning the status
			return $status;
		}

 //
 // PRIMATIVE METHODS
 // Basic methods to do basic things.
 //

	 /**
	  * Adjust the size of the image and canvas.
	  *
	  * @param int $width The new width for the image.
	  * @param int $height The new height for the image.
	  * @return JS_Image The object for chaining.
	  */

		public function resize( $width, $height ) {
			// Create a blank canvas in the size we want
			$canvas = self::_create_canvas( $width, $height );
			// Copy the image to our thumbnail
			imagecopyresampled( $canvas, $this->_resource, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height );
			// Destroy the original canvas
			self::destroy();
			// Update the object parameters
			$this->_resource = $canvas;
			$this->_width = $width;
			$this->_height = $height;
			// Return the object for chaining
			return $this;
		}

	 /**
	  * Adjust the size of the canvas.
	  *
	  * @param int $width The new width for the canvas.
	  * @param int $height  The new height for the canvas.
	  * @param int $x The horizontal offset (from the left) for the image within the canvas. Defaults to 0.
	  * @param int $y The vertical offset (from the top) for the image within the canvas. Defaults to 0.
	  * @return JS_Image The object for chaining.
	  */

		public function crop( $width, $height, $x=0, $y=0 ) {
			// Create a blank canvas in the size we want
			$canvas = self::_create_canvas( $width, $height );
			// Copy the image to our thumbnail
			imagecopyresampled( $canvas, $this->_resource, 0-$x, 0-$y, 0, 0, $this->_width, $this->_height, $this->_width, $this->_height );
			// Destroy the original canvas
			self::destroy();
			// Update the object parameters
			$this->_resource = $canvas;
			$this->_width = $width;
			$this->_height = $height;
			// Return the object for chaining
			return $this;
		}

	 /**
	  * Rotate the image.
	  *
	  * @param int $width The new width for the canvas.
	  * @param int $height  The new height for the canvas.
	  * @param int $x The horizontal offset (from the left) for the image within the canvas. Defaults to 0.
	  * @param int $y The vertical offset (from the top) for the image within the canvas. Defaults to 0.
	  * @return JS_Image The object for chaining.
	  */

		public function rotate( $angle ) {
			// Copy the image to our thumbnail
			$rotated = imagerotate( $this->_resource, $angle, -1 );
			// Turn off alpha blending
			imagesavealpha( $rotated, true );
			// Update the object parameters
			$this->_resource = $rotated;
			$this->_width = imagesx( $this->_resource );
			$this->_height = imagesy( $this->_resource );
			// Return the object for chaining
			return $this;
		}

	 /**
	  * Insert the given image into the current image.
	  *
	  * @param mixed $path The path to the image file to overlay. Another JS_Image object can also be used.
	  * @param int $x The horizontal offset (from the left) for the overlaid image. Defaults to 0.
	  * @param int $y The vertical offset (from the top) for the overlaid image. Defaults to 0.
	  * @param string $type The layer effect to use in overlaying the image, can be replace, normal or overlay. Defaults to 'normal'.
	  * @return JS_Image The object for chaining.
	  */

		public function overlay( $path, $x=0, $y=0, $type='normal' ) {
			// Validate parameters
			if( ( ! $path instanceof Path || ! $path->isFile() ) && ! $path instanceof Image ) {
				throw new \InvalidArgumentException;
			}
			// Layer effect types
			$types = array( 'replace' => IMG_EFFECT_REPLACE, 'normal' => IMG_EFFECT_NORMAL, 'overlay' => IMG_EFFECT_OVERLAY );
			// Get the source image as a resource
			$overlay = self::_fetch_resource( $path );
			// Change the layer effect
			imagelayereffect( $this->_resource, $types[$type] );
			// Copy the overlay into the current canvas
			imagecopy( $this->_resource, $overlay, $x, $y, 0, 0, imagesx( $overlay ), imagesy( $overlay ) );
			// Reset the layer effect
			imagelayereffect( $this->_resource, IMG_EFFECT_NORMAL );
			// Return the object for chaining
			return $this;
		}

	 /**
	  * Mask the current image using the provided image file.
	  *
	  * @param mixed $path The path to the image file to use as a mask. Another JS_Image object can also be used.
	  * @param int $x The horizontal offset (from the left) for the mask. Defaults to 0.
	  * @param int $y The vertical offset (from the top) for the mask. Defaults to 0.
	  * @return JS_Image The object for chaining.
	  */

		public function mask( $path, $x=0, $y=0 ) {
			// Validate parameters
			if( ( ! $path instanceof Path || ! $path->isFile() ) && ! $path instanceof Image ) {
				throw new \InvalidArgumentException;
			}
			// Get the source image as a resource
			$mask = self::_fetch_resource( $path );
			// First we crop to the size of the mask
			self::crop( imagesx( $mask ), imagesy( $mask ), $x, $y );
			// Turn on alpha blending on the current canvas
			imagealphablending( $this->_resource, false );
			// Iterate through the pixels for the image
			for( $pixel_x = 0; $pixel_x < $this->_width; $pixel_x++ ) :
				for( $pixel_y = 0; $pixel_y < $this->_height; $pixel_y++ ) :
					// Get the alpha index
					$alpha = imagecolorsforindex( $mask, imagecolorat( $mask, $pixel_x, $pixel_y ) );
					$alpha = 127 - floor( $alpha['red'] / 2 );
					// Get the colour
					$color = imagecolorsforindex( $this->_resource, imagecolorat( $this->_resource, $pixel_x, $pixel_y ) );
					// Merge the colour and alpha index
					$color_alpha = imagecolorallocatealpha( $this->_resource, $color['red'], $color['green'], $color['blue'], $alpha );
					// Set the pixel colour and alpha on the current canvas
					imagesetpixel( $this->_resource, $pixel_x, $pixel_y, $color_alpha );
				endfor;
			endfor;
			// Return the object for chaining
			return $this;
		}

	 /**
	  * Randomly adjust the colour the pixels in the image, given a maximum percentage.
	  *
	  * @param float $percent The maximum amount of noise, as a percentage. Defaults to 0.1.
	  * @return JS_Image The object for chaining.
	  */

	    public function noise( $percent=0.1 ) {
			// Create a blank canvas in the size we want
			$canvas = self::_create_canvas( $this->_width, $this->_height );
	    	// Convert the percentage to a value
	    	$amount = ( $percent >= 0 && $percent <= 1 ) ? 255 * $percent : 255 * 0.1;
			// Iterate through the pixels for the image
			for( $pixel_x = 0; $pixel_x < $this->_width; $pixel_x++ ) {
				for( $pixel_y = 0; $pixel_y < $this->_height; $pixel_y++ ) {
					// Get the colour of the pixel
					$rgba = imagecolorsforindex( $this->_resource, imagecolorat( $this->_resource, $pixel_x, $pixel_y ) );
	                // Adjust the pixel colour
					$modifier = rand( 0 - $amount, $amount );
	                $red = self::_clean_color_value( $rgba['red'] + $modifier );
	                $green = self::_clean_color_value( $rgba['green'] + $modifier );
	                $blue = self::_clean_color_value( $rgba['blue'] + $modifier );
					// Create the new colour resource
					$new = imagecolorallocatealpha( $this->_resource, $red, $green, $blue, $rgba['alpha'] );
					// Set the colour of the pixel
	                imagesetpixel( $canvas, $pixel_x, $pixel_y, $new );
	            }
	        }
			// Destroy the original canvas
			self::destroy();
			// Update the object parameters
			$this->_resource = $canvas;
			// Return the object for chaining
			return $this;
	    }

	 /**
	  * Randomly rearrange the pixels for the image, given a maximum distance.
	  *
	  * @param int $dist The maximum pixel distance. Defaults to 2.
	  * @return JS_Image The object for chaining.
	  */

	    public function diffuse( $dist=2 ) {
			// Create a blank canvas in the size we want
			$canvas = self::_create_canvas( $this->_width, $this->_height );
			// Iterate through the pixels for the image
			for( $pixel_x = 0; $pixel_x < $this->_width; $pixel_x++ ) {
				for( $pixel_y = 0; $pixel_y < $this->_height; $pixel_y++ ) {
					// Get a random distance
	                $new_x = $pixel_x + rand( 0 - $dist, $dist );
	                $new_y = $pixel_y + rand( 0 - $dist, $dist );
	    			// If the position of the destination pixel is outside of the canvas
	                if( $new_x >= $this->_width || $new_x < 0 )
	                	$new_x = $pixel_x;
	                if( $new_y >= $this->_height || $new_y < 0 )
	                	$new_y = $pixel_y;
	                // Get the colour for the original pixel and the destination pixel
	 				$new = imagecolorsforindex( $this->_resource, imagecolorat( $this->_resource, $new_x, $new_y ) );
					$new = imagecolorallocatealpha( $this->_resource, $new['red'], $new['green'], $new['blue'], $new['alpha'] );
	    			// Swap the two pixels
	                imagesetpixel( $canvas, $pixel_x, $pixel_y, $new );
	            }
	        }
			// Destroy the original canvas
			self::destroy();
			// Update the object parameters
			$this->_resource = $canvas;
			// Return the object for chaining
			return $this;
	    }

	 /**
	  * Shift the colour pallet of the image to a given colour.
	  * This method adjusts the colour pallet using a custom algorithm. For an alternate method,
	  * see JS_Image::colorize_alt, which uses the built-in GD filter.
	  *
	  * @param int $red The red value for the desired colour shift. Should be between 0 and 255.
	  * @param int $green The green value for the desired colour shift. Should be between 0 and 255.
	  * @param int $blue The blue value for the desired colour shift. Should be between 0 and 255.
	  * @param float $percent The amount to shift towards the provided colour as a percentage.
	  * @return JS_Image The object for chaining.
	  */

	    public function colorize( $red, $green, $blue, $percent=0.5 ) {
			// Create a blank canvas in the size we want
			$canvas = self::_create_canvas( $this->_width, $this->_height );
			// Iterate through the pixels for the image
			for( $pixel_x = 0; $pixel_x < $this->_width; $pixel_x++ ) {
				for( $pixel_y = 0; $pixel_y < $this->_height; $pixel_y++ ) {
					// Get the colour of the pixel
					$rgba = imagecolorsforindex( $this->_resource, imagecolorat( $this->_resource, $pixel_x, $pixel_y ) );
	                // Adjust the pixel colour
	                $lightness = (int) ( $rgba['red'] + $rgba['green'] + $rgba['blue'] ) / 3;
					$red_diff = $rgba['red'] + ( ( $lightness - $rgba['red'] ) * $percent );
					$new_red = self::_clean_color_value( $red_diff + ( $red * $percent ) );
					$green_diff = $rgba['green'] + ( ( $lightness - $rgba['green'] ) * $percent );
	                $new_green = self::_clean_color_value( $green_diff + ( $green * $percent ) );
					$blue_diff = $rgba['blue'] + ( ( $lightness - $rgba['blue'] ) * $percent );
	                $new_blue = self::_clean_color_value( $blue_diff + ( $blue * $percent ) );
					// Create the new colour resource
					$new = imagecolorallocatealpha( $this->_resource, $new_red, $new_green, $new_blue, $rgba['alpha'] );
					// Set the colour of the pixel
	                imagesetpixel( $canvas, $pixel_x, $pixel_y, $new );
	            }
	        }
			// Destroy the original canvas
			self::destroy();
			// Update the object parameters
			$this->_resource = $canvas;
			// Return the object for chaining
			return $this;
		}

	 /**
	  * Remove colour from the image canvas, effectively converting it to a greyscale image.
	  *
	  * @param float $percent The amount of desaturation to be applied to the image as a percentage. Defaults to 1.
	  * @return JS_Image The object for chaining.
	  */

	    public function desaturate( $percent=1 ) {
	    	return self::colorize( 0, 0, 0, $percent );
		}

	 /**
	  * Change the opacity of the image so that it appears to be see through.
	  *
	  * @param float $percent The percentage of the original opacity.
	  * @return JS_Image The object for chaining.
	  */

	    public function opacity( $percent=1 ) {
			// Create a blank canvas in the size we want
			$canvas = self::_create_canvas( $this->_width, $this->_height );
			// Iterate through the pixels for the image
			for( $pixel_x = 0; $pixel_x < $this->_width; $pixel_x++ ) {
				for( $pixel_y = 0; $pixel_y < $this->_height; $pixel_y++ ) {
					// Get the colour of the pixel
					$rgba = imagecolorsforindex( $this->_resource, imagecolorat( $this->_resource, $pixel_x, $pixel_y ) );
	                // Adjust the alpha
	                $alpha = self::_clean_alpha_value( 127 - ( ( 127 - $rgba['alpha'] ) * $percent ) );
					// Create the new colour resource
					$new = imagecolorallocatealpha( $this->_resource, $rgba['red'], $rgba['green'], $rgba['blue'], $alpha );
					// Set the colour of the pixel
	                imagesetpixel( $canvas, $pixel_x, $pixel_y, $new );
	            }
	        }
			// Destroy the original canvas
			self::destroy();
			// Update the object parameters
			$this->_resource = $canvas;
			// Return the object for chaining
			return $this;
	    }

//
// SMART METHODS
// These methods do some extra calulations to make common tasks easier.
//

	 /**
	  * Resize image to the percentage of the original size.
	  * i.e. 1024x768 resized by 0.1 produces a 102x77 image (and canvas).
	  *
	  * @param int $percent The percentage of the original to resize the image to.
	  * @return JS_Image The object for chaining.
	  */

		public function percent( $percent ) {
			// Get the new width and height based on the percentage
			$width = round( $this->_width * $percent );
			$height = round( $this->_height * $percent );
			// Resize the image
			$this->resize( $width, $height );
			// Return the object for chaining
			return $this;
		}

	 /**
	  * Resize the image and canvas so that the image fits within the given dimensions.
	  * i.e. 1024x768 resized to fit 300x300 produces a 300x225 image (and canvas).
	  *
	  * @param int $width The maximum width for the resized image.
	  * @param int $height The maximum height for resized image.
	  * @return JS_Image The object for chaining.
	  */

		public function fit( $width, $height ) {
			// Set the default percent to 100
			$percent = 1;
			// Resize to fit the maximum width
			if( $this->_width > $width )
				$percent = ( $width / $this->_width );
			// Resize to fit the maximum height
			if( ( $this->_height * $percent ) > $height )
				$percent = ( $height / ( $this->_height * $percent ) );
			// Resize the image using the percentage
			if( $percent != 1 )
				$this->percent( $percent );
			// Return the object for chaining
			return $this;
		}

	 /**
	  * Resize the image and canvas so that the image fills the given dimensions.
	  * i.e. 1024x768 resized to fit 300x300 produces a 400x300 image centred in a 300x300 canvas.
	  *
	  * @param int $width The new width for the canvas.
	  * @param int $height The new height for the canvas.
	  * @return JS_Image The object for chaining.
	  */

		public function fill( $width, $height ) {
			// Set the default percent to 100
			$percent = 1;
			// Get the ratios
			$imageRatio = $this->_height / $this->_width;
			$resizeRatio = $height / $width;
			// Resize to fit the maximum height
			if( $imageRatio < $resizeRatio )
				$percent = ( $height / $this->_height );
			// Resize to fit the maximum width
			else
				$percent = ( $width / $this->_width );
			// Resize the image using the percentage
			if( $percent != 1 )
				$this->percent( $percent );
			// Crop the image
			if( $imageRatio != $resizeRatio ) {
				$x = 0 - round( ( $width - $this->_width ) / 2 );
				$y = 0 - round( ( $height - $this->_height ) / 2 );
				$this->crop( $width, $height, $x, $y );
			}
			// Return the object for chaining
			return $this;
		}

//
// FILTERS
// Run built-in GD filters.
//

	 /**
	  * Reverses all colors of the image, giving it a negative appearance.
	  *
	  * @return JS_Image The object for chaining.
	  */

		public function invert() {
			// Run the filter
			imagefilter( $this->_resource, IMG_FILTER_NEGATE );
			// Return the object for chaining
			return $this;
	    }

	 /**
	  * Uses mean removal to achieve a "sketchy" effect.
	  *
	  * @param int $level Changes the brightness of the image.
	  * @return JS_Image The object for chaining.
	  */

		public function brightness( $level ) {
			// Run the filter
			imagefilter( $this->_resource, IMG_FILTER_BRIGHTNESS, $level );
			// Return the object for chaining
			return $this;
	    }

	 /**
	  * Changes the contrast of the image.
	  *
	  * @param int $level The level of contrast.
	  * @return JS_Image The object for chaining.
	  */

		public function contrast( $level ) {
			// Run the filter
			imagefilter( $this->_resource, IMG_FILTER_CONTRAST, $level );
			// Return the object for chaining
			return $this;
	    }

	 /**
	  * Shift the colour pallet of the image to a given colour.
	  * This method uses the IMG_FILTER_COLORIZE filter provided by GD and gives a slightly different
	  * result to JS_Image::colorize. For convenience, they are set up to work using the same parameters.
	  *
	  * @param int $red The red value for the desired colour shift. Should be between 0 and 255.
	  * @param int $green The green value for the desired colour shift. Should be between 0 and 255.
	  * @param int $blue The blue value for the desired colour shift. Should be between 0 and 255.
	  * @param float $percent The amount to shift towards the provided colour as a percentage.
	  * @return JS_Image The object for chaining.
	  */

		public function colorize_alt( $red, $green, $blue, $percent=0.5 ) {
	        // Adjust the alpha
	        $alpha = self::_clean_alpha_value( 127 - ( 127 * $percent ) );
			// Run the filter
			imagefilter( $this->_resource, IMG_FILTER_COLORIZE, $red, $green, $blue, $alpha );
			// Return the object for chaining
			return $this;
	    }

	 /**
	  * Uses edge detection to highlight the edges in the image.
	  *
	  * @return JS_Image The object for chaining.
	  */

		public function edge_detect() {
			// Run the filter
			imagefilter( $this->_resource, IMG_FILTER_EDGEDETECT );
			// Return the object for chaining
			return $this;
	    }

	 /**
	  * Embosses the image.
	  *
	  * @return JS_Image The object for chaining.
	  */

		public function emboss() {
			// Run the filter
			imagefilter( $this->_resource, IMG_FILTER_EMBOSS );
			// Return the object for chaining
			return $this;
	    }

	 /**
	  * Blurs the image using the Gaussian method.
	  *
	  * @return JS_Image The object for chaining.
	  */

		public function gaussian_blur() {
			// Run the filter
			imagefilter( $this->_resource, IMG_FILTER_GAUSSIAN_BLUR );
			// Return the object for chaining
			return $this;
	    }

	 /**
	  * Blurs the image.
	  *
	  * @return JS_Image The object for chaining.
	  */

		public function selective_blur() {
			// Run the filter
			imagefilter( $this->_resource, IMG_FILTER_SELECTIVE_BLUR );
			// Return the object for chaining
			return $this;
	    }

	 /**
	  * Uses mean removal to achieve a "sketchy" effect.
	  *
	  * @return JS_Image The object for chaining.
	  */

		public function mean_removal() {
			// Run the filter
			imagefilter( $this->_resource, IMG_FILTER_MEAN_REMOVAL );
			// Return the object for chaining
			return $this;
	    }

	 /**
	  * Makes the image smoother.
	  *
	  * @param int $level The level of smoothness.
	  * @return JS_Image The object for chaining.
	  */

		public function smooth( $level ) {
			// Run the filter
			imagefilter( $this->_resource, IMG_FILTER_SMOOTH, $level );
			// Return the object for chaining
			return $this;
	    }

	 /**
	  * Applies a pixelation effect to the image.
	  *
	  * @param int $size Block size in pixels.
	  * @param bool $advanced Whether to use advanced pixelation effect (true) or not (false). Defaults to false.
	  * @return JS_Image The object for chaining.
	  */

		public function pixelate( $size, $advanced=false ) {
			// Run the filter
			imagefilter( $this->_resource, IMG_FILTER_PIXELATE, $size, $advanced );
			// Return the object for chaining
			return $this;
	    }

//
// CANVAS
// Manipulate the internal canvas.
//

	 /**
	  * Reduce the palette of the image canvas. This is a slightly quicker method than JS_Image::adaptive.
	  *
	  * @param int $colors The maximum number of colours to allow in the canvas.
	  * @param bool $dithering Flag indicating whether to dither the image (true) or not (false). Defaults to true.
	  * @return JS_Image The object for chaining.
	  */

		public function palette( $colors, $dithering=true ) {
	        // Set the colour palette for the current canvas
	        imagetruecolortopalette( $this->_resource, $dithering, $colors );
			// Return the object for chaining
			return $this;
		}

	 /**
	  * Reduce the palette of the image canvas, matching the resulting colours as closely to the original palette
	  * as possible. This produces a more accurate (and better looking) image that JS_Image::palette.
	  *
	  * @param int $colors The maximum number of colours to allow in the canvas.
	  * @param bool $dithering Flag indicating whether to dither the image (true) or not (false). Defaults to true.
	  * @return JS_Image The object for chaining.
	  */

		public function adaptive( $colors, $dithering=true ) {
			// Duplicate the current canvas
			$canvas = self::_create_canvas( $this->_width, $this->_height );
		    imagecopymerge( $canvas, $this->_resource, 0, 0, 0, 0, $this->_width, $this->_height, 100 );
		    // Reduce the palette of the image
		    imagetruecolortopalette( $this->_resource, $dithering, $colors );
		    // Match the resulting colours to the duplicated image.
		    imagecolormatch( $canvas, $this->_resource );
		    // And then destroy the duplicate
		    imagedestroy( $canvas );
			// Return the object for chaining
			return $this;
		}

	 /**
	  * Reduce the palette of the image canvas.
	  *
	  * @param int $red The red value for the desired background colour. Should be between 0 and 255.
	  * @param int $green The green value for the desired background colour. Should be between 0 and 255.
	  * @param int $blue The blue value for the desired background colour. Should be between 0 and 255.
	  * @param float $alpha The $alpha value for the desired background colour as a percentage.
	  * @return JS_Image The object for chaining.
	  */

		public function background( $red, $green, $blue, $alpha=1 ) {
	        // Clean the values
	        $red = self::_clean_color_value( $red );
	        $green = self::_clean_color_value( $green );
	        $blue = self::_clean_color_value( $blue );
	        $alpha = self::_clean_alpha_value( 127 - ( 127 * $alpha ) );
			// Create a blank canvas in the size we want
			$canvas = self::_create_canvas( $this->_width, $this->_height );
			// Create the new colour resource
			$rgba = imagecolorallocatealpha( $this->_resource, $red, $green, $blue, $alpha );
			// Set the background color as transparent
			imagefill( $canvas, 0, 0, $rgba );
			// Copy the image to our thumbnail
			imagecopyresampled( $canvas, $this->_resource, 0, 0, 0, 0, $this->_width, $this->_height, $this->_width, $this->_height );
			// Destroy the original canvas
			self::destroy();
			// Update the object parameters
			$this->_resource = $canvas;
			// Return the object for chaining
			return $this;
		}

	 /**
	  * Reset the image canvas to the original.
	  *
	  * @return JS_Image The object for chaining.
	  */

		public function reset() {
			// If we're dealing with an JS_Image object
			if( $this->_path instanceof \Framework\Core\Image ) {
				$image_size = $this->_path->size();
				$this->_width = $image_size->width;
				$this->_height = $image_size->height;
				$this->_mime = $this->_path->mime();
			}
			// If we have an existing path
			elseif( $this->_path instanceof \Framework\Core\Path && $this->_path->isFile() ) {
				$image_size = getimagesize( $this->_path );
				$this->_width = $image_size[0];
				$this->_height = $image_size[1];
				$this->_mime = $image_size['mime'];
			}
			// Destroy the original canvas (if one exists)
			self::destroy();
			// Get the source image as a resource
		 	$this->_resource = self::_fetch_resource( $this->_path );
		 	// Resize to 100% to workaround losing transparency when outputting the image unmanipulated.
		 	$this->percent( 1 );
			// Return the object for chaining
			return $this;
		}

	 /**
	  * Destroy the current canvas.
	  *
	  * @return JS_Image The object for chaining.
	  */

		public function destroy() {
			// Destroy the original canvas (if one exists)
			if( $this->_resource ) {
				imagedestroy( $this->_resource );
				$this->_resource = null;
			}
			// Return the object for chaining
			return $this;
		}

//
// PRIVATE METHODS
// Useful methods that shouldn't be called directly.
//

	 /**
	  * Create a canvas in the size given.
	  *
	  * @param int $width The width for the new canvas.
	  * @param int $height The height for new canvas.
	  * @returns An image resource.
	  */

		private function _create_canvas( $width, $height ) {
			// Create a blank canvas in the size we want
			$canvas = imagecreatetruecolor( $width, $height );
			// Turn off alpha blending
			imagesavealpha( $canvas, true );
			// Set the background color as transparent
			imagefill( $canvas, 0, 0, imagecolorallocatealpha( $canvas, 0, 0, 0, 127 ) );
			// Return the new canvas
			return $canvas;
		}

	 /**
	  * Fetch an image resource for the image
	  *
	  * @param string $path Server-side path to the image file.
	  * @returns An image resource.
	  */

		private function _fetch_resource( $path ) {
			// If we're dealing with an JS_Image object
			if( $path instanceof \Framework\Core\Image ) {
				$content = $path->asString();
				return imagecreatefromstring( $content );
			}
			// If we have an existing path
			elseif( $path instanceof \Framework\Core\Path && $path->isFile() ) {
				$content = $path->contents();
				return imagecreatefromstring( $content );
			}
			// Default to null
			return null;
		}

	 /**
	  * Cleans the given integer value to a value that can be used as a colour value.
	  * The output will be a round number between 0 and 255.
	  *
	  * @param int $value The value to be cleaned.
	  * @returns int The cleaned value.
	  */

		private function _clean_color_value( $value ) {
			// Max out the value
			$value = MIN( 255, $value );
			// Min value
			$value = MAX( 0, $value );
			// Return a round number
			return (int) round( $value );
		}

	 /**
	  * Cleans the given integer value to a value that can be used as an alpha value.
	  * The output will be a round number between 0 and 127.
	  *
	  * @param int $value The value to be cleaned.
	  * @returns int The cleaned value.
	  */

		private function _clean_alpha_value( $value ) {
			// Max out the value
			$value = MIN( 127, $value );
			// Min value
			$value = MAX( 0, $value );
			// Return a round number
			return (int) round( $value );
		}

	}
