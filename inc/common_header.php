<?php
if( ! file_exists( "usr/config.php" ) ) {
    require 'inc/header.php';
    echo "<div class='alert alert-danger'>";
    echo "<strong>Error:</strong><br />";
    echo "No config file exists. Make a copy of config.sample.php and add your configuration there.";
    echo "</div>";
    die();
} else {
    require_once 'usr/config.php';
}
// Plugin dir init
if( ! file_exists( "plugins" ) ) {
    mkdir( "plugins" );
}

// iRedAPD Database
$apd = new PDO( "mysql:host=" . IAPD_HOST . ";dbname=" . IAPD_DB , IAPD_USER , IAPD_PASSWORD );

session_start();

// Version control
define( MAVERSION , "1.1" );
?>