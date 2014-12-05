<?php

/**
* @author Kevin
* @copyright 2014
* @info Web interface
*/

//Debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

require("bTemplate.php");
require("./../functions/sqlQuery.php");

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

list($rowCount, $resultRows) = sqlQueryi("SELECT COUNT(*) AS `rows` FROM `imdb` ".$query, $parameters, true);

///////////////////////////////////////
///////////////////////////////////////

function createURL($type,$sort,$textG,$get,$upDown=true)
{
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
	
	return array($url,$text,$class);
}

///////////////////////////////////////
///////////////////////////////////////


var_dump($_GET); //Otherwise the navbar is 'in the way'
var_dump($_GET);

var_dump($_SERVER["QUERY_STRING"]);
var_dump($rowCount);

var_dump("SELECT COUNT(*) AS `rows` FROM `imdb` ".$query);

var_dump($url);

?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Film2.0 - Just watch!</title>

<!-- Bootstrap -->
<link href="css/bootstrap.min.css" rel="stylesheet">

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>

<body>

	<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="./">Home</a>
			</div>
			<div id="navbar" class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
					<li class="active"><a href="#">Populair</a></li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Sort <span class="caret"></span></a>
						<ul class="dropdown-menu" role="menu">
							<?php 
							
							list($urlSA, $textSA, $classSA) = createURL("added",$sort,"Added","sort");
							list($urlSI, $textSI, $classSI) = createURL("imdb",$sort,"IMDB","sort");
							list($urlSRa, $textSRa, $classSRa) = createURL("rating",$sort,"Rating","sort");
							list($urlSRe, $textSRe, $classSRe) = createURL("release",$sort,"Release","sort");
							list($urlSRu, $textSRu, $classSRu) = createURL("runtime",$sort,"Runtime","sort");
							list($urlST, $textST, $classST) = createURL("title",$sort,"Title","sort");
							
							print("<li ".$classSA."><a href=\"".$urlSA."\">".$textSA."</a></li>");
							print("<li ".$classSI."><a href=\"".$urlSI."\">".$textSI."</a></li>");
							print("<li ".$classSRa."><a href=\"".$urlSRa."\">".$textSRa."</a></li>");
							print("<li><a href=\"#\">Ratio</a></li>");
							print("<li ".$classSRe."><a href=\"".$urlSRe."\">".$textSRe."</a></li>");
							print("<li ".$classSRu."><a href=\"".$urlSRu."\">".$textSRu."</a></li>");
							print("<li ".$classST."><a href=\"".$urlST."\">".$textST."</a></li>");
							
							?>
						</ul>
					</li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Genre <span class="caret"></span></a>
						<ul class="dropdown-menu" role="menu">
							<?php 
							
							list($urlGAc, $textGAc, $classGAc) = createURL("action",$genre,"Action","genre",false);
							list($urlGAd, $textGAd, $classGAd) = createURL("adventure",$genre,"Adventure","genre",false);
							list($urlGCo, $textGCo, $classGCo) = createURL("comedy",$genre,"Comedy","genre",false);
							list($urlGCr, $textGCr, $classGCr) = createURL("crime",$genre,"Crime","genre",false);
							list($urlGDo, $textGDo, $classGDo) = createURL("documentary",$genre,"Documentary","genre",false);
							list($urlGDr, $textGDr, $classGDr) = createURL("drama",$genre,"Drama","genre",false);
							list($urlGFam, $textGFam, $classGFam) = createURL("family",$genre,"Family","genre",false);
							list($urlGFan, $textGFan, $classGFan) = createURL("fantasy",$genre,"Fantasy","genre",false);
							list($urlGHi, $textGHi, $classGHi) = createURL("history",$genre,"History","genre",false);
							list($urlGHo, $textGHo, $classGHo) = createURL("horror",$genre,"Horror","genre",false);
							list($urlGMusic, $textGMusic, $classGMusic) = createURL("music",$genre,"Music","genre",false);
							list($urlGMusica, $textGMusica, $classGMusica) = createURL("musical",$genre,"Musical","genre",false);
							list($urlGMy, $textGMy, $classGMy) = createURL("mystery",$genre,"Mystery","genre",false);
							list($urlGR, $textGR, $classGR) = createURL("romance",$genre,"Romance","genre",false);
							list($urlGSc, $textGSc, $classGSc) = createURL("sci-Fi",$genre,"Sci-Fi","genre",false);
							list($urlGSp, $textGSp, $classGSp) = createURL("sport",$genre,"Sport","genre",false);
							list($urlGT, $textGT, $classGT) = createURL("thriller",$genre,"Thriller","genre",false);
							list($urlGWa, $textGWa, $classGWa) = createURL("war",$genre,"War","genre",false);
							list($urlGWe, $textGWe, $classGWe) = createURL("western",$genre,"Western","genre",false);
							
							print("<li ".$classGAc."><a href=\"".$urlGAc."\">".$textGAc."</a></li>");
							print("<li ".$classGAd."><a href=\"".$urlGAd."\">".$textGAd."</a></li>");
							print("<li ".$classGCo."><a href=\"".$urlGCo."\">".$textGCo."</a></li>");
							print("<li ".$classGCr."><a href=\"".$urlGCr."\">".$textGCr."</a></li>");
							print("<li ".$classGDo."><a href=\"".$urlGDo."\">".$textGDo."</a></li>");
							print("<li ".$classGDr."><a href=\"".$urlGDr."\">".$textGDr."</a></li>");
							print("<li ".$classGFam."><a href=\"".$urlGFam."\">".$textGFam."</a></li>");
							print("<li ".$classGFan."><a href=\"".$urlGFan."\">".$textGFan."</a></li>");
							print("<li ".$classGHi."><a href=\"".$urlGHi."\">".$textGHi."</a></li>");
							print("<li ".$classGHo."><a href=\"".$urlGHo."\">".$textGHo."</a></li>");
							print("<li ".$classGMusic."><a href=\"".$urlGMusic."\">".$textGMusic."</a></li>");
							print("<li ".$classGMusica."><a href=\"".$urlGMusica."\">".$textGMusica."</a></li>");
							print("<li ".$classGMy."><a href=\"".$urlGMy."\">".$textGMy."</a></li>");
							print("<li ".$classGR."><a href=\"".$urlGR."\">".$textGR."</a></li>");
							print("<li ".$classGSc."><a href=\"".$urlGSc."\">".$textGSc."</a></li>");
							print("<li ".$classGSp."><a href=\"".$urlGSp."\">".$textGSp."</a></li>");
							print("<li ".$classGT."><a href=\"".$urlGT."\">".$textGT."</a></li>");
							print("<li ".$classGWa."><a href=\"".$urlGWa."\">".$textGWa."</a></li>");
							print("<li ".$classGWe."><a href=\"".$urlGWe."\">".$textGWe."</a></li>");
							
							?>
						</ul>
					</li>
					<li><a href="#">Admin</a></li>
				</ul>
				<form class="navbar-form navbar-right" role="form" method="get" action="">
					<div class="form-group">
						<input type="text" name="title" placeholder="Movie title" class="form-control">
					</div>
					<button type="submit" class="btn btn-success">Search</button>
				</form>
			</div>
			<!--/.navbar-collapse -->
		</div>
	</nav>

	<div class="container theme-showcase" style="padding:40px;" role="main">

		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="js/jquery-1.11.1.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="js/bootstrap.min.js"></script>
		<!-- FitText, needed for responsive titles -->
		<script src="js/jquery.fittext.js"></script>

		<div class="page-header">
			<h1>Film2.0 - Just watch!</h1>
		</div>
	
		<div class="row">

		<?php

		//Calculate how many movies
		$movieCount = ceil($resultRows[0]["rows"]/6);

		//Limit total count to 60, for now..
		if($movieCount>10)
		{		
			$movieCount = 10;
		}

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

				print("<div class=\"col-xs-6 col-sm-4 col-md-3 col-lg-2\">");
				print("<div class=\"panel panel-default\">");
				print("<div class=\"panel-heading\" style=\"width: 100%\">");
				print("<h6 id=\"fitText-".$fetch["imdb"]."\" data-toggle=\"modal\" data-target=\"#myModal2\" class=\"panel-title\" title=\"".$fetch["title"]." (".date("Y",strtotime($fetch["release"])).")\" style=\"font-size: 0.8em;cursor:pointer;\">".$title."</h6>");
				print("</div>");
				print("<div class=\"panel-body\" title=\"".$fetch["title"]." (".date("Y",strtotime($fetch["release"])).")\" style=\"background: url('./../poster/".$fetch["imdb"].".jpg') no-repeat center; height: 250px\">");
				print("</div>");
				print("</div>");
				print("</div>");
		
				print("<script type=\"text/javascript\">$(\"#fitText-".$fetch["imdb"]."\").fitText();</script>");
			}
		} 

		?>
		</div>
	</div>

	

////////////////

<!-- Modal -->
<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title" id="myModalLabel">The Believers *Status/Levend?*</h4>
			</div>
			<div class="modal-body">
				1987-11-26, 114 min, 6*, Horror,<br />
				After the death of his wife, police psychiatrist Cal Jamison moves to New York. There he has to help in the investigation of the murder of two youths, who seem to have been immolated during a cult ritual. Jamison believes it's been Voodoo and, ignoring the warnings of his housekeeper, enters the scenery and soon gets under their influence. They try to get him to sacrifice his own son.

				<div class="row">
					<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 ">
						<table class="table table-condensed" style="font-size: 0.7em">
							<thead>
								<tr>
									<th>Tracker</th>
									<th>Seeders</th>
									<th>Leechers</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>1</td>
									<td>Mark</td>
									<td>Otto</td>
								</tr>
								<tr>
									<td>2</td>
									<td>Jacob</td>
									<td>Thornton</td>
								</tr>
								<tr>
									<td>3</td>
									<td>Larry</td>
									<td>the Bird</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

</body>

</html>