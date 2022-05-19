<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
$user = filter_var( $_GET['user'] , FILTER_SANITIZE_STRING );
if( isset( $_GET['confirm'] ) ) {
    $part = explode( "@" , $user );
    $domain = $part[1];
    $dnToDelete = "mail=" . $user . ",ou=Users,domainName=" . $domain . "," . LDAP_DOMAINDN;
    // Get home path
    $search = ldap_search( $ds , $dnToDelete , "(mail=*)" );
    $entry = ldap_get_entries( $ds , $search );
    $path = $entry[0]['homedirectory'][0];
    if( ! file_exists( "usr/delete_list.txt" ) ) {
        $make = fopen( "usr/delete_list.txt" , "w" );
        fwrite( $make , "" );
        fclose( $make );
    }
    $add = fopen( "usr/delete_list.txt" , "a" );
    fwrite( $add , $path . "\n" );
    fclose( $add );

    // Delete
    if( ldap_delete( $ds , $dnToDelete ) ) {
        plugins_process( "users_delete" , "submit" );
        watchdog( "Deleting user `" . $user . "`" );
        header( "Location: users.php?deleted" );
    } else {
        die( "Cannot delete!" );
    }
}
?>
<html>
    <head>
        <?php
        require 'inc/header.php';
        ?>
    </head>
    <body>
        <?php require 'inc/topbar.php'; ?>
        <div class="container">
            <div class="row">
                <div class="col">
                    <form method="post">
                        <h1>Delete User</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="users.php">Users</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Delete</li>
                            </ol>
                        </nav>
                        <div class="alert alert-warning">
                            Are you sure you want to delete `<?php echo $user ?>`?
                        </div>
                        <?php plugins_process( "users_delete" , "form" ); ?>
                        <p>
                            <a href="users_delete.php?user=<?php echo $user; ?>&confirm" class="btn btn-danger"><i class="fas fa-check"></i>&nbsp;Yes</a>
                            <a href="users.php" class="btn btn-success"><i class="fas fa-times"></i>&nbsp;No</a> 
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>