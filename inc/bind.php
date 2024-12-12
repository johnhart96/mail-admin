<?php
$ds = ldap_connect( LDAP_SERVER );
ldap_set_option( $ds, LDAP_OPT_PROTOCOL_VERSION, 3 );
$bind = ldap_bind( $ds , LDAP_ADMINUSER , LDAP_ADMINPASSWD );
if( ! $bind ) {
    die( "Error: Unable to bind to LDAP, please check your settings and retry!" );
}
?>