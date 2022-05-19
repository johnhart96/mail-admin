<?php
/*
*   Project: Mail-Admin
*   Author: John Hart
*   Mailbox cleanup script to be run via cron
*/
define( "USR" , "/var/www/mail-admin/usr/" );
echo "Mailbox cleanup...\n";
if( file_exists( USR . "/delete_list.txt" ) ) {
    $file = file( USR . "/delete_list.txt" );
    foreach( $file as $delete ) {
        echo "Deleting " . $delete . "\n";
        shell_exec( "rm -rf " . $delete );
    }
}
unlink( USR . "/delete_list.txt" );
?>