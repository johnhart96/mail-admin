<?php
require 'inc/functions.php';
require 'inc/common_header.php';
watchdog( "Logging out" );
session_destroy();
echo "<script>window.location='login.php'</script>";
?>