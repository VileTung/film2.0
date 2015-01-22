#!/usr/bin/php
<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info The Runner!
 */

//Only CLI (CommandLine)
if (php_sapi_name() == "cli")
{
    require_once ("functions/functions.php");

    //Can we continue?
    $continue = false;

    if (!isset($argv[1]))
    {
        print ("Pick one: 'YTS', 'buildCache': ");
        $retriever = trim(fgets(STDIN));

        if ($retriever != "YTS" && $retriever != "buildCache")
        {
            print ("\nWrong input!\n");
            exit;
        }

        //Only for YTS
        if ($retriever == "YTS")
        {
            print ("Starting page: ");
            $min = trim(fgets(STDIN));

            if (!is_numeric($min))
            {
                print ("\nWrong input!\n");
                exit;
            }

            print ("Ending page: ");
            $max = trim(fgets(STDIN));

            if (!is_numeric($max))
            {
                print ("\nWrong input!\n");
                exit;
            }
        }

        print ("\n");
        print ("Is this correct?\n");
        print ("Retriever: " . $retriever . "\n");

        //Only for YTS
        if ($retriever == "YTS")
        {
            print ("Start: " . $min . "\n");
            print ("End: " . $max . "\n");

            //Locker text
            $lText = "searching (" . $min . " until " . $max . ")";
        }
        //Only for buildCache
        elseif ($retriever == "buildCache")
        {
            //Locker text
            $lText = "building SQL Cache";
        }

        print ("\n");
        print ("Typ 'yes' to continue: ");

        $correct = trim(fgets(STDIN));

        if ($correct != "yes")
        {
            print ("\nWrong input!\n");
            exit;
        }

        print ("\n\n");

        //Continue, execute
        $continue = true;
    }
    //Automatic system
    //YTS
    elseif ($argv[1] == "YTS" && isset($argv[2]) && isset($argv[3]) && is_numeric($argv[2]) && is_numeric($argv[3]))
    {
        $retriever = "YTS";
        $min = $argv[2];
        $max = $argv[3];

        //Locker text
        $lText = "(" . $min . " until " . $max . ")";

        //Continue, execute
        $continue = true;
    }
    //buildCache
    elseif ($argv[1] == "buildCache")
    {
        $retriever = "buildCache";

        //Locker text
        $lText = "building SQL Cache";

        //Continue, execute
        $continue = true;
    }

    if ($continue)
    {
        try
        {
            //Locks a session
            $locker = new locker($retriever . " " . $lText);

            //Get created session
            $session = $locker->getSession();

            //Starts logging
            $logging = new loggen($log . $session);

            //Message
            $logging->info("Starting '" . $retriever . "' " . $lText . "!");

            //YTS
            if ($retriever == "YTS")
            {
                //Initialize YTS
                $yts = new yts();

                //Start YTS
                $yts->movies($min, $max);
            }
            //buildCache
            elseif ($retriever == "buildCache")
            {
                //Initialize getMovies
                $getMovies = new getMovies();

                //Build Cache
                $getMovies->setCache();
            }

            //Removes lock
            $locker->stop($retriever == "buildCache" ? true : false);
        }
        //Error..
        catch (exception $e)
        {
            $logging->error($e->getMessage());
        }
    }
    //We're missing some info
    else
    {
        print ("We're missing some info!\n");
        exit;
    }
}
//Browser is not allowed..
else
{
    die("I'm a CLI application!\n");
}

?>
?>