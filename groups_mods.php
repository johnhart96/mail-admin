<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
$group = filter_var( $_GET['group'] , FILTER_SANITIZE_STRING );
$part = explode( "@" , $group );
$domain = $part[1];
$groupDN = "mail=" . $group . ",ou=Groups,domainName=" . $domain . "," . LDAP_DOMAINDN;
                
// Add mod
if( isset( $_POST['submit'] ) ) {
    $addMod = filter_var( $_POST['addMod'] , FILTER_VALIDATE_EMAIL );
    ldap_mod_add( $ds , $groupDN , array( "listModerator" => $addMod ) );
}

// Remove mod
if( isset( $_GET['delete'] ) ) {
    $delete = filter_var( $_GET['delete'] , FILTER_VALIDATE_EMAIL );
    ldap_mod_del( $ds , $groupDN , array( "listModerator" => $delete ) );
    header( "Location: groups_mods.php?group=" . $group );
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
                        <h1>Edit Distribution Group</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="groups.php">Groups</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $group; ?></li>
                            </ol>
                        </nav>
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="groups_edit.php?group=<?php echo $group; ?>">General</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="groups_mods.php?group=<?php echo $group; ?>">Moderators</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="groups_members.php?group=<?php echo $group; ?>">Members</a>
                            </li>
                        </ul>

                        <p>&nbsp;</p>

                        <table class="table table-bordered table-stripped">
                            <?php
                            $dnToUse = "mail=" . $group . ",ou=Groups,domainName=" . $domain . "," . LDAP_DOMAINDN;
                            $filter = "(mail=*)";
                            $getGroupDetails = ldap_search( $ds , $dnToUse , $filter );
                            $groupDetail = ldap_get_entries( $ds , $getGroupDetails );
                            $groupDetail = $groupDetail[0];
                            if( ! empty( $groupDetail['listmoderator'] ) ) {
                                $mods = $groupDetail['listmoderator'];
                                unset( $mods['count'] );
                                foreach( $mods as $mod ) {
                                    echo "<tr>";
                                    echo "<td><a href='users_edit.php?user=" . $mod . "'>" . display_name( $mod ) . "</a></td>";
                                    echo "<td width='1'><a href='groups_mods.php?group=" . $group . "&delete=$mod' class='btn btn-danger'><i class='fas fa-trash'></i></td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                            <tr>
                                <td>
                                    <select class="form-control" name="addMod">
                                        <?php
                                        $dn = "ou=Users,domainName=$domain," . LDAP_DOMAINDN;
                                        $getMailboxes = ldap_search( $ds , $dn , "(mail=*)" );
                                        $entries = ldap_get_entries( $ds , $getMailboxes );
                                        unset( $entries['count'] );
                                        foreach( $entries as $entry ) {
                                            echo "<option value='" . $entry['mail'][0] . "'>" . $entry['displayname'][0] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td width="1"><button type="submit" name="submit" class="btn btn-success"><i class="fas fa-plus"></i></td>
                            </tr>
                        </table>    

                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
