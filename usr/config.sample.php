<?php
/*
*   Project: Mail-Admin
*   Author: John Hart
*/

// General
define( "BRANDING" , "Mail-Admin" ); // Set the brand name
define( "MAILQUOTA" , 6442450944 );
define( "DEBUG" , TRUE );

// Apps
define( "APP_MAIL" , "https://gmail.com" );
define( "APP_DRIVE" , "https://drive.google.com" );

// LDAP
define( "LDAP_SERVER" , "ldap://127.0.0.1:389" ); // LDAP server location
define( "LDAP_BASEDN" , "dc=domain,dc=com" ); // LDAP Base DN
define( "LDAP_ADMINUSER" , "cn=Manager,dc=domain,dc=com" ); // FULL DN for admin user
define( "LDAP_ADMINPASSWD" , "passwordhere" );

define( "LDAP_DOMAINDN" , "o=domains," . LDAP_BASEDN ); // Base DN to add domains

// iRedAPD
define( "IAPD_HOST" , "127.0.0.1" );
define( "IAPD_USER" , "iredapd" );
define( "IAPD_PASSWORD" , "GJVAQskHtE3Oh5mcchz81f14vWg3ZwaD" );
define( "IAPD_DB" , "iredapd" );

// Amavisd
define( "AMA_HOST" , "127.0.0.1" );
define( "AMA_USER" , "amavisd" );
define( "AMA_PASSWORD" , "ENGMUJ0DHQgdiIySQVRrfiBZQiNofqqL" );
define( "AMA_DB" , "amavisd" );
?>