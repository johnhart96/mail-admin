<?php
require 'inc/functions.php';
require 'inc/common_header.php';
watchdog( "Logging out" );
session_start();
session_destroy();
header( "Location: login.php" );
?>