<?php

/**
 * @author Kevin
 * @copyright 2015
 * @info API
 */

//Check if there is any POST-data
if (count($_POST) > 0)
{
    //Security check
    if ($_POST["check" . date("Y")] == date("dmY"))
    {
        //Load
        require_once ("functions/functions.php");

        //Start data class
        $_getMovies = new getMovies();

        //Begin
        if (isset($_POST["page"]) && is_numeric($_POST["page"]))
        {
            $begin = ($_POST["page"] - 1) * 30;

            $_getMovies->__set("begin", $begin);
        }
        else
        {
            $_getMovies->__set("begin", 0);
        }

        //Limit
        $_getMovies->__set("limit", 30);

        //By
        if (isset($_POST["by"]) && $_POST["by"] != "")
        {
            $_getMovies->__set("qBy", $_POST["by"]);
        }
        else
        {
            $_getMovies->__set("qBy", "DESC");
        }

        //Sort
        if (isset($_POST["sort"]) && $_POST["sort"] != "")
        {
            $_getMovies->__set("qSort", $_POST["sort"]);
        }
        else
        {
            $_getMovies->__set("qSort", "added");
        }

        //Genre
        if (isset($_POST["genre"]) && $_POST["genre"] != "")
        {
            $_getMovies->__set("qGenre", $_POST["genre"]);
        }

        //Title
        if (isset($_POST["title"]) && $_POST["title"] != "")
        {
            $_getMovies->__set("qTitle", $_POST["title"]);
        }

        //Title cut
        if (isset($_POST["titleLength"]) && $_POST["titleLength"] > 0)
        {
            $_getMovies->__set("titleCut", true);
            $_getMovies->__set("titleLength", $_POST["titleLength"]);
        }
        else
        {
            $_getMovies->__set("titleCut", false);
        }

        //Movie and other data
        $data = $_getMovies->data();

        //JSON
        if ($_POST["output"] == "json")
        {
            //Header
            header("Content-type: application/json; charset=utf-8");

            //If we found movies
            if (is_array($data))
            {
                print (json_encode($data));
            }
            else
            {
                $json = array("error" => "1", "message" => "I couldn't find any movies.");
                //Print it
                print (json_encode($json));
            }
        }
        //XML
        elseif ($_POST["output"] == "xml")
        {
            header("Content-Type: application/xml; charset=utf-8");

            require_once ($web . "bTemplate.php");

            $bTemplate = new bTemplate();

            //Movies found?
            if (is_array($data) && count($data) > 0)
            {
                $bTemplate->set("movieFound", true, true);

                //Set the movies
                $bTemplate->set("movies", $data);
            }
            //Nothing found
            else
            {
                $bTemplate->set("movieFound", false, true);

                //Set error message
                $bTemplate->set("error", "1");
                $bTemplate->set("message", "I couldn't find any movies.");
            }

            //Print the template!
            print ($bTemplate->fetch($web . "template/api.xml"));
        }
    }
}
