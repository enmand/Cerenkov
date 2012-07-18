<?php
/**
 * ImageInterface.php
 *
 * For information on the Image class, please see the Image documentation.
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/graphical
 */

interface ImageInterface
{
	/* PUBLIC STATIC */
	public static function checkFont($fontfile);
	public static function delete($name, $type);
	public static function gdInfo($show = false);
	public static function generateID($IDParams);
	public static function isSupported($type);
	public static function obj_resource($src_img);
	public static function psBounding($string, $font, $size, $spacing = NULL, $char_spacing = NULL, $angle = NULL);
	public static function ttfBounding($size, $angle, $fontfile, $string);
	
	
	/* PUBLIC */
	public function __construct($name, $type);
	public function __destruct();
	public function antialias($onoff);
	public function arc($centre_x, $centre_y, $width, $height, $angle_start, $angle_end, $colour, $filled = false, $style = IMG_SRC_NOFILL);
	public function brushThickness($thickness);
	public function char($font_num, $x, $y, $string, $colour, $horz = true);
	public function colorAllocate($red, $green, $blue);
	public function colourAllocate($red, $green, $blue);
	public function colorAllocateAlpha($red, $green, $blue, $alpha);
	public function colourAllocateAlpha($red, $green, $blue, $alpha);
	public function colorAt($x, $y);
	public function colourAt($x, $y);
	public function colorClosest($red, $green, $blue);
	public function colourClosest($red, $green, $blue);
	public function colorClosestAlpha($red, $green, $blue, $alpha);
	public function colourClosestAlpha($red, $green, $blue, $alpha);
	public function colorClosestHBW($red, $green, $blue);
	public function colourClosestHBW($red, $green, $blue);
	public function colorDeallocate($colour);
	public function colourDeallocate($colour);
	public function colorExact($red, $green, $blue);
	public function colourExact($red, $green, $blue);
	public function colorExactAlpha($red, $green, $blue, $alpha);
	public function colourExactAlpha($red, $green, $blue, $alpha);
	public function colorMatch(Image $img2);
	public function colourMatch(Image $img2);
	public function colorResolve($red, $green, $blue);
	public function colourResolve($red, $green, $blue);
	public function colorResolveAlpha($red, $green, $blue, $alpha);
	public function colourResolveAlpha($red, $green, $blue, $alpha);
	public function colorSet($colour, $red, $green, $blue);
	public function colourSet($colour, $red, $green, $blue);
	public function colorsForIndex($index);
	public function coloursForIndex($index);
	public function colorsTotal();
	public function coloursTotal();
	public function colorTransparent($colour);
	public function colourTransparent($colour);
	public function convolution(array $matrix, $div, $offset);	
	public function copy($src_img, $dest_x, $dest_y, $src_x, $src_y, $src_w, $src_h, $percent = NULL);
	public function createBrush($brush_w, $brush_h);
	public function createCanvas($width, $height, $trueColour = true);
	public function createImage();
	public function createTile($tile_w, $tile_h);
	public function createFrom($filename, $type);
	public function ellipse($centre_x, $centre_y, $width, $height, $colour, $filled = false);
	public function fill($x, $y, $colour);
	public function fillToBorder($x, $y, $border, $colour);
	public function filter($filtertype, $red = NULL, $green = NULL, $blue = NULL, $alpha = NULL);
	public function fontWidth($font_num);
	public function fontHeight($font_num);
	public function ftText($size, $angle, $start_x, $start_y, $colour, $fontfile, $string);
	public function gammacorrect($inputgamma, $outputgamma);
	public function getID();
	public function getPath();
	public function getResource();
	public function height();
	public function isTrueColor();
	public function isTrueColour();
	public function interlace($interlace = 0);
	public function layerEffect($effect);
	public function line($x1, $y1, $x2, $y2, $colour);
	public function loadFont($filename);
	public function paletteCopy($src_img);
	public function pixel($x, $y, $colour);
	public function polygon(array $points, $colour, $filled = false);
	public function psEncodeFont($font, $fname);
	public function psExtendFont($font, $ratio);
	public function psFreeFont($font_r);
	public function psLoadFont($fontfile);
	public function psSlantFont($font, $slant);
	public function psText($string, $font, $size, $f_colour, $b_colour, $start_x, $start_y, $spacing = NULL, $char_spacing = NULL, $angle = NULL, $antialias = NULL);
	public function rectangle($top_left_x, $top_right_y, $bottom_righr_x, $bottom_right_y, $colour, $filled = false);
	public function resize($src_img, $dest_x, $dest_y, $src_x, $src_y, $dest_w, $dest_h, $src_w, $src_h, $interpol = true);
	public function rotate($angle, $bgd_color ,$ignore_transparent);
	public function saveAlpha($onoff);
	public function setBrush(Image $brush);
	public function setStyle(array $style);
	public function setTile(Image $tile);
	public function string($font_num, $x, $y, $string, $colour, $horz = true);
	public function ttfText($size, $angle, $start_x, $start_y, $colour, $fontfile, $string);
	public function width();
}
?>