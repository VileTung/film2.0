<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Pager
 */
 
class pager
{
	//Current page
	private $current;
	
	//Total pages
	private $pages;
	
	//URL
	private $url;
	
	
	
	public function __construct($pages,$url)
	{
		//Total pages
		$this->pages = $pages;
		
		//Set current page
		$this->current = (isset($url["page"])?$url["page"]:1);
		
		//Remove page from URL
		unset($url["page"]);
		$this->url = $url;		
	}
	
	public function navigation()
	{
		//Lower by 1
		$this->url["page"]=$this->current-1;
		
		//Previous
		if(($this->current-1)<1)
		{
			//Disabled
			$list[] = array("class"=>"class=\"disabled\"", "url"=>"?".http_build_query($this->url), "text"=>"Previous");
		}
		else
		{
			//Enabled
			$list[] = array("class"=>"", "url"=>"?".http_build_query($this->url), "text"=>"Previous");
		}
		
		//Remove page from URL
		unset($this->url["page"]);
		
		//Set new page and add 1
		$this->url["page"]=$this->current+1;
		
		//Next
		if(($this->current+1)>$this->pages)
		{
			//Disabled
			$list[] = array("class"=>"class=\"disabled\"", "url"=>"?".http_build_query($this->url), "text"=>"Next");
		}
		else
		{
			//Enabled
			$list[] = array("class"=>"", "url"=>"?".http_build_query($this->url), "text"=>"Next");
		}		
		
		return $list;
	}
}