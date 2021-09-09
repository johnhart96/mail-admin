<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
$domain = filter_var( $_GET['domain'] , FILTER_SANITIZE_STRING );
require 'inc/bind.php';
if( isset( $_GET['confirm'] ) ) {
    $entry = "domainName=" . $domain . "," . LDAP_DOMAINDN;

    // Delete OUs
    ldap_delete( $ds  , "ou=Aliases," . $entry );
    ldap_delete( $ds  , "ou=Groups," . $entry );
    ldap_delete( $ds  , "ou=Computers," . $entry );
    ldap_delete( $ds  , "ou=Users," . $entry );
    ldap_delete( $ds  , "ou=Externals," . $entry );


    // Delete the domain it's self
    if( ldap_delete( $ds  , $entry ) ) {
        plugins_process( "domain_delete" , "submit" );
        watchdog( "Deleting domain `" . $domain . "`" );
        header( "Location:domains.php?deleted" );
    } else {
        die( "Unable to delete" );
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
                        <h1>Delete Domain</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="domains.php">Domains</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Delete</li>
                            </ol>
                        </nav>
                        <?php
                        $sr = ldap_search( $ds , LDAP_BASEDN , "(domainName=$domain)" );
                        $check = ldap_get_entries( $ds , $sr );
                        if( (int)$check[0]['domaincurrentusernumber'][0] !== 0 ) {
                            echo "<div class='alert alert-danger'><strong>Error:</strong> This domain cannot be considared for deletion as it has existing mailboxes.</div>";
                            echo "<a href='domains.php' class='btn btn-primary'><i class='fas fa-arrow-left'></i>&nbsp;Back</a>";
                            die();
                        }
                        plugins_process( "domain_delete" , "form" );
                        ?>
                        <div class="alert alert-warning">Are you sure you want to delete `<?php echo $check[0]['domainname'][0]; ?>`?</div>
                        <p>
                            <a class="btn btn-danger" href="domain_delete.php?domain=<?php echo $domain; ?>&confirm"><i class='fas fa-check'></i>&nbsp;Yes</a>
                            <a class="btn btn-success" href="domains.php"><i class='fas fa-times'></i>&nbsp;No</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>