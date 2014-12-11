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
			<!--/.navbar-collapse -->
		</div>
	</nav>

	<div class="container theme-showcase" style="padding:40px;" role="main">

		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="js/jquery-1.11.1.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="js/bootstrap.min.js"></script>

		<div class="page-header">
			<h1>Film2.0 - Administration!</h1>
		</div>
		
		<div class="jumbotron">
		<if:isSuccess>
			<div class="alert alert-success" role="alert">
				<strong>Well done!</strong> You successfully read this important alert message.
			</div>
			</if:isSuccess>
			<if:isError>
			<div class="alert alert-danger" role="alert">
				<strong>Oh snap!</strong> Change a few things up and try submitting again.
			</div>
			</if:isError>
			<div class="row">
				<div class="col-md-12">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Process</th>
								<th>Progress</th>
								<th>#</th>
							</tr>
						</thead>
						<tbody>
							<loop:processes>
								<tr>
									<td>
										<tag:processes[].process />
									</td>
									<td>
										<div class="progress" style="margin-bottom: 0px;">
											<div style="width: <tag:processes[].progress />%;" aria-valuemax="100" aria-valuemin="0" aria-valuenow="<tag:processes[].progress />" role="progressbar" class="progress-bar <tag:processes[].class />"><span class="sr-only"><tag:processes[].progress />% Complete</span>
											</div>
										</div>
									</td>
									<td><a data-href="admin.php?delete=<tag:processes[].sessionId />" data-toggle="modal" data-target="#confirm-delete" href="#">Stop</a>
									</td>
								</tr>
							</loop:processes>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="myModalLabel">Confirm Stop</h4>
				</div>
				<div class="modal-body">
					<p>You are about to stop this session, this procedure is irreversible.</p>
					<p>Do you want to proceed?</p>
					<p class="debug-url"></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					<a href="#" class="btn btn-danger danger">Delete</a>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		$("#confirm-delete").on("show.bs.modal", function(e) {
			$(this).find(".danger").attr("href", $(e.relatedTarget).data("href"));

			$(".debug-url").html("Delete URL: <strong>" + $(this).find(".danger").attr("href") + "</strong>");
		})
	</script>
</body>

</html>