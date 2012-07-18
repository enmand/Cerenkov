<?php

/**
 * RSS.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/xml
 */

class RSSWriter extends XMLWriter
{
	public function __construct($channel_url, $title, $link, $description, $image_url)
	{
		parent::__construct();
		// <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/">
		$this->startElement('rdf:RDF'); // <rdf:RDF>
		$this->addAttribute('xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
		$this->addAttribute('xmlns', 'http://purl.org/rss/1.0/');
		$this->endLastElement();
		$this->startElement('channel'); // <channel>
		$this->addAttribute('rdf:about', $channel_url);
		$this->writeElement('title', $title); // <title>...</title>
		$this->writeElement('link', $link); // <link>...</link>
		$this->writeElement('description', $description); // <description>...</description>
		$this->startElement('image'); // <image>
		$this->addAttribute('rdf:resource', $image_url);
		$this->endLastElement(); // </image>
	}
	
	public function addItemsAssoc($item_array)
	{
		foreach ($item_array as $item) {
			$this->addItem($item['title'], $item['link'], $item['description']);
		}
	}
	
	public function addItems($item_array)
	{
		foreach($item_array as $item)
		{
			$this->addItem($item[0], $item[1], $item[2]);
		}
	}
	
	public function addItem($title, $link, $description)
	{
		$this->startElement('item'); // <item>
		$this->addAttribute('rdf:about', $link);
		$this->writeElement('title', $title); // <title>...</title>
		$this->writeElement('link', $link); // <link>...</link>
		$this->writeElement('description', $description); // <description>...</description>
		$this->endLastElement(); // </item>
	}
	
	public function closeDocument()
	{
		$this->endLastElement(); // </channel>
		$this->endLastElement(); // </rdf:RDF>
	}
	
	public function getRSS()
	{
		return $this->getXML();
	}
}

?>