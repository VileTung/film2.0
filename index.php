<?php

/**
 * @author Kevin
 * @copyright 2015
 * @info Web interface
 */

require_once ("functions/functions.php");

//Template
$bTemplate = new bTemplate();

print ($bTemplate->fetch($web . "template/index.html"));

?>