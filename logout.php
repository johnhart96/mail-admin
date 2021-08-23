<?php
require 'inc/functions.php';
require 'inc/common_header.php';
session_start();
session_destroy();
header( "Location: login.php" );
?>