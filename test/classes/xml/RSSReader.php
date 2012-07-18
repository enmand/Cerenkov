<?php
/**
 * RSSReader.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/xml
 */
class RSSReader
{
	private $parser;
	private $xmlContents;
	private $xml;
	
	public function __construct($rss, $url = TRUE)
	{
		$this->uri = $rss;
		if($url)
		{
			$rssFile = new File($rss);
			$this->xmlContents = $rssFile->getContents();
		} else
		{
			$this->xmlContents = $rss;
		}
		$this->xml = new SimpleXMLElement($this->xmlContents);
	}
	
	public function getTitle()
	{
		return (string)$this->xml->channel->title;
	}
	
	public function getLink()
	{
		return (string)$this->xml->channel->link;
	}
	
	public function getDescription()
	{
		return (string)$this->xml->channel->description;
	}
	
	public function numItems()
	{
		return count($this->xml->channel->item);
	}
	
	public function getStoryTitle($storyNum)
	{
		if($storyNum > $this->numItems())
			throw new XMLException("Could not get story number " . $storyNum);
		return (string)$this->xml->channel->item[$storyNum]->title;
	}
	
	public function getStoryLink($storyNum)
	{
		if($storyNum > $this->numItems())
			throw new XMLException("Could not get story number " . $storyNum);
		return (string)$this->xml->channel->item[$storyNum]->link;
	}
	
	public function getStoryPubDate($storyNum)
	{
		if($storyNum > $this->numItems())
			throw new XMLException("Could not get story number " . $storyNum);
		return (string)$this->xml->channel->item[$storyNum]->pubDate;
	}
	
	public function getStoryDescription($storyNum)
	{
		if($storyNum > $this->numItems())
			throw new XMLException("Could not get story number " . $storyNum);
		return (string)$this->xml->channel->item[$storyNum]->description;
	}
	
	public function getItems($numItems = NULL)
	{
		if($numItems === NULL) $numItems = $this->numItems();
		if($numItems > $this->numItems()) throw new XMLException("There are not ".$numItems." stories");
		$items = array(array());
		for($i = 0;$i < $numItems; $i++)
		{
			$items[$i]['title'] = $this->getStoryTitle($i);
			$items[$i]['link'] = $this->getStoryLink($i);
			$items[$i]['pubDate'] = $this->getStoryPubDate($i);
			$items[$i]['description'] = $this->getStoryDescription($i);
		}
		return $items;
	}
}