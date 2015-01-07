$("#confirm-delete").on("show.bs.modal", function(e) {
	$(this).find(".danger").attr("data-href", "action.php").attr("data-pid", $(e.relatedTarget).data("pid"));
	$(this).find(".warning").attr("data-href", "action.php").attr("data-session", $(e.relatedTarget).data("session"));
})

$(function() {
	$("[data-toggle=\"tooltip\"]").tooltip();
});

/* Mark cache as old */
$(document).on("click", "#clearCache", function() {
	var url = $(this).attr("data-href");

	//Send data
	var posting = $.post(url, {
		markCache: "cache",
	});

	//Put the results in a div
	posting.done(function(data) {
		processData(data);
	}, "json");

	return false;
});

/* Refresh page */
$(document).on("click", "#refresh", function() {
	refresh();
	refreshWait();

	return false;
});

/* Clean */
$(document).on("click", ".clean", function() {
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
$(document).on("click", ".warning", function() {
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
$(document).on("click", ".danger", function() {
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
$(document).on("submit", "#startProcess", function(event) {
	//Stop form from submitting normally
	event.preventDefault();

	//Get values
	var $form = $(this),
		processD = $form.find("select[name='process']").val(),
		startD = $form.find("input[name='start']").val(),
		endD = $form.find("input[name='end']").val(),
		waitD = $form.find("select[name='wait']").val(),
		repeatD = $form.find("input[name='repeat']:checked").val(),
		flowD = $form.find("select[name='flow']").val(),
		dateD = $form.find("input[name='startDate']").val(),

		url = $form.attr("action");

	//Send data
	var posting = $.post(url, {
		process: processD,
		start: startD,
		end: endD,
		wait: waitD,
		repeat: repeatD,
		flow: flowD,
		startDate: dateD
	});

	/* Reset form */
	$("#startProcess").trigger("reset");

	//Put the results in a div
	posting.done(function(data) {
		processData(data);
	}, "json");
});

/* Start master processor */
$(document).on("submit", "#masterProcessor", function(event) {
	//Stop form from submitting normally
	event.preventDefault();

	//Get values
	var $form = $(this),
		type = $form.find("input[name='type']").val(),
		sessionId = $form.find("input[name='sessionId']").val(),
		url = $form.attr("action");

	//Start
	if (type == "start") {
		//Send start data
		var posting = $.post(url, {
			processor: type
		});
	} else if (type == "stop") {
		//Send stop data
		var posting = $.post(url, {
			stop: sessionId,
		});
	}

	/* Reset form */
	$("#masterProcessor").trigger("reset");

	//Put the results in a div
	posting.done(function(data) {
		processData(data);
	}, "json");
});

/* Reload data */
function refresh() {
	$("#list").fadeOut("fast", function() {
		$(this).empty().append("<tr><td colspan=\"7\">Loading...</td></tr>").fadeIn("fast");
	});

	//Send data
	var posting = $.post("action.php", {
		refresh: "process",
		type: "process"
	});

	//Put the results in the table
	posting.done(function(data) {
		$("#list").fadeOut("fast", function() {
			$(this).empty().html(data).fadeIn("fast");

			//We have to bind it again!
			$(function() {
				$("[data-toggle=\"tooltip\"]").tooltip();
			});
		});
	});
}

/* Reload 'wait for' processes */
function refreshWait() {
	$("#wait").fadeOut("fast", function() {
		$(this).empty().append("<option value=\"0\">Loading...</option>").fadeIn("fast");
	});

	//Send data
	var posting = $.post("action.php", {
		refresh: "process",
		type: "waitFor"
	});

	//Put the results in the table
	posting.done(function(data) {
		$("#wait").fadeOut("fast", function() {
			$(this).empty().html(data).fadeIn("fast");

			//We have to bind it again!
			$(function() {
				$("[data-toggle=\"tooltip\"]").tooltip();
			});
		});
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
				refreshWait();
			});
		});
	});
}

jQuery("#startDate").datetimepicker({
	format: 'Y-m-d H:i:s',
	mask: true
});