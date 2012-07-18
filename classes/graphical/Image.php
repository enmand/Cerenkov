<?php
/**
 * Image.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/graphical
 */


class Image implements ImageInterface
{	
	/*
	 *	THESE FUNCTIONS ARE PUBLIC STATIC FUNCTIONS
	 */
		
	/**
	 * Checks to ensure the font is in the file system.
	 *
	 * @access public
	 * @static
	 * @param string $fontfile Directory to font file
	 * @return string Path to font file
	 **/
	public static function checkFont($fontfile)
	{
		$font = $GLOBALS['ROOT_PATH']."/system/fonts/".$fontfile;
		if(!File::fileExists($font))
			throw new GraphicsException("No such font file");
		return $font;
	}
	
	/**
	 * Clears all the images older than Config::cache_time
	 *
	 * @access public
	 * @static
	 * @param string $fontfile Directory to font file
	 * @return string Path to font file
	 **/
	public static function clearOld()
	{
		global $Config;
		$imageFile = new File($GLOBALS['ROOT_PATH'].'/'.$Config->get('file.temp_dir') . '/graphics/');
		if($handle = $imageFile->openDir())
		{
			while(false !== ($file = readdir($handle)))
			{
				if($file!='.' && $file!='..' && $file!='.svn')
				{
					$imageFile->setPath($GLOBALS['ROOT_PATH'].'/'.$Config->get("file.temp_dir").'/graphics/'.$file);
					if(time() - $imageFile->getModifiedTime() > $Config->get("cache.image_time"))
					{
						$imageFile->delete();
					}
				}
			}
		} else {
			throw new GraphicsException("There was an error cleaning the images");
		}
	}
	
	/**
	 * Deletes a given image based on the name and type of the image
	 *
	 * @access public
	 * @static
	 * @param string $name Name of the image
	 * @param string $type The type of file
	 * @return bool Throws exception is fails
	 **/
	public static function delete($name, $type)
	{
		global $Config;
		$id = self::generateID($name);
		if(!unlink($Config->get("file.temp_dir")."/graphics/".$id.".".$type))
			return false;
		else
			return true;
	}
	
	/**
	 * Generates the bounding coordinates for text that is written with a FreeType font.
	 *
	 * @access public
	 * @static
	 * @param float $size The size of the font
	 * @param float $angle The angle of the text
	 * @param string $fontfile The path font (in system/fonts)
	 * @param string $string The image string
	 * @param array $exrtainfo (optional) Extra information provided for the image
	 * @return bool Return is the same return of {@link http://www.php.net/imageftbbox imageftbbox()}
	 **/
	public static function ftBounding($size, $angle, $fontfile, $string, $extrainfo = NULL)
	{
		$font = self::checkFont($fontfile);
		return imagettfbbox($size, $angle, $font, $string, $extrainfo);
	}
	
	/**
	 * Returns the array, that is either formatted or not that is returned by the
	 * function gd_info()
	 *
	 * @access public
	 * @static
	 * @param bool $show Format the returned array
	 * @return array Return is the same return of {@link http://www.php.net/gd_info gd_info()}
	 **/
	public static function gdInfo($show = false)
	{
		if($show)
		{
			echo "<pre>"; var_dump(gd_info()); echo "</pre>";
		} else {
			return gd_info();
		}
	}
	
	/**
	 * Generates an identifying ID based on the $IDParams that were input
	 *
	 * @access public
	 * @static
	 * @param mixed $IDParams What to use to generate a unique ID.
	 * @return string Unique ID hash for image
	 **/
	public static function generateID($IDParams)
	{
		$id = md5(serialize($IDParams));
		return $id;
	}
	
	/**
	 * Check if $type is a supported type in GD
	 *
	 * @access public
	 * @static
	 * @param string $type The image type to check
	 * @return bool True if type is supported, false otherwise
	 **/
	public static function isSupported($type)
	{
		$supported = imagetypes();
		return($supported & constant("IMG_".strtoupper($type)));
	}
	
	/**
	 * Discovers if $src_img is an object of type Image or a gd resource, and returns
	 * the corresponding resource.
	 *
	 * @access public
	 * @static
	 * @param resource|Image $src_img Either an object of type Image, or a GD resource.
	 * @return res The corresponding GD resource
	 **/
	public static function obj_resource($src_img)
	{
		if(is_a($src_img, "Image"))
			$src_img_r = $src_img->getResource();
		else if(get_resource_type($src_img) === "gd")
			$src_img_r = $src_img;
		else
			throw new GraphicsException("Error getting resource");
		return $src_img_r;
	}
	
	/**
	 * Generates the bounding coordinates for text that is written with a PostScript Type 1 font.
	 *
	 * @access public
	 * @static
	 * @param string $string The string to be written
	 * @param int $font The font identifier as returned by this::psLoadFont()
	 * @param int $size The size of the font on the image
	 * @param int $spacing (optional) The value of the space of the font
	 * @param int $char_spacing (optional) The "tightness" of the characters, controls spacing between characters
	 * @param float $angle (optional) The angle at which the font will be written at.
	 * @return bool Return is the same return of {@link http://www.php.net/imagepsbbox imagepsbbox()}
	 **/
	public static function psBounding($string, $font, $size, $spacing = NULL, $char_spacing = NULL, $angle = NULL)
	{
		$num_args = func_num_args();
		if($num_args > 3 && $num_args < 9)
			throw new GraphicsException("Wrong parameter count");
		return imagepsbbox($string, $font, $size, $spacing, $char_spacing, $angle);
	}
	
	/**
	 * Generates the bounding coordinates for text that is written with a PostScript Type 1 font.
	 *
	 * @access public
	 * @static
	 * @param string $size The size of the font
	 * @param float $angle The angle at which the font is written
	 * @param string $fontfile The file name to be loaded (in system/fonts)
	 * @param string $string The string to be written
	 * @return bool Return is the same return of {@link http://www.php.net/imagettfbbox imagettfbbox()}
	 **/
	public static function ttfBounding($size, $angle, $fontfile, $string)
	{
		$font = self::checkFont($fontfile);
		return imagettfbbox($size, $angle, $font, $string);
	}
	
	

	/*
	 *	THESE FUNCTIONS ARE PUBLIC FUNCTIONS
	 */
	
	/**
	 * Default constructor generates the ID based on the name and type, and ensures that the
	 * typed entered is a valid type.
	 *
	 * @access public
	 * @param string $name The name of the image to be created
	 * @param float $type The type (will be checked using Image::checkType())
	 * @return Image Default constructor
	 **/
	public function __construct($name, $type)
	{
		global $Config;
		$this->brush = false;
		$this->id = self::generateID($name);
		$this->type = $type;
		if(self::checkType($this->type))
			$this->func = "image".$this->type;
	}
	/**
	 * Destroys the image in memory, as long as it isn't a brush or tile, since they will be
	 * destroyed when their parent image is.
	 *
	 * @access public
	 **/
	public function __destruct()
	{
		if(!$this->brush)
			$this->destroy();
	}
	
	/**
	 * Generates the bounding coordinates for text that is written with a PostScript Type 1 font.
	 *
	 * @access public
	 * @param bool $onoff Turn antialias on off of.
	 * @return bool Return is the same return of {@link http://www.php.net/imageantialias imageantialias()}
	 **/
	public function antialias($onoff)
	{
		return imageantialias($this->img_r, $onoff);
	}
	
	/**
	 * Draws an arc on the canvas based on arguments.
	 *
	 * @access public
	 * @param int $centre_x The x-coordinate of the centre
	 * @param int $centre_y The x-coordinate of the centre
	 * @param int $width The width of the arc
	 * @param int $height The height of the arc
	 * @param int $angle_start The start angle of the arc. 0deg is at 3 o'clock, and arc is drawn clockwise.
	 * @param int $angle_end The end angle of the arc
	 * @param int $colour The colour of the arc (the border if $filled is false, else the entire arc is $false = true)
	 * @param bool $filled (default = false) If true, will use imagefilledarc() instead, and create filled arc
	 * @param int $style (default = IMG_SRC_NOFILL) The style of fill to use. Please refer to {@link http://www.php.net/imagefilledarc imagefilledarc()} for constants
	 * @return bool The return is the same return as either {@link http://www.php.net/imagearc imagearc()} or {@link http://www.php.net/imagefilledarc imagefilledarc()}
	 **/
	public function arc($centre_x, $centre_y, $width, $height, $angle_start, $angle_end, $colour, $filled = false, $style = IMG_SRC_NOFILL)
	{
		if(!$filled)
			return imagearc($this->img_r, $centre_x, $centre_y, $width, $height, $angle_start, $angle_end, $colour);
		else
			return imagearc($this->img_r, $centre_x, $centre_y, $width, $height, $angle_start, $angle_end, $colour, $style);
	}

	/**
	 * Sets the thickness of the brush that should be used to draw
	 *
	 * @access public
	 * @param int $thickness The thickness of the brush to use
	 * @return bool Return is the same return of {@link http://www.php.net/imagesetthickness imagesetthickness()}
	 **/	
	public function brushThickness($thickness)
	{
		return imagesetthickness($this->img_r, $thickness);
	}
	
	/**
	 * Draw a single character, either horizontally, or vertically, depending on the value of $horz
	 *
	 * @access public
	 * @param int $font The GD font number to use when drawing the character
	 * @param int $x The x-coordinate to draw the character at
	 * @param int $y The y-coordinate to draw the character at
	 * @param int $string The string to take the first letter, or the character to be drawn
	 * @param int $colour The colour to use
	 * @param bool $horz (default = true) If true, write the character horizontally, else draw is vertically
	 *
	 * @return bool Return is the same return of {@link http://www.php.net/imagestring imagestring()}
	 **/
	public function char($font_num, $x, $y, $string, $colour, $horz = true)
	{
		return $this->string($font_num, $x, $y, $string[0], $colour, $horz);
	}
	
	/**
	 * Allocate a colour for use with the image. For a non-true-colour image, the first allocated colour will be
	 * background colour. Image::colorAllocate() is an alias for Image::ColourAllocate()
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)
	 * @return bool Return is the same return of {@link http://www.php.net/imagecolorallocate imagecolorallocate()}
	 **/
	public function colorAllocate($red, $green, $blue)
	{
		return $this->colourAllocate($red, $green, $blue);
	}
	
	/**
	 * Allocate a colour for use with the image. For a non-true-colour image, the first allocated colour will be
	 * background colour.
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)
	 * @return bool Return is the same return of {@link http://www.php.net/imagecolorallocate imagecolorallocate()}
	 **/
	public function colourAllocate($red, $green, $blue)
	{
		return imagecolorallocate($this->img_r, $red, $green, $blue);
	}
	
	/**
	 * Allocate a colour for use with the image. For a non-true-colour image, the first allocated colour will be
	 * background colour. Image::colorAllocateAlpha() is an alias for Image::colourAllocateAlpha()
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)
	 * @param int $alpha The value of how opaque the colour will be (from 0x00 to 0x7F) 
	 * @return bool Return is the same return of {@link http://www.php.net/imagecolorallocate imagecolorallocate()}
	 **/
	public function colorAllocateAlpha($red, $green, $blue, $alpha)
	{
		return $this->colourAllocateAlpha($red, $green, $blue, $alpha);
	}
	
	/**
	 * Allocate a colour for use with the image. For a non-true-colour image, the first allocated colour will be
	 * background colour.
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)
	 * @param int $alpha The value of how opaque the colour will be (from 0x00 to 0x7F) 
	 * @return bool Return is the same return of {@link http://www.php.net/imagecolorallocate imagecolorallocate()}
	 **/
	public function colourAllocateAlpha($red, $green, $blue, $alpha)
	{
		return imagecolorallocatealpha($this->img_r, $red, $green, $blue, $alpha);
	}
	
	/**
	 * Get the colour at a specific point (x, y). Image::colorAt() is an alias for Image::colourAt()
	 *
	 * @access public
	 * @param int $x The x-coordinate of where to get the colour
	 * @param int $y The y-coordinate of where to get the colour
	 * @return bool Return is the same return of {@link http://www.php.net/imagecolorat imagecolorat()}
	 **/
	public function colorAt($x, $y)
	{
		return $this->colour($x, $y);
	}
	
	/**
	 * Get the colour at a specific point (x, y).
	 *
	 * @access public
	 * @param int $x The x-coordinate of where to get the colour
	 * @param int $y The y-coordinate of where to get the colour
	 * @return bool Return is the same return of {@link http://www.php.net/imagecolorat imagecolorat()}
	 **/
	public function colourAt($x, $y)
	{
		return imagecolorat($this->img, $x, $y);
	}
	
	/**
	 * Get the colour that is closest to the colour in the image of similar RGB values. Image::colorClosest() is an
	 * alias of Image::colourClosest()
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)	 
	 * @return bool Return is the same return of {@link http://www.php.net/imagecolorclosest imagecolorclosest()}
	 **/
	public function colorClosest($red, $green, $blue)
	{
		return $this->colourClosest($red, $green, $blue);
	}
	
	/**
	 * Get the colour that is closest to the colour in the image of similar RGB values.
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)	 
	 * @return bool Return is the same return of {@link http://www.php.net/imagecolorclosest imagecolorclosest()}
	 **/
	public function colourClosest($red, $green, $blue)
	{
		return imagecolorclosest($this->img_r, $red, $green, $blue);
	}
	
	/**
	 * Get the colour that is closest to the colour in the image of similar RGBA values. Image::colorClosestAlpha() is an
	 * alias of Image::colourClosestAlpha()
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)
	 * @param int $alpha The value of how opaque the colour will be (from 0x00 to 0x7F) 
	 * @return bool Return is the same return of {@link http://www.php.net/imagecolorclosestalpha imagecolorclosestalpha()}
	 **/
	public function colorClosestAlpha($red, $green, $blue, $alpha)
	{
		return $this->colourClosestAlpha($red, $green, $blue, $alpha);
	}
	
	/**
	 * Get the colour that is closest to the colour in the image of similar RGBA values.
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)
	 * @param int $alpha The value of how opaque the colour will be (from 0x00 to 0x7F) 
	 * @return bool Return is the same return of {@link http://www.php.net/imagecolorclosestalpha imagecolorclosestalpha()}
	 **/
	public function colourClosestAlpha($red, $green, $blue, $alpha)
	{
		return imagecolorclosestalpha($this->img_r, $red, $green, $blue, $alpha);
	}
	
	/**
	 * Get the colour that is closest to the colour in the image of similar RGB values. Similar to Image::colourClosest(), 
	 * except uses hue, black and white to match the colours. Image::colorClosestHBW() is an alias of Image::colourClosestHBW()
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)
	 * @return bool Return is the same return of {@link http://www.php.net/imagecolorclosesthbw imagecolorclosesthbw()}
	 **/
	public function colorClosestHBW($red, $green, $blue)
	{
		return $this->colourClosestHBW($red, $green, $blue);
	}
	
	/**
	 * Get the colour that is closest to the colour in the image of similar RGB values. Similar to Image::colourClosest(), 
	 * except uses hue, black and white to match the colours.
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)
	 * @return bool Return is the same return of {@link http://www.php.net/imagecolorclosesthbw imagecolorclosesthbw()}
	 **/
	public function colourClosestHBW($red, $green, $blue)
	{
		return imagecolorclosesthwb($this->img_r, $red, $green, $blue);
	}
	
	/**
	 * Deallocate a colour in an image. Image::colorDeallocate() is an alias of Image::colourDeallocate();
	 *
	 * @access public
	 * @param int $colour The identifier of the colour in the image.
	 * @return bool Return is the same return of {@link http://www.php.net/imagecolordeallocate imagecolordeallocate()}
	 **/
	public function colorDeallocate($colour)
	{
		return $this->colourDeallocate($colour);
	}
	
	/**
	 * Deallocate a colour in an image.
	 *
	 * @access public
	 * @param int $colour The identifier of the colour in the image.
	 * @return bool Return is the same return of {@link http://www.php.net/imagecolordeallocate imagecolordeallocate()}
	 **/
	public function colourDeallocate($colour)
	{
		return imagecolordeallocate($this->img_r, $colour);
	}
	
	/**
	 * Get the index of the colour in an image based on the RGB values. Image::colorExact() is an alias of Image::colourExact()
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)
	 * @return int Return is the same return of {@link http://www.php.net/imagecolorexact imagecolorexact()}
	 **/
	public function colorExact($red, $green, $blue)
	{
		return $this->colourExact($red, $green, $blue);
	}
	
	/**
	 * Get the index of the colour in an image based on the RGB values
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)
	 * @return int Return is the same return of {@link http://www.php.net/imagecolorexact imagecolorexact()}
	 **/
	public function colourExact($red, $green, $blue)
	{
		return imagecolorexact($this->img_r, $red, $green, $blue);
	}
	
	/**
	 * Get the index of the colour in an image based on the RGBA values. Image::colorExactAlpha() is an alias
	 * of Image::colourExactAlpha()
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)
	 * @param int $alpha The value of how opaque the colour will be (from 0x00 to 0x7F) 
	 * @return int Return is the same return of {@link http://www.php.net/imagecolorexact imagecolorexact()}
	 **/
	public function colorExactAlpha($red, $green, $blue, $alpha)
	{
		return $this->colourExactAlpha($red, $green, $blue, $alpha);
	}
	
	/**
	 * Get the index of the colour in an image based on the RGBA values. Image::colorExactAlpha() is an alias
	 * of Image::colourExactAlpha()
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)
	 * @param int $alpha The value of how opaque the colour will be (from 0x00 to 0x7F) 
	 * @return int Return is the same return of {@link http://www.php.net/imagecolorexactalpha imagecolorexactalpha()}
	 **/
	public function colourExactAlpha($red, $green, $blue, $alpha)
	{
		return imagecolorexactalpha($this->img_r, $red, $green, $blue);
	}
	
	/**
	 * Match the colours of a palette version of an image more closely with the true colour version of the image.
	 * Image::colorMatch() is an alias of Image::colourMatch()
	 *
	 * @access public
	 * @param Image $img2
	 * @return bool Return is the same return of {@link http://www.php.net/imagecolormatch imagecolormatch()}
	 **/
	public function colorMatch(Image $img2)
	{
		return $this->colourMatch($img2);
	}
	
	/**
	 * Match the colours of a palette version of an image more closely with the true colour version of the image.
	 *
	 * @access public
	 * @param Image $img2
	 * @return bool Return is the same return of {@link http://www.php.net/imagecolormatch imagecolormatch()}
	 **/
	public function colourMatch(Image $img2)
	{
		return imagecolormatch($this->img_r, $img2->getResource());
	}
	
	/**
	 * Get the colour that is closest to the colour in the image of similar RGB values. Guaranteed to return an
	 * index to a colour, unlike Image::colourClosest(). Image::colorResolve() is an alias of Image::colourResolve()
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)	 
	 * @return int Return is the same return of {@link http://www.php.net/imagecolorresolve imagecolorresolve()}
	 **/
	public function colorResolve($red, $green, $blue)
	{
		return $this->colourResolve($red, $green, $blue);
	}
	
	/**
	 * Get the colour that is closest to the colour in the image of similar RGB values. Guaranteed to return an
	 * index to a colour, unlike Image::colourClosest().
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)	 
	 * @return int Return is the same return of {@link http://www.php.net/imagecolorresolve imagecolorresolve()}
	 **/
	public function colourResolve($red, $green, $blue)
	{
		return imagecolorresolve($this->img_r, $red, $green, $blue);
	}
	
	/**
	 * Get the colour that is closest to the colour in the image of similar RGB values. Guaranteed to return an
	 * index to a colour, unlike Image::colourClosest(). Image::colorResolveAlpha() is an alias of Image::colourResolveAlpha()
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)	
	 * @param int $alpha The value of how opaque the colour will be (from 0x00 to 0x7F) 
	 * @return int Return is the same return of {@link http://www.php.net/imagecolorresolve imagecolorresolve()}
	 **/
	public function colorResolveAlpha($red, $green, $blue, $alpha)
	{
		return $this->colourResolve($red, $green, $blue, $alpha);
	}
	
	/**
	 * Get the colour that is closest to the colour in the image of similar RGB values. Guaranteed to return an
	 * index to a colour, unlike Image::colourClosest().
	 *
	 * @access public
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greenness of the colour (from 0x00 to 0xFF)
	 * @param int $blue The blueness of the colour (from 0x00 to 0xFF)	
	 * @param int $alpha The value of how opaque the colour will be (from 0x00 to 0x7F) 
	 * @return int Return is the same return of {@link http://www.php.net/imagecolorresolve imagecolorresolve()}
	 **/
	public function colourResolveAlpha($red, $green, $blue, $alpha)
	{
		return imagecolorresolvealpha($this->img_r, $red, $green, $blue, $alpha);
	}
	
	/**
	 * Set the index specified with $colour to the colour give by the RGB values. Image::colorSet() is 
	 * an alias of Image::colourSet()
	 *
	 * @access public
	 * @param int $colour The index of the colour
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greeness of the colour (from 0x00 to 0xFF)	
	 * @param int $blue The blueness the colour (from 0x00 to 0x7F) 
	 * @return void
	 **/
	public function colorSet($colour, $red, $green, $blue)
	{
		$this->colourSet($colour, $red, $green, $blue);
	}
	
	/**
	 * Set the index specified with $colour to the colour give by the RGB values.
	 *
	 * @access public
	 * @param int $colour The index of the colour
	 * @param int $red The redness of the colour (from 0x00 to 0xFF)
	 * @param int $green The greeness of the colour (from 0x00 to 0xFF)	
	 * @param int $blue The blueness the colour (from 0x00 to 0x7F) 
	 * @return void
	 **/
	public function colourSet($colour, $red, $green, $blue)
	{
		imagecolorset($this->img_r, $colour, $red, $green, $blue);
	}
	
	/**
	 * Get the RGB colour values of a specific index. Image::colorsForIndex() is 
	 * an alias of Image::coloursForIndex()
	 *
	 * @access public
	 * @param int $index The index of the colour
	 * @return array Return is the same return of {@link http://www.php.net/imagecolorsforindex imagecolorsforindex()}
	 **/
	public function colorsForIndex($index)
	{
		return $this->coloursForIndex($index);
	}
	
	/**
	 * Get the RGB colour values of a specific index. Image::colorsForIndex() is 
	 * an alias of Image::coloursForIndex()
	 *
	 * @access public
	 * @param int $index The index of the colour
	 * @return array Return is the same return of {@link http://www.php.net/imagecolorsforindex imagecolorsforindex()}
	 **/
	public function coloursForIndex($index)
	{
		return imagecolorsforindex($this->img_r, $index);
	}

	/**
	 * Get the total number of colours that are in the image. Image::colorsTotal() is 
	 * an alias of Image::coloursTotal()
	 *
	 * @access public
	 * @return int Return is the same return of {@link http://www.php.net/imagecolorstotal imagecolorstotal()}
	 **/
	public function colorsTotal()
	{
		return $this->coloursTotal();
	}
	
	/**
	 * Get the total number of colours that are in the image.
	 *
	 * @access public
	 * @return int Return is the same return of {@link http://www.php.net/imagecolorstotal imagecolorstotal()}
	 **/
	public function coloursTotal()
	{
		return imagecolorstotal($this->img_r);
	}
	
	/**
	 * Turn the colour that is pointed to by the colour index $colour into a transparent colour. 
	 * Image::colorTransparent() is an alias of Image::colorTransparent()
	 *
	 * @access public
	 * @param int $colour The index to the colour that will be the transparent colour
	 * @return int Return is the same return of {@link http://www.php.net/imagecolortransparent imagecolortransparent()}
	 **/
	public function colorTransparent($colour)
	{
		return $this->colourTransparent($this->img_r, $colour);
	}
	
	/**
	 * Turn the colour that is pointed to by the colour index $colour into a transparent colour. 
	 *
	 * @access public
	 * @param inde $colour The index to the colour that will be the transparent colour
	 * @return int Return is the same return of {@link http://www.php.net/imagecolortransparent imagecolortransparent()}
	 **/
	public function colourTransparent($colour)
	{
		return imagecolortransparent($this->img_r, $colour);
	}
	
	/**
	 * Apply the convolution in the 3x3 matrix $matrix. 
	 *
	 * @access public
	 * @param array $matrix The 3x3 matrix by which to apply the convolution 
	 * @param float $div The divisor of the result of the convolution. For normalization
	 * @param float $offset The offset of the convolution.
	 * @return int Return is the same return of {@link http://www.php.net/imageconvolution imageconvolution()}
	 **/
	public function convolution(array $matrix, $div, $offset)
	{
		return imageconvolution($this->img_r, $matrix, $div, $offset);
	}
	
	/**
	 * Copy all, or a portion of another image into the current image object 
	 *
	 * @access public
	 * @param resource|Image $src_img The image that is to be copied in
	 * @param int $dest_x The x-coordinate of the destination point
	 * @param int $dest_y The y-coordinate of the destination point
	 * @param int $src_x The x-coordinate of the source point
	 * @param int $src_y The y-coordinate of the source point
	 * @param int $src_w The width of the source image
	 * @param int $src_h The height of the source image
	 * @param int $percent (optional) The amount (in percent) of the image to copy
	 * @return bool Return is the same return of {@link http://www.php.net/imagecopy imagecopy()} or {@link http://www.php.net/imagecopymerge imagecopymerge()}
	 **/
	public function copy($src_img, $dest_x, $dest_y, $src_x, $src_y, $src_w, $src_h, $percent = NULL)
	{
		$src_img_r = self::obj_resource($src_img);
		if(is_null($percent))
			return imagecopy($this->img_r, $src_img_r, $dest_x, $dest_y, $src_x, $src_y, $src_w, $src_h);
		else
			return imagecopymerge($this->img_r, $src_img_r, $dest_x, $dest_y, $src_x, $src_y, $src_w, $src_h, $percent);
	}
	
	/**
	 * Create a brush by which you may draw on the canvas with
	 *
	 * @access public
	 * @param int $brush_w The width of the brush to create
	 * @param int $brush_h The height of the brush to create
	 * @return Image The brush as an object of Image is returned.
	 **/
	public function createBrush($brush_w, $brush_h)
	{
		$brush = new Image($this->name."_brush", $this->type);
		$brush->isBrush();
		if($this->isTrueColour())
			$brush->createCanvas($brush_w, $brush_h, true);
		else
			$brush->createCanvas($brush_w, $brush_h, false);
		return $brush;
	}
	
	/**
	 * Create a brush by which you may draw on the canvas with
	 *
	 * @access public
	 * @param int $width The width of the brush to create
	 * @param int $height The height of the brush to create
	 * @param bool $trueColour (default = true) True creates a True Colour image, false creates a palette image
	 * @return Image The brush as an object of Image is returned.
	 **/
	public function createCanvas($width, $height, $trueColour = true)
	{
		if($trueColour)
			$this->img_r = imagecreatetruecolor($width, $height);
		else
			$this->img_r = imagecreate($width, $height);
		if(is_null($this->img_r))
			throw new GraphicsException("Could not create canvas");
		else
			return true;
	}
	
	/**
	 * Creates the image in the file system and returns the path to it
	 *
	 * @access public
	 * @return string The path to the image file
	 **/
	public function createImage()
	{
		global $Config;
		$func = $this->func;
		if($func($this->img_r, $GLOBALS['ROOT_PATH'] . "/" . $Config->get("file.temp_dir")."/graphics/".$this->id.".".$this->type))
		{
			return $Config->get("cerenkov.path") . "/" . $Config->get("file.temp_dir")."/graphics/".$this->id.".".$this->type;
		}
	}
	
	/**
	 * Create a tile by which you may draw on the canvas with
	 *
	 * @access public
	 * @param int $tile_w The width of the tile to create
	 * @param int $tile_h The height of the tile to create
	 * @return Image The tile as an object of Image is returned.
	 **/
	public function createTile($tile_w, $tile_h)
	{
		$tile = new Image($this->name."_tile", $this->type);
		$tile->isBrush();
		if($this->isTrueColour())
			$tile->createCanvas($tile_w, $tile_h, true);
		else
			$tile->createCanvas($tile_w, $tile_h, false);
		return $tile;
	}
	
	/**
	 * Creates an image based on a previous image in the file system
	 *
	 * @access public
	 * @param string $filename The filename (and path) of the image
	 * @param string $type The type of the image that will be used
	 * @return void
	 **/
	public function createFrom($filename, $type)
	{
		$from_func = '';
		if(self::checkType($type))
		{
			switch($type)
			{
				case "string":
					$from_func = "imagecreatefromstring";
					break;
				case "xbm":
					$from_func = "imagecreatefromxbm";
					break;
				case "xpm":
					$from_func = "imagecreatefromxpm";
					break;
				default:
					$from_func = "imagecreatefrom".$type;
			}
		}
		else
			throw new GraphicsException("Invalid type");
		$this->img_r = $from_func($filename);
	}
	
	/**
	 * Draw an ellipse on the canvas based on the provided arguments
	 *
	 * @access public
	 * @param int $centre_x The x-coordinate of the centre of the ellipse
	 * @param int $centre_y The y-coordinate of the centre of the ellipse
	 * @param int $width The width of the ellipse
	 * @param int $height The height of the ellipse
	 * @param int $colour The colour of the ellipse (or border if $filled = false)
	 * @param bool $filled (default = false) If true the ellipse will be filled with $colour
	 * @return bool Return is the same return of {@link http://www.php.net/imageellipse imageellipse()} or {@link http://www.php.net/imagefilledellipse imagefilledellipse()}
	 **/
	public function ellipse($centre_x, $centre_y, $width, $height, $colour, $filled = false)
	{
		if(!$filled)
			return imageellipse($this->img_r, $centre_x, $centre_y, $width, $height, $colour);
		else
			return imagefilledellipse($this->img_r, $centre_x, $centre_y, $width, $height, $colour);
	}
	
	/**
	 * Flood fill the image with $colour
	 *
	 * @access public
	 * @param int $x The x-coordinate to start the fill
	 * @param int $y The y-coordinate to start the fill
	 * @param int $colour The colour to fill
	 * @return bool Return is the same return of {@link http://www.php.net/imagefill imagefill()} or {@link http://www.php.net/imagefill imagefill()}
	 **/
	public function fill($x, $y, $colour)
	{
		return imagefill($this->img_r, $x, $y, $colour);
	}
	
	/**
	 * Flood fill to the border of the image with $colour
	 *
	 * @access public
	 * @param int $x The x-coordinate to start the fill
	 * @param int $y The y-coordinate to start the fill
	 * @param int $border The colour of the border
	 * @param int $colour The colour to fill
	 * @return bool Return is the same return of {@link http://www.php.net/imagefilltoborder imagefilltoborder()} or {@link http://www.php.net/imagefilltoborder imagefilltoborder()}
	 **/
	public function fillToBorder($x, $y, $border, $colour)
	{
		return imagefilltoborder($this->img_r, $x, $y, $border, $colour);
	}
	
	/**
	 * Flood fill to the border of the image with $colour
	 *
	 * @access public
	 * @param int $filter The type of filter to apply
	 * @param int $arg1 Undocumented argument (depends on $filter)
	 * @param int $arg2 Undocumented argument (depends on $filter)
	 * @param int $arg3 Undocumented argument (depends on $filter)
	 * @return bool Return is the same return of {@link http://www.php.net/imagefilter imagefilter()} 
	 **/
	public function filter($filtertype, $red = NULL, $green = NULL, $blue = NULL, $alpha = NULL)
	{
		return imagefilter($this->img_r, $red, $green, $blue, $alpha);
	}
	
	/**
	 * Get the height of an internal (or loaded) GD font
	 *
	 * @access public
	 * @param int $font_num The font number to check the height of
	 * @return bool Return is the same return of {@link http://www.php.net/imagefontheight imagefontheight()} 
	 **/
	public function fontHeight($font_num)
	{
		return imagefontheight($font_num);
	}
	
	/**
	 * Get the width of an internal (or loaded) GD font
	 *
	 * @access public
	 * @param int $font_num The font number to check the width of
	 * @return bool Return is the same return of {@link http://www.php.net/imagefontwidth imagefontwidth()} 
	 **/
	public function fontWidth($font_num)
	{
		return imagefontwidth($font_num);
	}
	
	/**
	 * Write a string to the image using a FreeType font
	 *
	 * @access public
	 * @param float $size The font number to check the width of
	 * @param float $angle The angle at which to write the font
	 * @param int $start_x The start x-coordinate of the text
	 * @param int $start_y The start y-coordinate of the text
	 * @param int $colour The colour of the text to be written
	 * @param string $fontfile The file path of the font (in system/fonts)
	 * @param string $string The string to be written to the image.
	 * @return bool Return is the same return of {@link http://www.php.net/imagefttext imagefttext()} 
	 **/
	public function ftText($size, $angle, $start_x, $start_y, $colour, $fontfile, $string, array $extrainfo = NULL)
	{
		$font = self::checkFont($fontfile);
		return imagefttext($this->img_r, $size, $angle, $start_x, $start_y, $colour, $font, $string, $extrainfo = NULL);
	}
	
	/**
	 * Write a string to the image using a FreeType font
	 *
	 * @access public
	 * @param float $inputGamma The input gamma
	 * @param float $outputGamma The output gamma
	 * @return bool Return is the same return of {@link http://www.php.net/imagegammacorrect imagegammacorrect()} 
	 **/
	public function gammaCorrect($inputGamma, $outputGamma)
	{
		imagegammacorrect($this->img_r, $inputgamma, $outputgamma);
	}
	
	/**
	 * Get the unique ID identifier for the image.
	 *
	 * @access public
	 * @return string The unique ID identifier
	 **/
	public function getID()
	{
		return $this->img_id;
	}
	
	/**
	 * Get the path to the image
	 *
	 * @access public
	 * @return string The path to the image
	 **/
	public function getPath()
	{
		global $Config;
		return $Config->get("cerenkov.path") . '/' . $Config->get("file.temp_dir")."/graphics/".$this->id.".".$this->type;
	}
	
	/**
	 * Get the resource of the image
	 *
	 * @access public
	 * @return resource The resource of the image
	 **/
	public function getResource()
	{
		return $this->img_r;
	}
	
	/**
	 * Get the height of the image
	 *
	 * @access public
	 * @return int The height of the image
	 **/
	public function height()
	{
		return imagesy($this->img_r);
	}
	
	/**
	 * Checks to see if the current image is true colour. Image::isTrueColor() is an alias for Image::isTrueColour()
	 *
	 * @access public
	 * @return bool True is the image is true colour, false other wise.
	 **/
	public function isTrueColor()
	{
		return $this->isTrueColour();
	}
	/*
	 * Checks to see if the current image is true colour.
	 *
	 * @access public
	 * @return bool True is the image is true colour, false other wise.
	 **/
	public function isTrueColour()
	{
		return imageistruecolor($this->img_r);
	}
	
	/**
	 * Interlace the image if TRUE, else don't
	 *
	 * @access public
	 * @param bool 
	 * @return bool True is the image is true colour, false other wise.
	 **/
	public function interlace($interlace = FALSE)
	{
		if($interlace === TRUE)
			$interlace = 1;
		else if($interlace === FALSE)
			$interlace = 0;
		return imageinterlace($this->img_r, $interlace);
	}
	
	/**
	 * Add an effect to the image
	 *
	 * @access public
	 * @param string $effect The effect to add to the image
	 * @return bool Return is the same return of {@link http://www.php.net/imagelayereffect imagelayereffect()} 
	 **/
	public function layerEffect($effect)
	{
		return imagelayereffect($this->img_r, $effect);
	}
	
	/**
	 * Draw a line on the image.
	 *
	 * @access public
	 * @param int $x1 The initial x-coordinate of the line
	 * @param int $y1 The initial y-coordinate of the line
	 * @param int $x2 The ending x-coordinate of the line
	 * @param int $y2 The ending y-coordinate of the line
	 * @param int $colour The colour of the text
	 * @return bool Return is the same return of {@link http://www.php.net/imageline imageline()} 
	 **/
	public function line($x1, $y1, $x2, $y2, $colour)
	{
		return imageline($this->img_r, $x1, $y1, $x2, $y2, $colour);
	}
	
	/**
	 * Add an effect to the image
	 *
	 * @access public
	 * @param sting $filename The effect to add to the image
	 * @return bool Return is the same return of {@link http://www.php.net/imagelayereffect imagelayereffect()} 
	 **/
	public function loadFont($filename)
	{
		var_dump(File::fileExists("system/fonts/".$filename));
		return imageloadfont("system/fonts/".$filename);
	}
	
	/**
	 * Copy one palette of an Image object (or resource) from the source to the destination
	 *
	 * @access public
	 * @param resource|Image $src_img The image to copy the image
	 * @return bool Return is the same return of {@link http://www.php.net/imagepalettecopy imagepalettecopy()} 
	 **/
	public function paletteCopy($src_img)
	{
		$src_img_r = self::obj_resource($src_img);
		imagepalettecopy($this->img_r, $src_img_r);
	}
	
	/**
	 * Copy one palette of an Image object (or resource) from the source to the destination
	 *
	 * @access public
	 * @param int $x The x-coordinate of the pixel to light
	 * @param int $y The y-coordinate of the pixel to light
	 * @param int $colour The colour of the pixel to light
	 * @return bool Return is the same return of {@link http://www.php.net/imagesetpixel imagesetpixel()} 
	 **/
	public function pixel($x, $y, $colour)
	{
		return imagesetpixel($this->img_r, $x, $y, $col);
	}
	
	/**
	 * Copy one palette of an Image object (or resource) from the source to the destination
	 *
	 * @access public
	 * @param array $points The array points polygon to draw (array goes x1, y1, x2, y2, etc.)
	 * @param int $colour The colour of the pixel to light
	 * @param bool $filled True if the polygon is to be completely filled
	 * @return bool Return is the same return of {@link http://www.php.net/imagepolygon imagepolygon()} or {@link http://www.php.net/imagefilledpolygon imagefilledpolygon()}
	 **/
	public function polygon(array $points, $colour, $filled = false)
	{
		$numPoints = count($points)/2;
		if(!$filled)
			return imagepolygon($this->img_r, $points, $numPoints, $colour);
		else
			return imagefilledpolygon($this->img_r, $points, $numPoints, $colour);
	}
	
	/**
	 * Encode the font specified by $font to be encoded by the file $fname
	 *
	 * @access public
	 * @param string $font The font to be encoded
	 * @param string $fname The file to encode the font with
	 * @return bool Return is the same return of {@link http://www.php.net/imagesetpixel imagesetpixel()} 
	 **/
	public function psEncodeFont($font, $fname)
	{
		return imagepsencodefont($font, $fname);
	}
	
	/**
	 * Expand or condense a font ($font) by $ratio
	 *
	 * @access public
	 * @param int $font The font to be encoded
	 * @param float $ratio The file to encode the font with
	 * @return bool Return is the same return of {@link http://www.php.net/imagepsextendfont imagepsextendfont()} 
	 **/
	public function psExtendFont($font, $ratio)
	{
		return imagepsextendfont($font, $ratio);
	}
	
	/**
	 * Free the font that was loaded by Image::psLoadFont()
	 *
	 * @access public
	 * @param resource $font_r The font resource that was loaded with Image::psLoadFont()
	 * @return bool Return is the same return of {@link http://www.php.net/imagepsextendfont imagepsextendfont()} 
	 **/
	public function psFreeFont($font_r)
	{
		return imagepsfreefont($font_r);
	}
	
	/**
	 * Load a PostScript Type 1 font
	 *
	 * @access public
	 * @param string $fontfile The filename of the font that should be loaded
	 * @return resource Return is the same return of {@link http://www.php.net/imagepsloadfont imagepsloadfont()} 
	 **/
	public function psLoadFont($fontfile)
	{
		$font = self::checkFont($fontfile);
		return imagepsloadfont($font);
	}
	
	/**
	 * Slant $font by a certain amount specified by $slant
	 *
	 * @access public
	 * @param resource $font The resource of the font (that was loaded by Image::psSlantFont())
	 * @param float $slant The amount to slant a font resource
	 * @return bool Return is the same return of {@link http://www.php.net/imagepsslantfont imagepsslantfont()} 
	 **/
	public function psSlantFont($font, $slant)
	{
		return imagepsslantfont($font, $slant);
	}
	
	/**
	 * Write a string to the image using a FreeType font
	 *
	 * @access public
	 * @param string $string The string to be written to the image.
	 * @param string $font The file path of the font (in system/fonts)
	 * @param int $size The font number to check the width of
	 * @param int $f_colour The colour of the foreground for the font
	 * @param int $b_colour The colour of the background for the font
	 * @param int $start_x The start x-coordinate of the text
	 * @param int $start_y The start y-coordinate of the text
	 * @param int $spacing (optional) The amount of spacing between words
	 * @param int $char_spacing (optional) The amount of spacing between characters
	 * @param float $angle The angle at which to write the font
	 * @param bool $antialias (optional) Turn antialiasing off or off
	 * @return bool Return is the same return of {@link http://www.php.net/imagefttext imagefttext()} 
	 **/
	public function psText($string, $font, $size, $f_colour, $b_colour, $start_x, $start_y, $spacing = NULL, $char_spacing = NULL, $angle = NULL, $antialias = NULL)
	{
		$num_args = func_num_args();
		if(($num_args > 6 && $num_args < 10))
			throw new GraphicsException("Wrong parameter count");
		return imagepstext($this->img_r, $string, $font, $size, $f_colour, $b_colour, $start_x, $start_y, $spacing, $char_spacing, $angle, $antialias);
	}
	
	/**
	 * Draw a rectangle on the image.
	 *
	 * @access public
	 * @param int $top_left_x The x-coordinate of the top left of the rectangle
	 * @param int $top_left_y The y-coordinate of the top left of the rectangle
	 * @param int $bottem_right_x The x-coordinate of the bottom right of the rectangle
	 * @param int $bottom_right_y The x-coordinate of the bottom right of the rectangle
	 * @param int $colour The colour of the rectangle border (or the entire rectangle if $filled is true)
	 * @param bool $filled True if the entire rectangle should be filled, false otherwise
	 * @return bool Return is the same return of {@link http://www.php.net/imageline imageline()} 
	 **/
	public function rectangle($top_left_x, $top_left_y, $bottem_right_x, $bottom_right_y, $colour, $filled = false)
	{
		if(!$filled)
			return imagerectangle($this->img_r, $top_left_x, $top_left_y, $bottem_right_x, $bottom_right_y, $colour);
		else
			return imagefilledrectangle($this->img_r, $top_left_x, $top_right_y, $bottom_righr_x, $bottom_right_y, $colour);
	}
	
	/**
	 * Resample (or simply resize) the an image into this Image object
	 *
	 * @access public
	 * @param resource|Image $src_img The x-coordinate of the top left of the rectangle
	 * @param int $dest_x The x-coordinate of the destination point
	 * @param int $dest_y The y-coordinate of the destination point
	 * @param int $src_x The x-coordinate of the source point
	 * @param int $src_y The y-coordinate of the source point
	 * @param int $dest_w The width of the destination point
	 * @param int $dest_h The height of the destination point
	 * @param int $src_w The width of the source point
	 * @param int $src_h The height of the destination point
	 * @param bool $interpol (default = true) If $interpolate is true, will resample the image, else will just resize
	 * @return bool Return is the same return of {@link http://www.php.net/imagecopyresampled imagecopyresampled()} or {@link http://www.php.net/imagecopyresized imagecopyresized()}
	 **/
	public function resize($src_img, $dest_x, $dest_y, $src_x, $src_y, $dest_w, $dest_h, $src_w, $src_h, $interpol = true)
	{
		$src_img_r = self::obj_resource($src_img);
		if($interpole)
			return imagecopyresampled($this->img_r, $src_img_r, $dest_x, $dest_y, $src_x, $src_y, $dest_w, $dest_h, $src_w, $src_h);
		else
			return imagecopyresized($this->img_r, $src_img_r, $dest_x, $dest_y, $src_x, $src_y, $dest_w, $dest_h, $src_w, $src_h);
	}
	
	/**
	 * Rotate the image by $angle
	 *
	 * @access public
	 * @param int $angle The angle at which to rotate the image
	 * @param int $bgd_colour The colour of the uncovered zone
	 * @param bool|int $ignore_transparent TRUE (or !0) will ignore transparent colours, else it will keep them
	 * @return bool Return is the same return of {@link http://www.php.net/imageline imageline()} 
	 **/
	public function rotate($angle, $bgd_colour, $ignore_transparent)
	{
		if($ignore_transparent === TRUE)
			$ignore_transparent = 0;
		else if($ignore_transparent === FALSE)
			$ignore_transparent = 1;
		return imagerotate($this->img_r, $angle, $bgd_colour, $ignoretransparent);
	}
	
	/**
	 * Wither we should save the full alpha channel when saving a ping, or not
	 *
	 * @access public
	 * @param bool $onoff The angle at which to rotate the image
	 * @return bool Return is the same return of {@link http://www.php.net/imagesavealpha imagesavealpha()} 
	 **/
	public function saveAlpha($onoff)
	{
		return imagesavealpha($this->img_r, $onoff);
	}
	
	/**
	 * Set the Image object (or resource) to use as brush
	 *
	 * @access public
	 * @param resource|Image $brush What image to use as the brush
	 * @return bool Return is the same return of {@link http://www.php.net/imagesetbrush imagesetbrush()} 
	 **/
	public function setBrush(Image $brush)
	{
		$brush_r = self::obj_resource($brush);
		return imagesetbrush($this->img_r, $brush_r);
	}
	
	/**
	 * Sets the style defined by $array
	 *
	 * @access public
	 * @param array $style The style as defined by the array
	 * @return bool Return is the same return of {@link http://www.php.net/imagesetstyle imagesetstyle()} 
	 **/
	public function setStyle(array $style)
	{
		return imagesetstyle($this->img_r, $style);
	}
	
	/**
	 * Set the Image object (or resource) to use as tile
	 *
	 * @access public
	 * @param resource|Image $tile What image to use as the tile
	 * @return bool Return is the same return of {@link http://www.php.net/imagesettile imagesettile()} 
	 **/
	public function setTile(Image $tile)
	{
		$tile_r = self::obj_resource($tile);
		return imagesettile($this->img_r, $tile_r);
	}
	
	/**
	 * Draw a string on the image using an internal GD font (or otherwise loaded)
	 *
	 * @access public
	 * @param int $font_num The font number to use to write the string
	 * @param int $x The x-coordinate of the start of the string
	 * @param int $y The y-coordinate of the start of the string
	 * @param strin $string The string to be written to the image
	 * @param bool $horz (default = true) Write the string horizontally, or vertically
	 * @return bool Return is the same return of {@link http://www.php.net/imagestring imagestring()} or {@link http://www.php.net/imagestringup imagestringup()}
	 **/
	public function string($font_num, $x, $y, $string, $colour, $horz = true)
	{
		$func = "";
		if(!$horz)
			$func = "up";
		if($font_num > 5)
			throw new GraphcsException("Invalid font number");
		$func = "imagestring".$func;
		return $func($this->img_r, $font_num, $x, $y, $string, $colour);
	}
	
	/**
	 * Convert a true colour image to a palette image. Image:trueColorPalette() is an alias of Image::trueColourPalette()
	 *
	 * @access public
	 * @param bool $dither Should the image be dithered 
	 * @param int $num_colours The maximum number of colours to be copied into the palette
	 * @return bool Return is the same return of {@link http://www.php.net/imagetruecolortopalette imagetruecolortopalette()} 
	 **/
	public function trueColorToPalette($dither, $num_colours)
	{
		return $this->trueColourToPalette($this->img_r, $dither, $num_colours);
	}
	
	/**
	 * Convert a true colour image to a palette image. Image:trueColorPalette() is an alias of Image::trueColourPalette()
	 *
	 * @access public
	 * @param bool $dither Should the image be dithered 
	 * @param int $num_colours The maximum number of colours to be copied into the palette
	 * @return bool Return is the same return of {@link http://www.php.net/imagetruecolortopalette imagetruecolortopalette()} 
	 **/
	public function trueColourToPalette($dither, $num_colours)
	{
		return imagetruecolortopalette($this->img_r, $dither, $num_colours);
	}
	
	/**
	 * Write a string to the image using a TrueType font
	 *
	 * @access public
	 * @param float $size The font number to check the width of
	 * @param float $angle The angle at which to write the font
	 * @param int $start_x The start x-coordinate of the text
	 * @param int $start_y The start y-coordinate of the text
	 * @param int $colour The colour of the text to be written
	 * @param string $fontfile The file path of the font (in system/fonts)
	 * @param string $string The string to be written to the image.
	 * @return bool Return is the same return of {@link http://www.php.net/imagettftext imagettftext()} 
	 **/
	public function ttfText($size, $angle, $start_x, $start_y, $colour, $fontfile, $string)
	{
		$font = self::checkFont($fontfile);
		return imagettftext($this->img_r, $size, $angle, $start_x, $start_y, $colour, $font, $string);
	}
	
	/**
	 * Get the width of the image
	 *
	 * @access public
	 * @return int The width of the image
	 **/
	public function width()
	{
		return imagesx($this->img_r);
	}
	
	/* PRIVATE STATIC */
	
	/**
	 * Ensure the type is a font that can be used with GD
	 *
	 * @access private
	 * @static
	 * @param string $type The image type
	 * @return bool True if image is supported
	 **/
	private static function checkType($type)
	{
		switch($type)
		{
			case "jpg":
				$type = "jpeg";
			case "jpeg":
			case "2wbmp":
			case "wbmp":
			case "gd":
			case "gd2":
			case "png":
			case "gif":
				return true;
				break;
			default:
				throw new GraphicsException("Type not recognized");
		}
	}
	
	/* Private */
	/**
	 * Destroys the image in memory (is called by __destruct() automatically)
	 *
	 * @access private
	 * @return bool Return is the same return of {@link http://www.php.net/imagedestroy imagedestroy()} 
	 **/
	private function destroy()
	{
		return imagedestroy($this->img_r);
	}
	
	/**
	 * Sets the object as a brush or tile
	 *
	 * @access private
	 * @return void
	 **/
	private function isBrush()
	{
		$this->brush = true;
	}
	
	/* Private Member Variables */
	
	/**
	 * The resource to the image
	 *
	 * @access private
	 * @see Image
	 * @var resource
	 **/
	private $img_r;
	
	/**
	 * The ID of the image
	 *
	 * @access private
	 * @see Image
	 * @var string
	 **/
	private $id;
	
	/**
	 * The type of the image
	 *
	 * @access private
	 * @see Image
	 * @var string
	 **/
	private $type;
	
	/**
	 * Wither the image is a brush or tile
	 *
	 * @access private
	 * @see Image
	 * @var bool
	 **/
	private $brush;
	
	/**
	 * The function call to use when dealing with the image (depends on $type)
	 *
	 * @access private
	 * @see Image
	 * @var string
	 **/
	private $func;
}
