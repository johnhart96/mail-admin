<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
plugins_process( "groups_edit" , "submit" );
plugins_process( "index" , "submit" );
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
                    <h1>Welcome</h1>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="card">
                        <div class="card-header"><strong>Statistics</strong></div>
                        <div class="card-body">
                            <table class="table table-bordered table-stripped">
                                <tr>
                                    <th>Domains:</th>
                                    <td align="center">
                                        <?php
                                        $filter = "(domainName=*)";
                                        $search = ldap_search( $ds , LDAP_BASEDN , $filter );
                                        $entries = ldap_get_entries( $ds , $search );
                                        unset( $entries['count'] );
                                        echo count( $entries );
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Groups:</th>
                                    <td align="center">
                                        <?php
                                        $filter = "(objectclass=mailList)";
                                        $search = ldap_search( $ds , LDAP_BASEDN , $filter );
                                        $entries = ldap_get_entries( $ds , $search );
                                        unset( $entries['count'] );
                                        echo count( $entries );
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Aliases:</th>
                                    <td align="center">
                                    <?php
                                        $filter = "(objectclass=mailAlias)";
                                        $search = ldap_search( $ds , LDAP_BASEDN , $filter );
                                        $entries = ldap_get_entries( $ds , $search );
                                        unset( $entries['count'] );
                                        echo count( $entries );
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Users:</th>
                                    <td align="center">
                                    <?php
                                        $filter = "(objectclass=mailUser)";
                                        $search = ldap_search( $ds , LDAP_BASEDN , $filter );
                                        $entries = ldap_get_entries( $ds , $search );
                                        unset( $entries['count'] );
                                        echo count( $entries );
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <div class="card-header"><strong>Quick Create</strong></div>
                        <div class="card-body">
                            <div class="btn-group">
                                <a href="domain_new.php" class="btn btn-success"><i class="fas fa-plus"></i>&nbsp;Domain</a>
                                <a href="users_new.php" class="btn btn-success"><i class="fas fa-plus"></i>&nbsp;User</a>
                                <a href="groups_new.php" class="btn btn-success"><i class="fas fa-plus"></i>&nbsp;Group</a>
                                <a href="alias_new.php" class="btn btn-success"><i class="fas fa-plus"></i>&nbsp;Alias</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    
                </div>
                <div class="col">
                    
                </div>
            </div>
            <?php plugins_process( "index" , "form" ); ?>
        </div>
    </body>
</html>