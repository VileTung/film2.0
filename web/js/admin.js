/**
 * @author Kevin
 * @copyright 2015
 * @info Admin AJaX calls
 */
/* Needed for modal */
$("#confirm-delete").on("show.bs.modal", function(e)
{
    $(this).find(".danger").attr("data-pid", $(e.relatedTarget).data("pid"));
    $(this).find(".warning").attr("data-session", $(e.relatedTarget).data("session"));
})

/* Needed for date */
function addZero(i)
{
    if (i < 10)
    {
        i = "0" + i;
    }
    return i;
}

/* Send a POST-request, with a HTML response */
function postDataHTML(identifier, post, fade, fresh)
{
    /* Security check */
    var date = new Date();

    /* Set our post data */
    post["check" + date.getFullYear()] = addZero(date.getDate()) + addZero(date.getMonth() + 1) + date.getFullYear();
    post["return"] = "html";

    /* Show a loading message */
    if (fresh)
    {
        $(identifier).fadeOut("fast", function()
        {
            $(this).empty().html("Loading...").fadeIn("fast");
        });
    }

    /* Send the POST-request */
    $.post("web/action.php", post)
        .done(function(data)
        {
            /* FadeIn & FadeOut effect */
            if (fade)
            {
                $(identifier).fadeOut("slow", function()
                {
                    $(this).empty().html(data).fadeIn("slow");

                    //We have to bind it again!
                    $(function()
                    {
                        $("[data-toggle=\"tooltip\"]").tooltip();
                    });
                });
            }
            /* No effects */
            else
            {
                $(identifier).empty().html(data);

                //We have to bind it again!
                $(function()
                {
                    $("[data-toggle=\"tooltip\"]").tooltip();
                });
            }
        });
}

/* Send a POST-request, with a JSON response */
function postDataJSON(post)
{
    /* Security check */
    var date = new Date();

    /* Set our post data */
    post["check" + date.getFullYear()] = addZero(date.getDate()) + addZero(date.getMonth() + 1) + date.getFullYear();
    post["return"] = "json";

    /* Send the POST-request */
    $.post("web/action.php", post)
        .done(function(data)
        {
            /* Set message */
            $("#message").empty().append(data.message);
            $("#message").removeClass("alert-danger alert-success").addClass(data.state);

            /* Show message */
            $("#message").fadeIn("slow", function()
            {
                $("#message").delay(1000).fadeOut("slow");

                //We have to bind it again!
                $(function()
                {
                    $("[data-toggle=\"tooltip\"]").tooltip();
                });
            });
        }, "json");
}

/* Load default page (movies) */
$(document).ready(function()
{
    /* Get master process state */
    postDataHTML("#masterProcess",
    {
        "type": "adminState"
    }, true, false);

    /* Get session list */
    postDataHTML("#sessionList",
    {
        "type": "adminSessions"
    }, true, false);

    /* Get process list */
    postDataHTML("#processList",
    {
        "type": "adminProcesses"
    }, true, false);
});

/* Start/stop master process */
$(document).on("submit", "#masterStartStop", function(event)
{
    /* Stop form from submitting normally */
    event.preventDefault();

    /* Get data */
    postDataJSON(
    {
        "type": "adminMasterProcess"
    });

    /* Get master process state */
    postDataHTML("#masterProcess",
    {
        "type": "adminState"
    }, true, false);
});

/* Show add process form */
$(document).on("click", "#plusClick", function()
{
    /* Get add process data */
    postDataHTML("#showAdd",
    {
        "type": "adminProcessForm"
    }, false, false);

    $("#showPlus").fadeOut("slow", function()
    {
        $("#showMinus").fadeIn("slow", function()
        {
            $("#showAdd").fadeIn("slow");
        });
    });
});

/* Hide add process form */
$(document).on("click", "#minusClick", function()
{
    $("#showMinus").fadeOut("slow", function()
    {
        $("#showPlus").fadeIn("slow", function()
        {
            $("#showAdd").fadeOut("slow");
        });
    });
});

/* Show extra options on change */
$(document).on("change", "#process", function()
{
    var value = $(this).val();

    /* Get YTS options */
    if (value == "yts")
    {
        postDataHTML("#processOptions",
        {
            "type": "adminOptions",
            "option": "yts"
        }, true, false);
    }
    else
    {
        $("#processOptions").fadeOut("slow", function()
        {
            $(this).empty();
        });
    }
});

/* Add a new process */
$(document).on("submit", "#addProcess", function(event)
{
    /* Stop form from submitting normally */
    event.preventDefault();

    /* Hide form */
    $("#showMinus").fadeOut("slow", function()
    {
        $("#showPlus").fadeIn("slow", function()
        {
            $("#showAdd").fadeOut("slow", function()
            {
                /* Get data */
                postDataJSON(
                {
                    "type": "adminAddProcess",
                    "process": $("#addProcess").find("select[name='process']").val(),
                    "wait": $("#addProcess").find("select[name='wait']").val(),
                    "repeat": $("#addProcess").find("input[name='repeat']:checked").val(),
                    "flow": $("#addProcess").find("select[name='flow']").val(),

                    /* YTS */
                    "begin": $("#addProcess").find("input[name='start']").val(),
                    "end": $("#addProcess").find("input[name='end']").val()
                });

                /* Reset form */
                $("#addProcess").trigger("reset");
            });
        });
    });
});

/* Remove a stopped session */
$(document).on("click", ".removeProcess", function()
{
    /*Get sessionId */
    var sessionId = $(this).attr("data-session");

    /* Get data */
    postDataJSON(
    {
        "type": "adminRemoveSession",
        "id": sessionId
    });

    /* Remove row, so we don't need to refresh everything */
    $("#sessionRow" + sessionId).fadeOut("slow", function()
    {
        $(this).remove();
    })

    return false;
});

/* Stop an active session */
$(document).on("click", ".warning", function() 
{
	/*Get sessionId */
	var sessionId = $(this).attr("data-session");

	/* Get data */
    postDataJSON(
    {
        "type": "adminStopSession",
        "id": sessionId
    });

	/* Close modalbox*/
	$("#confirm-delete").modal("toggle");

	return false;
});

/* Kill an active (or dead) session */
$(document).on("click", ".danger", function() 
{
	/*Get PID */
	var pid = $(this).attr("data-pid");

	/* Get data */
    postDataJSON(
    {
        "type": "adminKillSession",
        "id": pid
    });

	/* Close modalbox*/
	$("#confirm-delete").modal("toggle");

	return false;
});

/* Delete a process */
$(document).on("click", ".deleteProcess", function()
{
    /*Get ID */
    var id = $(this).attr("data-id");

    /* Get data */
    postDataJSON(
    {
        "type": "adminRemoveProcess",
        "id": id
    });

    return false;
});