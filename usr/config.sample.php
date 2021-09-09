<?php
/*
*   Project: Mail-Admin
*   Author: John Hart
*/

// General
define( "BRANDING" , "Mail-Admin" ); // Set the brand name
define( "MAILQUOTA" , 6442450944 );

// LDAP
define( "LDAP_SERVER" , "ldap://127.0.0.1:389" ); // LDAP server location
define( "LDAP_BASEDN" , "dc=domain,dc=com" ); // LDAP Base DN
define( "LDAP_ADMINUSER" , "cn=Manager,dc=domain,dc=com" ); // FULL DN for admin user
define( "LDAP_ADMINPASSWD" , "passwordhere" );

define( "LDAP_DOMAINDN" , "o=domains," . LDAP_BASEDN ); // Base DN to add domains

?>