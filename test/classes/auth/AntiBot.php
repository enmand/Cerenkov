<?php
/**
 * AntiBot.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/auth
 */

class AntiBot
{
	private $text = '';
	private $Image;
	public function create($letters = 6, $width = 114, $height = 40, $font = "arial.ttf")
	{
		global $Session;
		srand(time());
		$colour = array();
		$ellipse = array();
		for($i = 0; $i < $letters; $i++)
		{
			$r1 = chr(rand(65, 90));
			$r2 = chr(rand(97, 122));
			$this->text .= (rand(0, 1) == 1) ? $r1 : $r2;
		}
		$Session->set('captcha', $this->text);
		$this->Image = new Image($this->text, 'png');
		$this->Image->createCanvas($width,$height);
		$this->Image->fill(0, 0, 0xFFFFFF);
		for($i = 0; $i < $letters; $i++)
		{
			$color[$i] = $this->Image->colourAllocate(rand(10, 54), rand(25, 55), rand(100, 160));
			$this->Image->ttfText(15, rand(-10, 10), rand(0, 7)+$i*18, rand(24, 28), $color[$i], $font, $this->text[$i]);
		}
		$noise = rand(1.2*$height*$letters, 1.2*$width*$letters);
		for($i = 0; $i < 1.3*$noise; $i++)
		{
			$ellipse[$i%$letters] = $this->Image->colourAllocate(rand(45 ,80), rand(55, 105), rand(160, 255));
			$this->Image->ellipse(rand(0, $width), rand(0, $height), 1, 1, $ellipse[$i%$letters]);
		}
		$this->Image->createImage();
		return $this->getImage();
	}
	public function getImage()
	{
		return $this->Image->getPath();
	}
	
	public function getText()
	{
		return $this->text;
	}
	
	public function destroy()
	{
		@Image::delete($Session->get("AntiBot_text"), 'png');	
	}
}
