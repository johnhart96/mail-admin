<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
?>
<html>
    <head>
        <?php
        require 'inc/header.php';
        require 'inc/bind.php';
        ?>
    </head>
    <body>
        <?php require 'inc/topbar.php'; ?>
        <div class="container">
            <div class="row">
                <div class="col">
                    <form method="post">
                        <h1>Users</h1>
                        <?php
                            $domain = filter_var( $_GET['domain'] , FILTER_SANITIZE_STRING );
                        if( isset( $_GET['deleted'] ) ) {
                            echo "<div class='alert alert-success'>User deleted!</div>";
                        }
                        if( isset( $_GET['saved'] ) ) {
                            echo "<div class='alert alert-success'>User saved!</div>";
                        }
                        if( isset( $_GET['domain'] ) ) {
                            $link = "users_new.php?domain=" . filter_var( $_GET['domain'] , FILTER_SANITIZE_STRING );
                        } else {
                            $link = "users_new.php";
                        }
                        ?>
                        <p><a href="<?php echo $link; ?>" class="btn btn-success"><i class="fas fa-plus"></i>&nbsp;User</a></p>
                        <table class="table table-bordered table-stripped">
                            <thead>
                                <tr>
                                    <th>Display Name</th>
                                    <th>Address</th>
                                    <th>Account Status</th>
                                    <th>Description</th>
                                    <th colspan="2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $filter = "(uid=*)";
                                if( isset( $_GET['domain'] ) ) {
                                    $dnToUse = "ou=Users,domainName=" . filter_var( $_GET['domain'] , FILTER_SANITIZE_STRING ) . "," . LDAP_DOMAINDN;
                                } else {
                                    $dnToUse = LDAP_DOMAINDN;
                                }
                                $getUsers = ldap_search( $ds , $dnToUse , $filter );
                                $users = ldap_get_entries( $ds , $getUsers );
                                unset( $users['count'] );
                                foreach( $users as $user ) {
                                    echo "<tr>";
                                    // Display Name
                                    echo "<td>";
                                    if( ! empty( $user['cn'][0] ) ) {
                                        echo $user['cn'][0];
                                    } else {
                                        echo "?";
                                    }
                                    echo "</td>";
                                    // Address
                                    echo "<td>" . $user['mail'][0] . "</td>";
                                    // Account status
                                    echo "<td>";
                                    echo $user['accountstatus'][0];
                                    if( isset( $user['domainglobaladmin'][0] ) ) {
                                        echo " (Admin)";
                                    }
                                    echo "</td>";
                                    // Description
                                    echo "<td>";
                                    if( ! empty( $user['description'][0] ) ) {
                                        echo $user['description'][0];
                                    } else {
                                        echo "<em>None</em>";
                                    }
                                    echo "</td>";

                                    // buttons
                                    echo "<td width='1'><a href='users_edit.php?user=" . $user['mail'][0] . "' class='btn btn-primary'><i class='fas fa-edit'></i></td>";
                                    echo "<td width='1'><a href='users_delete.php?user=" . $user['mail'][0] . "' class='btn btn-danger'><i class='fas fa-trash'></i></td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>