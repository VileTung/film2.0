<?php

/**
 * @author Kevin
 * @copyright 2015
 * @info Web interface
 */

require_once ("functions/functions.php");

//Template
$bTemplate = new bTemplate();

//Version number
$_version = new version();
$bTemplate->set("version", $_version->display());

print ($bTemplate->fetch($web . "template/index.html"));

?>