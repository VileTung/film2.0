<?php

/**
* @author Kevin
* @copyright 2014
* @info Web interface
*/

require("./../functions/sqlQuery.php");

//Make the connection
Database();

list($rowCount, $resultRows) = sqlQueryi("SELECT COUNT(*) AS rows FROM imdb", false, true);

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
				<a class="navbar-brand" href="#">Home</a>
			</div>
			<div id="navbar" class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
					<li class="active"><a href="#">Populair</a></li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Sort <span class="caret"></span></a>
						<ul class="dropdown-menu" role="menu">
							<li><a href="#">Added</a></li>
							<li><a href="#">IMDB</a></li>
							<li><a href="#">Rating</a></li>
							<li><a href="#">Ratio</a></li>
							<li><a href="#">Release</a></li>
							<li><a href="#">Runtime</a></li>
							<li><a href="#">Title</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Genre <span class="caret"></span></a>
						<ul class="dropdown-menu" role="menu">
							<li><a href="#">Action</a></li>
							<li><a href="#">Adventure</a></li>
							<li><a href="#">Comedy</a></li>
							<li><a href="#">Crime</a></li>
							<li><a href="#">Documentary</a></li>
							<li><a href="#">Drama</a></li>
							<li><a href="#">Family</a></li>
							<li><a href="#">Fantasy</a></li>
							<li><a href="#">History</a></li>
							<li><a href="#">Horror</a></li>
							<li><a href="#">Music</a></li>
							<li><a href="#">Musical</a></li>
							<li><a href="#">Mystery</a></li>
							<li><a href="#">Romance</a></li>
							<li><a href="#">Sci-Fi</a></li>
							<li><a href="#">Sport</a></li>
							<li><a href="#">Thriller</a></li>
							<li><a href="#">War</a></li>
							<li><a href="#">Western</a></li>
						</ul>
					</li>
					<li><a href="#">Admin</a></li>
				</ul>
				<form class="navbar-form navbar-right" role="form">
					<div class="form-group">
						<input type="text" placeholder="Movie title" class="form-control">
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
			list($rowCount, $result) = sqlQueryi("SELECT * FROM imdb LIMIT ".($i*6).",6", false, true);
			
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
							print("<h6 id=\"fitText-".$fetch["imdb"]."\" class=\"panel-title\" title=\"".$fetch["title"]." (".date("Y",strtotime($fetch["release"])).")\" style=\"font-size: 0.8em;\">".$title."</h6>");
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
</body>

</html>