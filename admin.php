<?php

/**
 * @author Kevin
 * @copyright 2015
 * @info Admin web interface
 */

require_once ("functions/functions.php");

//Template
$bTemplate = new bTemplate();

//Version number
$_version = new version();
$bTemplate->set("version", $_version->display());

print ($bTemplate->fetch($web . "template/admin.html"));

?>