<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Admin interface
 */

//Debug
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once ("bTemplate.php");
require_once ("./../functions/sqlQuery.php");

class admin
{
    //Template
    private $bTemplate;

    public function __construct()
    {
        //Template
        $this->bTemplate = new bTemplate();

        //Make the connection
        Database();
    }

    public function show()
    {
		//Default, no message bar needed
		$success = array("state"=>false,"message"=>false);
		$error = array("state"=>false,"message"=>false);
		
		if(isset($_GET["delete"]) && is_numeric($_GET["delete"]))
		{
			//Remove from DB
			sqlQueryi("DELETE FROM `sessions` WHERE `sessionId` = ?", array("i",$_GET["delete"]));
			
			//Delete lock file
			if(file_exists($cache . "lock_" . $_GET["delete"]))
			{
				unlink($cache . "lock_" . $_GET["delete"]);
				
				$success = array("state"=>true,"message"=>"Successfully stopped the process (".$_GET["delete"].")");
			}
			else
			{
				$error = array("state"=>true,"message"=>"Failed to stop the process (".$_GET["delete"].")");
			}
			
			//Place a redirect? And fix unlink! It's not working atm..
		}
		
		//Get all sessions
		list($rowCount, $result) = sqlQueryi("SELECT * FROM `sessions`", false, true);
		
		foreach($result as $key=>$value)
		{
			//Progressbar colors
			if($value["progress"]<34)
			{
				$result[$key]["class"]="progress-bar-danger";
			}
			elseif($value["progress"]<67)
			{
				$result[$key]["class"]="progress-bar-warning";
			}
			else
			{
				$result[$key]["class"]="progress-bar-success";
			}
		}
		
		$this->bTemplate->set("processes",$result);
		
		//Error bar
		$this->bTemplate->set("isError",$error["state"],true);
		$this->bTemplate->set("error",$error["message"]);
		
		//Success bar
		$this->bTemplate->set("isSuccess",$success["state"],true);
		$this->bTemplate->set("success",$success["message"]);
        
        //Print the template!
        print ($this->bTemplate->fetch("admin.tpl"));
    }
}

$show = new admin();
$show->show();

?>