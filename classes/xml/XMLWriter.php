<?php
/**
 * XML.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/xml
 */

class XMLWriter
{
	private $memory;
	private $elements;

	public function __construct()
	{
		$this->memory = xmlwriter_open_memory();
		xmlwriter_start_document($this->memory, '1.0', 'UTF-8');
		$this->elements = array();
	}

	public function setDTD($type, $spec, $url)
	{
		xmlwriter_start_dtd($this->memory, $type, $spec, $url);
		xmlwriter_end_dtd($this->memory);
	}

	public function startElement($element)
	{
		xmlwriter_start_element($this->memory, $element);
		array_push($this->elements, $element);
	}

	public function addAttribute($attr, $value)
	{
		xmlwriter_write_attribute($this->memory, $attr, $value);
	}

	public function endElement($element)
	{
		xmlwriter_end_element($this->memory, $element);
		array_pop($this->elements);
	}

	public function endLastElement()
	{
		xmlwriter_end_element($this->memory, array_pop($this->elements));
		return count($this->elements);
	}

	public function writeElement($element, $text)
	{
		xmlwriter_write_element($this->memory, $element, $text);
	}

	public function openElementCount()
	{
		return count($this->elements);
	}

	public function endDocument()
	{
		xmlwriter_end_document($this->memory);
	}

	public function getXML()
	{

		return xmlwriter_output_memory($this->memory, false); // Output without flushing buffer
	}

	public function flushBuffer()
	{
		return xmlwriter_flush($this->memory);
	}
}

?>