<?php

/**
 * @author Kevin
 * @copyright 2015
 * @info Admin web interface
 */

require_once ("functions/functions.php");

//Template
$bTemplate = new bTemplate();

print ($bTemplate->fetch($web . "template/admin.html"));

?>