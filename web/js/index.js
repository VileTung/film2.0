/**
 * @author Kevin
 * @copyright 2015
 * @info Index AJaX calls
 */
/* Default values, needed for index page */
var iBy = "DESC";
var iGenre = "";
var iSort = "added";
var iTitle = "";
var iCount = 0;

/* Needed for date */
function addZero(i)
{
    if (i < 10)
    {
        i = "0" + i;
    }
    return i;
}

/* Process movie data */
function movieData(fresh)
{
    /* Security check */
    var date = new Date();

    /* Set our post data */
    var post = {};
    post["check" + date.getFullYear()] = addZero(date.getDate()) + addZero(date.getMonth() + 1) + date.getFullYear();
    post["return"] = "html";
    post["type"] = "index";
    post["begin"] = iCount;
    post["limit"] = "30";
    post["by"] = iBy;
    post["genre"] = iGenre;
    post["sort"] = iSort;
    post["title"] = iTitle;

    /* Show a loading message */
    if (fresh)
    {
        $("#page").fadeOut("fast", function()
        {
            $(this).empty().html("Loading...").fadeIn("fast");
        });
    }

    /* Send the POST-request */
    $.post("web/action.php", post)
        .done(function(data)
        {
            /* Append */
            if ($("#append").length && !fresh)
            {
                var identifier = "#append";
            }
            /* New page */
            else
            {
                var identifier = "#page";
            }

            $(identifier).fadeOut("fast", function()
            {
                //New page
                if (identifier == "#page")
                {
                    $(this).empty().html(data).fadeIn("fast", function()
                    {
                        //FitText, make text fit!
                        $(".fitText").fitText();
                    });
                }
                //Append
                else
                {
                    $(this).replaceWith(data).fadeIn("fast", function()
                    {
                        //FitText, make text fit!
                        $(".fitText").fitText();
                    });
                }

                //We have to bind it again!
                $(function()
                {
                    $("[data-toggle=\"tooltip\"]").tooltip();
                });
            });

            /* Set new count */
            iCount += 30;
        });
}

/* Load default page (movies) */
$(document).ready(function()
{
    /* Get data */
    movieData(false);
});

/* Default page, when 'home' is clicked */
$(document).on("click", "#home", function()
{
    /* Remove active class */
    $("#" + iSort).closest("li").removeClass("active");
    $("#" + iGenre).closest("li").removeClass("active");

    /* Remove identifier */
    var value = $("#" + iSort).text();

    /* Split */
    var nValue = value.split(" ");

    $("#" + iSort).html(nValue[0]);

    /* Reset default values */
    iBy = "DESC";
    iGenre = "";
    iSort = "added";
    iTitle = "";
    iCount = 0;

    /* Get data */
    movieData(true);

    return false;
});

/* Load more */
$(document).on("click", "#loadMore", function()
{
    $("#loadMore").fadeOut("fast", function()
    {
        /* Let the user know we are trying to load */
        $(this).text("Loading...").fadeIn("fast");

        /* Remove ID, to avoid multiple clicking */
        $(this).removeAttr("id");
    });

    /* Get data */
    movieData(false);

    return false;
});

/* Sort or genre click */
$(document).on("click", ".sort, .genre", function()
{
    /* Hide/close the dropdown menu*/
    $(".dropdown.open .dropdown-toggle").dropdown("toggle");

    /* Sort or genre */
    var type = $(this).attr("class");

    /* Sort */
    if (type == "sort")
    {
        /* Remove identifier */
        var value = $("#" + iSort).text();

        /* Split */
        var nValue = value.split(" ");

        $("#" + iSort).html(nValue[0]);

        /* Get ASC or DESC */
        if (iBy == "DESC" && iSort == $(this).attr("id"))
        {
            /* ASC */
            iBy = "ASC";
            $(this).attr("data-by", "DESC");

            /* Identifier */
            var value = $(this).text();

            /* Split */
            var nValue = value.split(" ");

            $(this).html(nValue[0] + " &#8593;");
        }
        else
        {
            /* DESC */
            iBy = "DESC";
            $(this).attr("data-by", "ASC");

            /* Identifier */
            var value = $(this).text();

            /* Split */
            var nValue = value.split(" ");

            $(this).html(nValue[0] + " &#8595;");
        }

        /* Remove active class */
        $("#" + iSort).closest("li").removeClass("active");

        /* Get sort type */
        iSort = $(this).attr("id");
    }
    /* Genre */
    else if (type == "genre")
    {
        /* Remove active class */
        $("#" + iGenre).closest("li").removeClass("active");

        /* Get genre */
        iGenre = $(this).attr("id");
    }

    /* Set active class to selected genre */
    $(this).closest("li").addClass("active");

    /* Reset page count */
    iCount = 0;

    /* Get data */
    movieData(true);

    return false;
});

/* Search title */
$(document).on("submit", "#search", function(event)
{
    /* Stop form from submitting normally */
    event.preventDefault();

    /* Set title, for searching */
    iTitle = $(this).find("input[name='title']").val();

    /* Reset form */
    $("#search").trigger("reset");

    /* Reset page count */
    iCount = 0;

    /* Get data */
    movieData(true);
});