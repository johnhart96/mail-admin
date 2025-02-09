<?php
if( ! file_exists( "usr/config.php" ) ) {
    header( "Location: /installer" );
} else {
    require_once 'usr/config.php';
}
// Plugin dir init
if( ! file_exists( "plugins" ) ) {
    mkdir( "plugins" );
}

// iRedAPD Database
$apd = new PDO( "mysql:host=" . IAPD_HOST . ";dbname=" . IAPD_DB , IAPD_USER , IAPD_PASSWORD );

// Amavisd
$amavisd = new PDO( "mysql:host=" . AMA_HOST . ";dbname=" . AMA_DB , AMA_USER , AMA_PASSWORD );
session_start();

// Version control
define( "MAILADMIN_VERSION" , "2.1" );
?>