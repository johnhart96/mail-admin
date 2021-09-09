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
?>