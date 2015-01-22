<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Version
 */

class version
{
    private $version;

    //Set version number
    public function __construct()
    {
        exec("git describe --always", $tag);
        //exec("git rev-list HEAD | wc -l", $commit);

        $this->version = trim($tag[0]);
    }

    //Display version number
    public function display()
    {
        return $this->version;
    }
}

?>