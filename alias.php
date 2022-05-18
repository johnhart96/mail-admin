<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
$filter = "(objectclass=mailalias)";
require 'inc/relmset.php';
$result = ldap_search( $ds , $relm , $filter ) or exit("Unable to search");
$entries = ldap_get_entries( $ds , $result );
$count = $entries['count'];
unset( $entries['count'] );
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
                    <h1>Aliases</h1>
                    <?php
                    if( isset( $_GET['saved'] ) ) {
                        echo "<div class='alert alert-success'>Alias saved!</div>";
                    }
                    if( isset( $_GET['deleted'] ) ) {
                        echo "<div class='alert alert-success'>Alias deleted!</div>";
                    }
                    ?>
                    <p><a href="alias_new.php" class="btn btn-success"><i class="fas fa-plus"></i>&nbsp;Alias</a></p>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Destinations</th>
                                <th colspan="2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach( $entries as $alias ) {
                                echo "<tr>";
                                echo "<td>"  . $alias['mail'][0] . "</td>";
                                // Description
                                echo "<td>";
                                if( ! empty( $alias['description'] ) ) {
                                    echo $alias['description'][0];
                                } else {
                                    echo "<em>None</em>";
                                }
                                echo "</td>";
                                echo "<td>"  . $alias['accountstatus'][0] . "</td>";
                                // Addresses
                                unset( $alias['mailforwardingaddress']['count'] );
                                echo "<td>";
                                foreach( $alias['mailforwardingaddress'] as $address ) {
                                    echo $address . ", ";
                                }
                                echo "</td>";
                                echo "<td><a href='alias_edit.php?alias=" . $alias['mail'][0] . "' class='btn btn-primary'><i class='fas fa-edit'></i></td>";
                                echo "<td><a href='alias_delete.php?alias=" . $alias['mail'][0] . "' class='btn btn-danger'><i class='fas fa-trash'></i></td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>