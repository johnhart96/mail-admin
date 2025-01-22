<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
$group = filter_var( $_GET['group'] , FILTER_UNSAFE_RAW );
$part = explode( "@" , $group );
$domain = $part[1];
$groupDN = "mail=" . $group . ",ou=Groups,domainName=" . $domain . "," . LDAP_DOMAINDN;
                
// Add Member
if( isset( $_POST['submit'] ) ) {
    $addMember = filter_var( $_POST['addMember'] , FILTER_VALIDATE_EMAIL );
    $userDN = "mail=$addMember,ou=Users,domainName=$domain," . LDAP_DOMAINDN; 
    // Add member to group
    ldap_mod_add( $ds , $userDN , array( "memberOfGroup" => $group ) );
    // Add group to mailbox
    ldap_mod_add( $ds , $groupDN , array( "member" => $userDN ) );

}

// Remove Member
if( isset( $_GET['delete'] ) ) {
    $delete = filter_var( $_GET['delete'] , FILTER_VALIDATE_EMAIL );
    $userDN = "mail=$delete,ou=Users,domainName=$domain," . LDAP_DOMAINDN; 

    // Remove member from group
    ldap_mod_del( $ds , $userDN , array( "memberOfGroup" => $group ) );
    // Remove group from mailbox
    ldap_mod_del( $ds , $groupDN , array( "member" => $userDN ) );
    
    header( "Location: groups_members.php?group=" . $group );
    
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
                                <a class="nav-link" aria-current="page" href="groups_mods.php?group=<?php echo $group; ?>">Moderators</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="groups_members.php?group=<?php echo $group; ?>">Members</a>
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
                            if( ! empty( $groupDetail['member'] ) ) {
                                $members = $groupDetail['member'];
                                unset( $members['count'] );
                                foreach( $members as $member ) {
                                    echo "<tr>";
                                    echo "<td><a href='users_edit.php?user=" . email( $member ) . "'>" . display_name( $member , TRUE ) . "</a></td>";
                                    echo "<td width='1'><a href='groups_members.php?group=" . $group . "&delete=" . email( $member ) . "' class='btn btn-danger'><i class='fas fa-trash'></i></td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                            <tr>
                                <td>
                                    <select class="form-control" name="addMember">
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
