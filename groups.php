<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
?>
<html>
    <head>
        <?php
        require 'inc/header.php';
        ?>
    </head>
    <body>
        <?php require 'inc/new_topbar.php'; ?>
        <div class="container">
            <div class="row">
                <div class="col">
                    <form method="post">
                        <h1>Distribution Groups</h1>
                        <?php
                        if( isset( $_GET['saved'] ) ) {
                            echo "<div class='alert alert-success'>Group saved!</div>";
                        }
                        if( isset( $_GET['deleted'] ) ) {
                            echo "<div class='alert alert-success'>Group deleted!</div>";
                        }
                        ?>
                        <p><a href="groups_new.php" class="btn btn-success"><i class="fas fa-plus"></i>&nbsp;Group</a></p>
                        <table class="table table-bordered table-stripped">
                            <thead>
                                <tr>
                                    <th>Address</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Members</th>
                                    <th>Owner</th>
                                    <th>Access Policy</th>
                                    <th colspan="2">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $filter = "(objectclass=mailList)";
                                require 'inc/relmset.php';
                                $getGroups = ldap_search( $ds , $relm , $filter );
                                $entries = ldap_get_entries( $ds , $getGroups );
                                unset( $entries['count'] );
                                foreach( $entries as $group ) {
                                    echo "<tr>";
                                    echo "<td>" . $group['mail'][0] . "</td>";
                                    // Description
                                    echo "<td>";
                                    if( isset( $group['description'][0] ) ) {
                                        echo $group['description'][0];
                                    } else {
                                        echo "<em>None</em>";
                                    }
                                    echo "</td>";
                                    echo "<td>" . $group['accountstatus'][0] . "</td>";
                                    // Members
                                    echo "<td>";
                                    $filter = "(memberofgroup=" . $group['mail'][0] . ")";
                                    $findMembers = ldap_search( $ds , $relm , $filter );
                                    $members = ldap_get_entries( $ds , $findMembers );
                                    $members['count'] = $members['count'] -1;
                                    echo $members['count'];
                                    echo "</td>";

                                    // Owner
                                    echo "<td>";
                                    if( ! empty( $group['listowner'][0] ) ) {
                                        echo $group['listowner'][0];
                                    } else {
                                        echo "<em>None</em>";
                                    }
                                    echo "</td>";

                                    // Access policy
                                    echo "<td>";
                                    if( ! empty( $group['accesspolicy'][0] ) ) {
                                        $key = array(
                                            "public" => "Public",
                                            "domain" => "This domain only",
                                            "membersonly" => "Members only",
                                            "membersandmoderatorsonly" => "Members and Moderators only"
                                        );
                                        $po = $group['accesspolicy'][0];
                                        echo $key[$po];
                                    } else {
                                        echo "Public";
                                    }
                                    echo "</td>";

                                    // Buttons
                                    echo "<td width='1'><a href='groups_edit.php?group=" . $group['mail'][0] . "' class='btn btn-primary'><i class='fas fa-edit'></i></a></td>";
                                    if( $members['count'] !== 0 ) {
                                        echo "<td width='1'><a href='#' class='btn btn-secondary'><i class='fas fa-trash'></i></td>";
                                    } else {
                                        echo "<td width='1'><a href='groups_delete.php?group=" . $group['mail'][0] . "' class='btn btn-danger'><i class='fas fa-trash'></i></a></td>";
                                    }
                                    
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