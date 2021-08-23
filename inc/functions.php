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
?>