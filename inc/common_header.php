<?php
if( ! file_exists( "usr/config.php" ) ) {
    header( "Location: installer/" );
} else {
    require_once 'usr/config.php';
}
// Plugin dir init
if( ! file_exists( "plugins" ) ) {
    mkdir( "plugins" );
}

// iRedAPD Database
if( IAPD_ENABLE ) {
    $apd = new PDO( "mysql:host=" . IAPD_HOST . ";dbname=" . IAPD_DB , IAPD_USER , IAPD_PASSWORD ); 
} else {
    $apd = NULL;
}

// Amavisd
if( AMA_ENABLE ) {
    $amavisd = new PDO( "mysql:host=" . AMA_HOST . ";dbname=" . AMA_DB , AMA_USER , AMA_PASSWORD );
} else {
    $amavisd = NULL;
}

session_start();

// Version control
define( "MAILADMIN_VERSION" , "2.2" );
?>