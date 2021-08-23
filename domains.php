<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
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
                    <h1>Domains</h1>
                    <?php
                    if( isset( $_GET['deleted'] ) ) {
                        echo "<div class='alert alert-success'>Domain deleted!</div>";
                    }
                    if( isset( $_GET['added'] ) ) {
                        echo "<div class='alert alert-success'>Domain added!</div>";
                    }
                    
                    ?>
                    <div class="btn-group">
                        <a href="domain_new.php" class="btn btn-success"><i class='fas fa-plus'></i>&nbsp;Domain</a>
                    </div>
                    <?php
                    require 'inc/bind.php';
                    $filter = "(domainName=*)";
                    $result = ldap_search( $ds , LDAP_BASEDN , $filter ) or exit("Unable to search");
                    $entries = ldap_get_entries( $ds , $result );
                    $count = $entries['count'];
                    unset( $entries['count'] );
                    ?>
                    <table id="domains" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Org</th>
                                <th>Domain</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>User Count</th>
                                <th colspan="2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach( $entries as $domain ) {
                                echo "<tr>";
                                echo "<td>" . $domain['cn'][0] . "</td>";
                                echo "<td>" . $domain['domainname'][0] . "</td>";
                                // Description
                                echo "<td>";
                                if( ! empty( $domain['description'] ) ) {
                                    echo $domain['description'][0];
                                } else {
                                    echo "<em>None</em>";
                                }
                                echo "</td>";
                                echo "<td>" . $domain['accountstatus'][0] . "</td>";
                                echo "<td>" . $domain['domaincurrentusernumber'][0] . "</td>";
                                echo "<td width='1'>";
                                echo "<a class='btn btn-primary' href='domain_edit.php?domain=" . $domain['domainname'][0] . "'><i class='fas fa-edit'></i></a>";
                                echo "</td>";
                                echo "<td width='1'>";
                                echo "<a class='btn btn-danger' href='domain_delete.php?domain=" . $domain['domainname'][0] . "'><i class='fas fa-trash'></i></a>";
                                echo "</td>";
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