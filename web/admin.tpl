<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Film2.0 - Just watch!</title>

	<link rel="icon" href="images/icon.png">

	<!-- Bootstrap -->
	<link href="css/bootstrap.min.css" rel="stylesheet">

	<!-- jQuery Datetime picker -->
	<link href="css/jquery.datetimepicker.css" rel="stylesheet">


	<!-- Extra -->
	<link href="css/table.min.css" rel="stylesheet">

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
					<li><a href="admin.php">Admin</a>
					</li>
					<li><a id="refresh" href="#">Refresh</a>
					</li>
					<li><a id="clearCache" data-href="action.php" href="#">Clear Cache</a>
					</li>
				</ul>
			</div>
			<!--/.navbar-collapse -->
		</div>
	</nav>

	<div class="container theme-showcase" style="padding:40px;" role="main">

		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="js/jquery-1.11.1.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="js/bootstrap.min.js"></script>

		<!-- jQuery Datetime picker -->
		<script src="js/jquery.datetimepicker.js"></script>

		<div class="page-header">
			<h1>Film2.0 - Administration!</h1>
		</div>

		<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="myModalLabel">Confirm Stop/Kill</h4>
					</div>
					<div class="modal-body">
						<p>You are about to stop or kill this session, this procedure is irreversible.</p>
						<p>Stop is a friendly way of stopping the process, while kill is not so friendly.</p>
						<p>Do you want to proceed?</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<a href="#" class="btn btn-warning warning">Stop</a>
						<a href="#" class="btn btn-danger danger">Kill</a>
					</div>
				</div>
			</div>
		</div>

		<div class="jumbotron">
			<div id="message" class="alert" style="display: none;" role="alert">
				Default text
			</div>
			<div id="page">
                <form role="form" method="post" action="action.php" id="masterProcessor">
				<div class="alert alert-<tag:sanity.class />">
					<span>The process is currently: </span><span class="label label-<tag:sanity.class />"><tag:sanity.state /></span>
					<br />
					<span><tag:sanity.text /></span>
					<br />
					<br />
                    <input type="hidden" name="type" value="<tag:sanity.type />">
                    <input type="hidden" name="sessionId" value="<tag:sanity.sessionId />">
					<button type="submit" class="btn btn-default" <tag:sanity.start />><span class="glyphicon glyphicon-play" aria-hidden="true"></span> Start</button>
					<button type="submit" class="btn btn-default" <tag:sanity.stop />><span class="glyphicon glyphicon-stop" aria-hidden="true"></span> Stop</button>
				</div>
                </form>
				<form role="form" method="post" action="action.php" id="startProcess">
					<div class="form-group">
						<label for="process">Process:</label>
						<select name="process" class="form-control" id="process">
							<option value="YTS">YTS</option>
						</select>
					</div>
					<div class="form-group">
						<label for="start">Start at page:</label>
						<input type="number" name="start" class="form-control" id="start" placeholder="Start" required="true">
					</div>
					<div class="form-group">
						<label for="end">Stop at page:</label>
						<input type="number" name="end" class="form-control" id="end" placeholder="End" required="true">
					</div>

					<div class="form-group">
						<label for="wait">Wait for other process to finish:</label>
						<select name="wait" class="form-control" id="wait">
							<option value="0">None</option>
							<loop:waitFor>
								<option value="<tag:waitFor[].id />">
									<tag:waitFor[].value />
								</option>
							</loop:waitFor>
						</select>
					</div>

					<div class="form-group">
						<label for="repeat">Repeat process:</label>
						<br />
						<input type="radio" name="repeat" id="repeatT" value="true" required="true"> Yes
						<br />
						<input type="radio" name="repeat" id="repeatF" value="false" required="true" checked="true"> No
					</div>
					<div class="form-group">
						<label for="flow">Repeat flow:</label>
						<select name="flow" class="form-control" id="flow">
							<option value=""><em>None</em>
							</option>
							<option value="hour">Hourly</option>
							<option value="day">Daily</option>
							<option value="week">Weekly</option>
							<option value="month">Monthly</option>
						</select>
					</div>
					<div class="form-group">
						<label for="startDate">Start date:</label>
						<input type="text" name="startDate" class="form-control" id="startDate" placeholder="Start date" required="true">
					</div>

					<button type="submit" class="btn btn-default">Submit</button>
				</form>
				<br />
				<div class="row">
					<div class="col-md-12">
						<table class="table table-striped">
							<thead>
								<tr>
									<th>Process</th>
									<th>State</th>
									<th>Progress</th>
									<th>Start</th>
									<th>End</th>
									<th>Log</th>
									<th>Kill</th>
								</tr>
							</thead>
							<tbody id="list">
								<loop:processes>
									<tr>
										<td>
											<tag:processes[].process />
										</td>
										<td>
											<span style="color: <tag:processes[].active />"><tag:processes[].state /></span>
										</td>
										<td>
											<div class="progress" style="margin-bottom: 0px;">
												<div data-toggle="tooltip" title="Complete: <tag:processes[].progress />%" style="width: <tag:processes[].progress />%;" aria-valuemax="100" aria-valuemin="0" aria-valuenow="<tag:processes[].progress />" role="progressbar" class="progress-bar <tag:processes[].class />"><span class="sr-only"><tag:processes[].progress />% complete</span>
												</div>
											</div>
										</td>
										<td>
											<tag:processes[].start />
										</td>
										<td>
											<tag:processes[].end />
										</td>
										<td><a target="_blank" href="./../log/<tag:processes[].sessionId />.html">Click</a>
										</td>
										<td>
											<tag:processes[].working />
										</td>
									</tr>
								</loop:processes>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="js/admin.js"></script>
</body>

</html>