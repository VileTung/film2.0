<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Get settings
 */

class settings
{
    //All our settings
    private $ini;

    public function __construct()
    {
        global $iniSettings;

        //Read file
        $this->ini = parse_ini_file($iniSettings, true);
    }

    //Read
    public function get($section, $name)
    {
        $value = $this->ini[$section][$name];

        //Boolean?
        if ($value == "true")
        {
            return true;
        }
        //Boolean?
        elseif ($value == "false")
        {
            return false;
        }
        //Regular value
        else
        {
            //Return
            return $value;
        }
    }

    //Save
    public function set($section, $name, $value, $bool = false)
    {
        global $iniSettings;

        //Check if value is a boolean
        if ($bool)
        {
            if ($value)
            {
                $value = "true";
            }
            else
            {
                $value = "false";
            }
        }

        //Set new value
        $this->ini[$section][$name] = $value;

        //Default
        $return = "";

        //Loop through array
        foreach ($this->ini as $key => $value)
        {
            $return .= "[" . $key . "]\n";

            foreach ($value as $key => $string)
            {
                $return .= $key . "=\"" . $string . "\";\n";
            }

            $return .= "\n";
        }

        $return = trim($return);

        //Save settings
        $state = file_put_contents($iniSettings, $return, LOCK_EX);

        if ($state > 0)
        {
            //Success
            return true;
        }
        else
        {
            //Failed
            return false;
        }
    }
}

?>