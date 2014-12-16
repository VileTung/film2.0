$("#confirm-delete").on("show.bs.modal", function(e) {
	$(this).find(".danger").attr("data-href", "action.php").attr("data-pid", $(e.relatedTarget).data("pid"));
	$(this).find(".warning").attr("data-href", "action.php").attr("data-session", $(e.relatedTarget).data("session"));
})

$(function() {
	$("[data-toggle=\"tooltip\"]").tooltip();
});

/* Clean */
$(document).on("click",".clean", function() {
	var sessionId = $(this).attr("data-session");
	var url = $(this).attr("data-href");

	//Send data
	var posting = $.post(url, {
		clean: sessionId,
	});

	//Put the results in a div
	posting.done(function(data) {
		processData(data);
	}, "json");

	return false;
});

/* Stop */
$(".warning").click(function() {
	var sessionId = $(this).attr("data-session");
	var url = $(this).attr("data-href");

	//Send data
	var posting = $.post(url, {
		stop: sessionId,
	});

	//Put the results in a div
	posting.done(function(data) {
		processData(data);
	}, "json");

	$("#confirm-delete").modal("toggle");

	return false;
});

/* Kill */
$(".danger").click(function() {
	var pid = $(this).attr("data-pid");
	var url = $(this).attr("data-href");

	//Send data
	var posting = $.post(url, {
		kill: pid,
	});

	//Put the results in a div
	posting.done(function(data) {
		processData(data);
	}, "json");

	$("#confirm-delete").modal("toggle");

	return false;
});

/* Start */
$("#startProcess").submit(function(event) {
	//Stop form from submitting normally
	event.preventDefault();

	//Get values
	var $form = $(this),
		processD = $form.find("select[name='process']").val(),
		startD = $form.find("input[name='start']").val(),
		endD = $form.find("input[name='end']").val(),
		url = $form.attr("action");
	
	console.log(processD+ " " +startD+ " " + endD);

	//Send data
	var posting = $.post(url, {
		process: processD,
		start: startD,
		end: endD
	});

	//Put the results in a div
	posting.done(function(data) {
		processData(data);
	}, "json");
});

/* Reload data */
function refresh() {
	$("#list").empty().append("<span>Loading...</span>");

	//Send data
	var posting = $.post("action.php", {
		refresh: "process"
	});

	//Put the results in the table
	posting.done(function(data) {
		$("#list").empty().html(data);
	});
}

function processData(data) {
	//Retuned message in div
	$("#message").empty().append(data.message);
	$("#message").removeClass("alert-danger alert-success").addClass(data.state);

	//Show message, hide page
	$("#page").fadeOut("slow", function() {
		$("#message").fadeIn("slow", function() {
			$("#message").delay(1000).fadeOut("slow", function() {
				$("#page").fadeIn("slow");
				refresh();
			});

		});

	});
}