<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
$group = filter_var( $_GET['group'] , FILTER_UNSAFE_RAW );
if( isset( $_GET['confirm'] ) ) {
    $part = explode( "@" , $group );
    $domain = $part[1];
    $dnToDelete = "mail=" . $group . ",ou=Groups,domainName=" . $domain . "," . LDAP_DOMAINDN;
    if( ldap_delete( $ds , $dnToDelete ) ) {
        plugins_process( "groups_delete" , "submit" );
        header( "Location: groups.php?deleted" );
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
                        <h1>Delete Distribution Group</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="groups.php">Groups</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Delete</li>
                            </ol>
                        </nav>
                        <div class="alert alert-warning">
                            Are you sure you want to delete `<?php echo $group ?>`?
                        </div>
                        <?php
                        plugins_process( "groups_delete" , "form" );
                        watchdog( "Deleting group `" . $group . "`" );
                        ?>
                        <p>
                            <a href="groups_delete.php?group=<?php echo $group; ?>&confirm" class="btn btn-danger"><i class="fas fa-check"></i>&nbsp;Yes</a>
                            <a href="groups.php" class="btn btn-success"><i class="fas fa-times"></i>&nbsp;No</a> 
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>