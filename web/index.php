<?php

/**
* @author Kevin
* @copyright 2014
* @info Web interface
*/

//Debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("bTemplate.php");
require_once("./../functions/sqlQuery.php");

//Template
$bTemplate = new bTemplate();

//Make the connection
Database();

//Default
$pType = "";
$pValue = array();
$query = "";
$url = array();
$whereAnd = "WHERE";

//Genre
if(!empty($_GET["genre"]))
{
	$pType .= "s";
	$pValue[] = $_GET["genre"];
	
	$query .= " INNER JOIN `genres` ON `imdb`.`imdb`=`genres`.`imdb` WHERE `genres`.`genre` = ?";
	
	$url["genre"] = $_GET["genre"];
	
	$whereAnd = "AND";
}

//Search
if(!empty($_GET["title"]))
{
	$pType .= "s";
	$pValue[] = "%".$_GET["title"]."%";
	
	$query .= " ".$whereAnd." `imdb`.`title` LIKE ? ";
	
	$url["title"] = $_GET["title"];
}

//Order
if(!empty($_GET["sort"]))
{
	//SQL safe, sort
	switch ($_GET["sort"]) {
	case "added":
		$sort = "added";
		break;
	case "imdb":
		$sort = "imdb";
		break;
	case "rating":
		$sort = "rating";
		break;
	case "ratio":
		//$sort = ""; //Not yet working!
		break;
	case "release":
		$sort = "release";
		break;
	case "runtime":
		$sort = "runtime";
		break;
	case "title":
		$sort = "title";
		break;
	default:
		$sort = "release";
		break;
	}
	
	//Sort order
	if(isset($_GET["by"]) && $_GET["by"]=="DESC")
	{
		$by = "DESC";
	}
	else
	{
		$by = "ASC";
	}
	
	//SQL Query
	$query .= " ORDER BY `imdb`.`".$sort."` ".$by;
	
	$url["sort"] = $_GET["sort"];
	$url["by"] = $by;
}

//Make list for 'sort'
$sort = $url;
unset($sort["sort"]);

//Make list for genre
$genre = $url;
unset($genre["genre"]);


//Bind parameters
if(count($pValue)>0)
{
	$parameters = array_merge(array($pType),$pValue);
}
else
{
	$parameters = false;
}

//Count rows from MySQL
list($rowCount, $resultRows) = sqlQueryi("SELECT COUNT(*) AS `rows` FROM `imdb` ".$query, $parameters, true);



function humanFilesize($bytes, $decimals = 2) 
{
	$sz = "BKMGTP";
	$factor = floor((strlen($bytes) - 1) / 3);
	return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}

//Generate URL
function createURL($type,$sort,$textG,$get,$extra,$upDown=true)
{
	global $bTemplate;
	
	//Check if there are any parameters
	if(count($sort)>0)
	{
		$url = $sort;
		
		if(isset($_GET[$get]) && $_GET[$get]==$type)
		{
			//No sort
			if(!$upDown)
			{
				$text = $textG;
				$class = "class=\"active\"";
			}
			//Sort
			elseif(isset($sort["by"]) && $sort["by"]=="ASC")
			{
				$url["by"]="DESC";
				$text = $textG." &#8595;";
				$class = "class=\"active\"";
			}
			elseif(isset($sort["by"]) && $sort["by"]=="DESC")
			{
				$url["by"]="ASC";
				$text = $textG." &#8593;";
				$class = "class=\"active\"";
			}
		}
		else
		{
			$text = $textG;
			$class = "";
		}
		
		$url = "?".http_build_query($url)."&".$get."=".$type;
	}
	else
	{
		$url = "?".$get."=".$type;
		$text = $textG;
		$class = "";
	}
	
	//Template
	$bTemplate->set("url".$extra, $url);
	$bTemplate->set("text".$extra, $text);
	$bTemplate->set("class".$extra, $class);
}

//Create URL for sorting
createURL("added",$sort,"Added","sort","SA");
createURL("imdb",$sort,"IMDB","sort","SI");
createURL("rating",$sort,"Rating","sort","SRa");
createURL("release",$sort,"Release","sort","SRe");
createURL("runtime",$sort,"Runtime","sort","SRu");
createURL("title",$sort,"Title","sort","ST");

//Create URL for genres
createURL("action",$genre,"Action","genre","GAc",false);
createURL("adventure",$genre,"Adventure","genre","GAd",false);
createURL("comedy",$genre,"Comedy","genre","GCo",false);
createURL("crime",$genre,"Crime","genre","GCr",false);
createURL("documentary",$genre,"Documentary","genre","GDo",false);
createURL("drama",$genre,"Drama","genre","GDr",false);
createURL("family",$genre,"Family","genre","GFam",false);
createURL("fantasy",$genre,"Fantasy","genre","GFan",false);
createURL("history",$genre,"History","genre","GHi",false);
createURL("horror",$genre,"Horror","genre","GHo",false);
createURL("music",$genre,"Music","genre","GMusic",false);
createURL("musical",$genre,"Musical","genre","GMusica",false);
createURL("mystery",$genre,"Mystery","genre","GMy",false);
createURL("romance",$genre,"Romance","genre","GR",false);
createURL("sci-Fi",$genre,"Sci-Fi","genre","GSc",false);
createURL("sport",$genre,"Sport","genre","GSp",false);
createURL("thriller",$genre,"Thriller","genre","GT",false);
createURL("war",$genre,"War","genre","GWa",false);
createURL("western",$genre,"Western","genre","GWe",false);

//Calculate how many movies
$movieCount = ceil($resultRows[0]["rows"]/6);

//Limit total count to 60, for now..
if($movieCount>10)
{		
	$movieCount = 10;
}

$total = array();

for($i=0;$i<=$movieCount;$i++)
{
	//Get movies
	list($rowCount, $result) = sqlQueryi("SELECT * FROM `imdb` ".$query." LIMIT ".($i*6).",6", $parameters, true);
	
	foreach($result as $key=>$fetch)
	{
		//Title too long
		if(strlen($fetch["title"])>18)
		{
			$title = substr($fetch["title"], 0, 16)."..";
		}
		//Don't change
		else
		{
			$title = $fetch["title"];
		}
		
		//Get all genres
		list($rowCountG, $resultG) = sqlQueryi("SELECT `genre` FROM `genres` WHERE `imdb` = ?", array("s",$fetch["imdb"]), true);
		
		$genres = "";
		
		foreach($resultG as $key=>$value)
		{		
			$genres .= $value["genre"].", ";
		}
		
		//Get all torrents
		list($rowCountT, $resultT) = sqlQueryi("SELECT * FROM `data` WHERE `imdb` = ?", array("s",$fetch["imdb"]), true);
		
		$torrents = array();
		
		foreach($resultT as $key=>$valueT)
		{	
			//Get all trackers
			list($rowCountTr, $resultTr) = sqlQueryi("SELECT * FROM `trackers` WHERE `hash` = ?", array("s",$valueT["hash"]), true);
			
			$avg = "";
			$iT=0;
			$leechers = "";
			$trackersURL = "magnet:?xt=urn:btih:".$valueT["hash"]."&dn=".urlencode($fetch["title"]);
			
			foreach($resultTr as $key=>$value)
			{
				if($value["seeders"]>0 && $value["leechers"]>0)
				{
					$avg += $value["seeders"]/$value["leechers"];
					
					$iT++;
					
					
				}
				
				$trackersURL .= "&tr=".urlencode($value["tracker"]);
			}
					
			//Calculate state
			if(($avg/$iT)>2)
			{
				$state="Good";
			}
			elseif(($avg/$iT)>=1)
			{
				$state="OK";
			}
			else
			{
				$state = "Bad";
			}
			
			$torrents[] = array("url"=>$trackersURL,"size"=> humanFilesize($valueT["size"]), "quality"=>$valueT["quality"], "state"=>$state);
			
			
		}
		
		$total[] = array("imdb"=> $fetch["imdb"], "title" => $title, "titleOriginal" => $fetch["title"], "date" => "(".date("Y",strtotime($fetch["release"])).")", "description" => $fetch["description"], "runtime" => $fetch["runtime"], "genres" => $genres, "rating" => $fetch["rating"], "torrents"=>$torrents);
	}
}

$bTemplate->set("movies", $total);

//Print the template!
print($bTemplate->fetch("index.tpl"));