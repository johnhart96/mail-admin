<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
$group = filter_var( $_GET['group'] , FILTER_SANITIZE_STRING );
if( isset( $_POST['submit'] ) ) {
    $members = filter_var( $_POST['members'] , FILTER_SANITIZE_STRING );
    $membersArray = explode( "," , $members );

    // Remove all members
    $filter = "(memberofgroup=$group)";
    $findMembers = ldap_search( $ds , LDAP_BASEDN , $filter );
    $members = ldap_get_entries( $ds , $findMembers );
    unset( $members['count'] );
    foreach( $members as $member ) {
        ldap_mod_del( $ds , $member['dn'] , array( "memberofgroup" => $group ) );
    }
    $errorUsers = array();
    // Add group to users
    foreach( $membersArray as $member ) {
        
        if( ! empty( $member ) ) {
            $part = explode( "@" , $member );
            $domain = $part[1];
            $userDN = "mail=" . $member . ",ou=Users,domainName=" . $domain . "," . LDAP_DOMAINDN;
            $filter = "mail=$member";
            $checkUserExists = ldap_search( $ds , LDAP_BASEDN, $filter );
            $check = ldap_get_entries( $ds , $checkUserExists );
            if( ! isset( $check['count'] ) ) {
                array_push( $errorUsers , $member );
            } else {
                error_reporting( 0 );
                ldap_mod_add( $ds , $userDN , array( "memberofgroup" => $group ) );
                error_reporting( E_ALL );
            }
        }
    }
    // Add members to group
    $count = 0;
    $part = explode( "@" , $group );
    $domain = $part[1];
    foreach( $membersArray as $member ) {
        $filter = "(mail=$member)";
        $search = ldap_search( $ds , LDAP_BASEDN , $filter );
        $e = ldap_get_entries( $ds , $search ); 
        $info['member'][$count] = $e[0]['dn'];
        $count ++;
    }
    $groupDN = "mail=" . $group . ",ou=Groups,domainName=" . $domain . "," . LDAP_DOMAINDN;
    ldap_modify( $ds , $groupDN , $info );
    

    // Owner
    $part = explode( "@" , $group );
    $domain = $part[1];
    $owner = filter_var( $_POST['owner'] , FILTER_SANITIZE_STRING ) . "@" . $domain;
    $filter = "mail=" . $owner;
    $searchForOwner = ldap_search( $ds , LDAP_BASEDN , $filter );
    $entries = ldap_get_entries( $ds  ,$searchForOwner );
    if( ! isset( $entries[0] ) ) {
        $cannotFindOwner = TRUE;
    } else {
        $dnToUse = "mail=" . $group . ",ou=Groups,domainName=" . $domain . "," . LDAP_DOMAINDN;
        ldap_modify( $ds , $dnToUse , array( "listowner" => $owner ) );
    }
    
    // Mods
    $mods = filter_var( $_POST['mods'] , FILTER_SANITIZE_STRING );
    $mod = explode( "," , $mods );
    ldap_modify( $ds , $dnToUse , array( "listmoderator" => array() ) ); // Delete existing mods
    foreach( $mod as $address ) {
        $filter = "mail=" . $address;
        $searchForMod = ldap_search( $ds , LDAP_BASEDN , $filter );
        $entries = ldap_get_entries( $ds  ,$searchForMod );
        if( isset( $entries[0] ) ) {
            ldap_mod_add( $ds , $dnToUse , array( "listmoderator" => $address ) );
        }
    }

    // Access policy
    $accesspolicy = filter_var( $_POST['accesspolicy'] , FILTER_SANITIZE_STRING );
    ldap_modify( $ds , $dnToUse , array( "accesspolicy" => $accesspolicy ) );

    // Description
    $description = filter_var( $_POST['description'] , FILTER_SANITIZE_STRING );
    if( ! empty( $description ) ) {
        ldap_modify( $ds , $dnToUse , array( "description" => $description ) );
    }
    

    plugins_process( "groups_edit" , "submit" );

    if( ! isset( $cannotFindOwner ) ) {
        watchdog( "Editing group `" . $group . "`" );
        header( "Location: groups.php?saved" );
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
                        <h1>Edit Group</h1>
                        <?php
                        if( isset( $errorUsers ) ) {
                            foreach( $errorUsers as $user ) {
                                echo "<div class='alert alert-warning'><strong>WARNING!</strong> Could not add the user `$user` to this group, as they could not be found!</div>";
                            }
                        }
                        if( isset( $cannotFindOwner ) ) {
                            echo "<div class='alert alert-danger'><strong>ERROR:</strong> Cannot find the owner, you asked for!</div>";
                        } else {
                            echo "<div class='alert alert-info'><strong>Note:</strong> Members of a list must have existing mailboxes on this server!</div>"; 
                        }
                        ?>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="groups.php">Groups</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $group; ?></li>
                            </ol>
                        </nav>
                        <div class="input-group">
                            <?php
                            $description = "";
                            if( isset( $group['description'] ) ) {
                                $description = $group['description'];
                            }
                            ?>
                            <div class="input-group-prepend"><span class="input-group-text">Description:</span></div>
                            <input type="text" class="form-control" name="description" value="<?php echo $description; ?>">
                        </div>
                        <p>&nbsp;</p>

                        <div class="mb-3">
                            <?php
                            $filter = "(memberofgroup=$group)";
                            $findMembers = ldap_search( $ds , LDAP_BASEDN , $filter );
                            $members = ldap_get_entries( $ds , $findMembers );
                            unset( $members['count'] );
                            $membersList = "";
                            foreach( $members as $member ) {
                                $membersList .= $member['mail'][0] . ","; 
                            }
                            $membersList = substr( $membersList , 0  , -1 );
                            ?>
                            <label for="members" class="form-label">Members (comma seperated):</label>
                            <textarea class="form-control" id="members" rows="3" name="members"><?php echo $membersList; ?></textarea>
                        </div>
                        <?php
                        $part = explode( "@" , $group );
                        $domain = $part[1];
                        $dnToUse = "mail=" . $group . ",ou=Groups,domainName=" . $domain . "," . LDAP_DOMAINDN;
                        $filter = "(mail=*)";
                        $getGroupDetails = ldap_search( $ds , $dnToUse , $filter );
                        $groupDetail = ldap_get_entries( $ds , $getGroupDetails );
                        $owner = "";
                        if( ! empty( $groupDetail[0]['listowner'][0] ) ) {
                            $owner = $groupDetail[0]['listowner'][0];
                        }
                        $owner = str_replace( "@" . $domain , "" , $owner );
                        ?>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Owner:</span></div>
                            <input type="text" class="form-control" name="owner" value="<?php echo $owner; ?>">
                            <div class="input-group-append">
                                <span class="input-group-text">@<?php echo $domain; ?></span>
                            </div>
                        </div>

                        <?php
                        $mods = "";
                        if( ! empty( $groupDetail[0]['listmoderator'] ) ) {
                            unset( $groupDetail[0]['listmoderator']['count'] );
                            foreach( $groupDetail[0]['listmoderator'] as $mod ) {
                                $mods .= $mod . ",";
                            }
                            $mods = substr( $mods , 0  , -1 );
                        }
                        ?>
                        <p>&nbsp;</p>
                        <div class="mb-3">
                            <label for="mods" class="form-label">Moderators (comma seperated):</label>
                            <textarea class="form-control" id="mods" rows="3" name="mods"><?php echo $mods; ?></textarea>
                        </div>

                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Access Policy:</span></div>
                            <select class="form-control" name="accesspolicy">
                                <?php
                                if( ! empty( $groupDetail[0]['accesspolicy'] ) ) {
                                    $policy = $groupDetail[0]['accesspolicy'][0];
                                } else {
                                    $policy = "public";
                                }
                                $options = array( 0 =>"public" , 1 => "domain" , 2 => "membersonly" , 3=> "membersandmoderatorsonly" );
                                $key = array(
                                    "public" => "Public",
                                    "domain" => "This domain only",
                                    "membersonly" => "Members only",
                                    "membersandmoderatorsonly" => "Members and Moderators only"
                                );
                                foreach( $options as $option ) {
                                    if( $option == $policy ) {
                                        echo "<option selected value='" . $option . "'>" . $key[$option] . "</option>";
                                    } else {
                                        echo "<option value='" . $option . "'>" . $key[$option] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <?php plugins_process( "groups_edit" , "form" ); ?>
                        <p>&nbsp;</p>
                        <p><button type="submit" name="submit" class="btn btn-success"><i class="fas fa-save"></i>&nbsp;Save</button></p>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>