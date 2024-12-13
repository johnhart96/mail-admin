<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
$group = filter_var( $_GET['group'] , FILTER_SANITIZE_STRING );
if( isset( $_POST['submit'] ) ) {
    
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
                        <h1>Edit Distribution Group</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="groups.php">Groups</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $group; ?></li>
                            </ol>
                        </nav>
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="groups_edit.php?group=<?php echo $group; ?>">General</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="groups_mods.php?group=<?php echo $group; ?>">Moderators</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="groups_members.php?group=<?php echo $group; ?>">Members</a>
                            </li>
                        </ul>

                        <p>&nbsp;</p>

                        

                        <?php
                        if( isset( $errorUsers ) ) {
                            foreach( $errorUsers as $user ) {
                                echo "<div class='alert alert-warning'><strong>WARNING!</strong> Could not add the user `$user` to this group, as they could not be found!</div>";
                            }
                        }
                        if( isset( $cannotFindOwner ) ) {
                            echo "<div class='alert alert-danger'><strong>ERROR:</strong> Cannot find the owner, you asked for!</div>";
                        }
                        ?>

                        <div class="form-group">
                            <?php
                            $description = "";
                            if( isset( $group['description'] ) ) {
                                $description = $group['description'];
                            }
                            ?>
                            <label for="description">Description:</label>
                            <input type="text" class="form-control" name="description" value="<?php echo $description; ?>">
                        </div>
                        <p>&nbsp;</p>

                        <div class="form-group">
                            <label for="accesspolicy">Access Policy:</label>
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

                        

                        <?php plugins_process( "groups_edit" , "form" ); ?>
                        <p>&nbsp;</p>
                        <p><button type="submit" name="submit" class="btn btn-success"><i class="fas fa-save"></i>&nbsp;Save</button></p>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
