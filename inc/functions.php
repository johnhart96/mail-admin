<?php
function hash_password($password) {
    if (@is_readable('/dev/urandom')) {
        $f=fopen('/dev/urandom', 'rb');
        $salt=fread($f, 4);
        fclose($f);
    } else {
        die('Could not query /dev/urandom');
    }
    return '{SSHA}' . base64_encode(sha1( $password.$salt, TRUE ). $salt);
}
function go( $location ) {
    echo "<script>window.location='" . $location . "'></script>";
} 
function securePage() {
    if( ! isset( $_SESSION['mail-admin'] ) ) {
        header( "Location: login.php" );
    }
}
function plugins_process( $page , $location ) {
    $plugin_dir = scandir( "plugins" );
    foreach( $plugin_dir as $dir ) {
        if( $dir !== ".." && $dir !== "." ) {
            $file_to_load = "plugins/" . $dir . "/" . $page . "_" . $location . ".php";
            if( file_exists( $file_to_load ) ) {
                require $file_to_load;
            }
        }
    }
}
function watchdog( $entry ) {
    // Header
    $stamp = date( "Y-m-d H:i" );
    if( isset( $_SESSION['mail-admin'] ) ) {
        $header = $stamp . " (" . $_SESSION['mail-admin'] . "): ";
    } else {
        $header = $stamp . ": ";
    }
    // Check if log file exists
    if( ! file_exists( "usr/admin.log" ) ) {
        // Create the file
        $create = fopen( "usr/admin.log" , "w" );
        fwrite( $create , NULL );
        fclose( $create );
    }
    // Add entry to the log file
    $log = fopen( "usr/admin.log" , "a" );
    fwrite( $log , "\n" . $header . $entry );
    fclose( $log );
}
function globalOnly() {
    require 'inc/relmset.php';
    if( $_SESSION['admin_level'] !== "global" ) {
        die( "Access Denied!" );
    }
}
function display_name( $email , $dn = false ) {
    require "inc/bind.php";
    if( ! $dn ) {
        $filter = "(mail=$email)";
        $search = ldap_search( $ds , LDAP_BASEDN , $filter );
        $results = ldap_get_entries( $ds , $search );
        $results = $results[0]['displayname'][0];
        if( empty( $results ) ) {
            return $email;
        } else {
            return $results;
        }
    } else {
        $search = ldap_search( $ds , $email , "(mail=*)" );
        $results = ldap_get_entries( $ds , $search );
        return $results[0]['displayname'][0];
    }
    
}
function email( $dn ) {
    require 'inc/bind.php';
    $filter = "(mail=*)";
    $search = ldap_search( $ds , $dn , $filter );
    $results = ldap_get_entries( $ds , $search );
    return $results[0]['mail'][0];
}
function getSystemMemInfo() {       
    $data = explode("\n", file_get_contents("/proc/meminfo"));
    $meminfo = array();
    error_reporting( 0 );
    foreach ($data as $line) {
        list($key, $val) = explode(":", $line);
        $meminfo[$key] = trim($val);
    }
    error_reporting( E_ALL );
    return $meminfo;
}
?>