<?php
/*
* Project: Mail-Admin
* Author: John Hart
*/

// LDAP bind
function bind() {
    if( file_exists( "inc/bind.php" ) ) {
        require "inc/bind.php";
    } else {
        require "../inc/bind.php";
    }
}

//Alias
function alias_delete( $object ) {
    require 'inc/bind.php';
    $part = explode( "@" , $alias );
    $domain = $part[1];
    $dn = "mail=" . $alias . ",ou=Aliases,domainName=" . $domain . "," . LDAP_DOMAINDN;
    if( ldap_delete( $ds , $dn ) ) {
        return TRUE;
    } else {
        return FALSE;
    }
}
?>