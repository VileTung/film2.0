<?php

/**
* @author Kevin
* @copyright 2014
* @info Subtitles
*/

//http://www.yifysubtitles.com/movie-imdb/tt1646971

class subtitle
{
	//Set IMDB
	private $imdb;
	
	public function __construct($id)
	{
		//Need to add some '0'
		$this->imdb = sprintf("%07d", $id);
	}
	
	public function yify()
	{
		//Get page
		list($state, $content) = cURL("http://yifysubtitles.com/movie-imdb/tt".$this->imdb);
		
		//Failed
		if (!$state)
		{
			throw new Exception("Invalid YIFY subtitle-url (" . $this->imdb . ")");
		}
		
		//Regex
		$regex = new regex;
		
		//Extract info
		$data = $regex->main("yify",$content);
		
		//Default
		$nl = array();
		$en = array();
		
		//Loop
		foreach($data[3] as $key=>$language)
		{			
			//Dutch
			if(strtolower($language)=="dutch" && strpos($data[0][$key],"verified") !== false)
			{				
				$nl[] = array("id"=>$data[1][$key],"votes"=>$data[2][$key],"url"=>$data[4][$key]);
			}
			//English
			elseif(strtolower($language)=="english" && strpos($data[0][$key],"verified") !== false)
			{
				$en[] = array("id"=>$data[1][$key],"votes"=>$data[2][$key],"url"=>$data[4][$key]);
			}
		}
		
		//Dutch
		if(count($nl)>0)
		{
			$nl = max($nl);
			
			//Construct URL
			$url = "http://yifysubtitles.com/".str_replace("subtitles","subtitle",$nl["url"]).".zip";
			
			//Retrieve subtitle
			self::saveSubtitle($url);
			
		}
		//English
		elseif(count($en)>0)
		{
			$en = max($en);
			
			//Construct URL
			$url = "http://yifysubtitles.com/".str_replace("subtitles","subtitle",$en["url"]).".zip";
			
			//Retrieve subtitle
			self::saveSubtitle($url);
		}
		//Failed
		else
		{
			print("FAILED\n");
		}
	}
	
	private function saveSubtitle($url)
	{
		global $cache, $subtitle;
		
		//Download subtitle
		list($state, $content) = cURL($url);
		
		//Failed
		if (!$state)
		{
			throw new Exception("Subtitle download failed (YIFY - " . $url . ")");
		}
		
		//Save subtitle
		$file = fopen($cache.$this->imdb.".zip","x");
		fwrite($file, $content);
		fclose($file);
		
		//Unzip
		// assuming file.zip is in the same directory as the executing script.
		$file = $cache.$this->imdb.".zip";

		// get the absolute path to $file
		$path = pathinfo(realpath($file), PATHINFO_DIRNAME)."/".$this->imdb;

		var_dump($path);
		
		$zip = new ZipArchive;
		$res = $zip->open($file);
		if ($res === TRUE) {
			// extract it to the path we determined above
			$zip->extractTo($path);
			$zip->close();
			echo "WOOT! $file extracted to $path";
		} else {
			echo "Doh! I couldn't open $file";
		}
		
		//Move to subtitle dir
		
		//Remove cache
		rmdir($path);
		unlink($file);
		
	}
}

?>