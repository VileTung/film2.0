<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Logging
 */

class loggen
{
    //Where do we need to place the log file?
    private $filenameCSV;
    private $filenameHTML;

    //Character between values (split values)
    private $separator = ",";

    //Starting headers
    private $header = "DATETIME,ERRORLEVEL,TAG,VALUE,LINE,FILE";

    //Default tag
    const defaultTag = "--";

    //Constructor
    public function __construct($file)
    {
        $this->filenameCSV = $file.".txt";
        $this->filenameHTML = $file.".html";
    }

    //Write log file
    private function writeLog($level = "INFO", $colorCLI = "\033[01;37m", $colorHTML = "black", $value, $tag)
    {
        //Current datetime
        $datetime = date("Y-m-d H:i:s");

        //Check if CSV not file exits
        if (!file_exists($this->filenameCSV))
        {
            $headersCSV = $this->header . "\n";
        }
        //Else, we already have the headers
        else
        {
            $headersCSV = false;
        }

        //Open file
        $openFileCSV = fopen($this->filenameCSV, "a");
        $openFileHTML = fopen($this->filenameHTML, "a");

        //Now write the headers, if necessary
        if ($headersCSV)
        {
            fwrite($openFileCSV, $headersCSV);
        }

        //Backtrace, to get the file and line
        $debugBacktrace = debug_backtrace();

        //Line and file
        $line = $debugBacktrace[1]["line"];
        $file = $debugBacktrace[1]["file"];

        //Only display function if possible
        $function = (isset($debugBacktrace[2]) ? $debugBacktrace[2]["function"] : $tag);

        //The line, with info
        $entry = array(
            $datetime,
            $level,
            $function,
            $value,
            $line,
            $file);

        //Place it in the file
        fputcsv($openFileCSV, $entry, $this->separator);
		
		//HTML line
		$html = "<span style=\"color: ".$colorHTML."\">[" . $datetime . "] " . $function . "</span> " . $value . "<br />";
		
		//Write HTML
		fwrite($openFileHTML, $html);

        //Close the file
        fclose($openFileCSV);
        fclose($openFileHTML);

        //And finally, push it to the screen
        print ($colorCLI . "[" . $datetime . "] " . $function . "\033[0m: " . $value . "\n");
    }

    //Function for simple information messages
    public function info($value, $tag = self::defaultTag)
    {
        self::writeLog("INFO", "\033[01;37m", "black", $value, $tag);
    }

    //Function for warning messages
    public function warning($value, $tag = self::defaultTag)
    {
        self::writeLog("WARNING", "\033[01;33m", "orange", $value, $tag);
    }

    //Function for error messages
    public function error($value, $tag = self::defaultTag)
    {
        self::writeLog("ERROR", "\033[01;31m", "red", $value, $tag);
    }

    //Function for debug messages
    public function debug($value, $tag = self::defaultTag)
    {
        self::writeLog("DEBUG", "\033[00;37m", "grey", $value, $tag);
    }
}

?>