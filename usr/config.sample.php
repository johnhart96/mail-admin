<?php
/*
*   Project: Mail-Admin
*   Author: John Hart
*/

// General
define( "BRANDING" , "Mail-Admin" ); // Set the brand name
define( "MAILQUOTA" , 6442450944 );
define( "DEBUG" , TRUE );
define( "SERVERHOSTNAME" , "mail.example.com" ); 

// Apps
define( "APP_MAIL" , "https://gmail.com" );
define( "APP_DRIVE" , "https://drive.google.com" );

// LDAP
define( "LDAP_SERVER" , "ldap://127.0.0.1:389" ); // LDAP server location
define( "LDAP_BASEDN" , "dc=johnathome,dc=online" ); // LDAP Base DN
define( "LDAP_ADMINUSER" , "cn=vmailadmin,dc=johnathome,dc=online" ); // FULL DN for admin user
define( "LDAP_ADMINPASSWD" , "7tV2Y2A7k4X4bvBawp5UFn3cH3bCxhzJ" );

define( "LDAP_DOMAINDN" , "o=domains," . LDAP_BASEDN ); // Base DN to add domains

// iRedAPD
define( "IAPD_ENABLE" , TRUE );
define( "IAPD_HOST" , "127.0.0.1" );
define( "IAPD_USER" , "vmail" );
define( "IAPD_PASSWORD" , "f424dYYdGFhodrIO0ik9WNGJfNYJTQMq" );
define( "IAPD_DB" , "iredapd" );

// Amavisd
define( "AMA_ENABLE" , TRUE );
define( "AMA_HOST" , "127.0.0.1" );
define( "AMA_USER" , "amavisd" );
define( "AMA_PASSWORD" , "f424dYYdGFhodrIO0ik9WNGJfNYJTQMq" );
define( "AMA_DB" , "amavisd" );
?>

