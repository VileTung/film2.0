<?php

/**
* @author Kevin
* @copyright 2014
* @info Logging
*/

class loggen
{
	//Where do we need to place the log file?
	private $filename;
	
	//Character between values (split values)
	private $separator = ",";
	
	//Starting headers
	private $header = 'DATETIME,ERRORLEVEL,TAG,VALUE,LINE,FILE';
	
	public function __construct($file) 
	{
		$this->filename = $file;
	}
	
	
}