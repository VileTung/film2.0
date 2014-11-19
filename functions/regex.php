<?php

/**
* @author Kevin
* @copyright 2014
* @info Regex
*/

//Regex functions we need
class regex
{
	//Simple function
	public function match($regex,$string,$i=null,$ii=0)
	{
		preg_match_all($regex, $string, $matches);
		
		if($i!=null)
		{
			return (isset($matches[$i][$ii]) ? $matches[$i][$ii]:false);
		}
		else
		{
			return $matches;
		}
	}
	
	//Main
	public function main($regex,$string,$i=null,$ii=0)
	{
		$return["year"] = "~(\b(19\d{2}|20[01]\d)\b)~i";
		
		return self::match($return[$regex],$string,$i,$ii);
	}
	
	//IMDB
	public function imdb($regex,$string,$i=null,$ii=0)
	{
		//$return[""] = "";
		
		return self::match($return[$regex],$string,$i,$ii);
	}
	
}

?>