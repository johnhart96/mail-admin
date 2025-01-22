<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
$user = filter_var( $_GET['user'] , FILTER_UNSAFE_RAW );
$part = explode( "@" , $user );
$domain = $part[1];
if( isset( $_POST['submit'] ) ) {
    $info = array();
    $info['uid'][0] = filter_var( $_POST['username'] , FILTER_UNSAFE_RAW );
    $info['givenname'][0] = filter_var( $_POST['firstname'] , FILTER_UNSAFE_RAW );
    $info['sn'][0] = filter_var( $_POST['lastname'] , FILTER_UNSAFE_RAW );
    $info['displayname'][0] = filter_var( $_POST['displayname'] , FILTER_UNSAFE_RAW );
    if( ! empty( $_POST['description'] ) ) {
        $info['description'][0] = filter_var( $_POST['description'] , FILTER_UNSAFE_RAW );
    }
    if( ! empty( $_POST['password'] ) ) {
        $password = filter_var( $_POST['password'] , FILTER_UNSAFE_RAW );
        $info['userPassword'] = hash_password( $password );
    }
    
    $dnToUse = "mail=" . $user . ",ou=Users,domainName=" . $domain . "," . LDAP_DOMAINDN;

    // Global admin bits
    if( $_SESSION['admin_level'] == "global" ) {
        // Global Admin?
        $search = ldap_search( $ds , $dnToUse , "(domainglobaladmin=TRUE)" );
        $result = ldap_get_entries( $ds , $search );
        if( isset( $_POST['admin'] ) ) {
            $info['domainglobaladmin'][0] = "TRUE";
        } else {
            if( $result['count'] == 1 ) {
                ldap_mod_del( $ds , $dnToUse , array( "domainglobaladmin" => "TRUE" ) );
            }
        }
        
        // Domain Admin?
        $search = ldap_search( $ds , $dnToUse , "(enabledservice=domainAdmin)" );
        $result = ldap_get_entries( $ds , $search );
        if( isset( $_POST['domainAdmin'] ) ) {
            if( $result['count'] !==1 ) {
                ldap_mod_add( $ds , $dnToUse , array( "enabledservice" => "domainAdmin" ) );
            }
            
        } else {
            if( $result['count'] !==0 ) {
                ldap_mod_del( $ds , $dnToUse , array( "enabledservice" => "domainAdmin" ) );
            }
        }
    }
    

    // Quota
    if( $_SESSION['admin_level'] == "global" ) { 
        $info['mailquota'] = filter_var( $_POST['mailQuota'] , FILTER_VALIDATE_INT );
    }

    // Modify all user details
    if( ldap_modify( $ds , $dnToUse , $info ) ) {
        plugins_process( "users_edit" , "submit" );
        watchdog( "Editing user `" . $user . "`" );
        header( "Location:users.php?saved" );
    } else {
        die( "Cannot modify object!" );
    }
}

// Get currently selected user
$filter = "(mail=$user)";
$findMembers = ldap_search( $ds , LDAP_BASEDN , $filter );
$userDetail = ldap_get_entries( $ds , $findMembers );
unset( $userDetail['count'] );
$userDetail = $userDetail[0];
if( ! isset( $userDetail['description'][0] ) ) {
    $userDetail['description'][0] = NULL;
}
if( empty( $userDetail['givenname'] ) ) {
    $userDetail['givenname'] = array( 0 => NULL );
}
if( empty( $userDetail['displayname'][0] ) ) {
    $userDetail['displayname'][0] = $userDetail['givenname'][0] . " " . $userDetail['sn'][0];
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
                        <h1>Edit Mailbox</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="users.php">Mailboxes</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $user; ?></li>
                            </ol>
                        </nav>
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="users_edit.php?user=<?php echo $user; ?>">General</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="users_groups.php?user=<?php echo $user; ?>">Groups</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="users_alias.php?user=<?php echo $user; ?>">Addresses</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="users_services.php?user=<?php echo $user; ?>">Permissions</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="users_forwarding.php?user=<?php echo $user; ?>">Forwarding</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="users_bcc.php?user=<?php echo $user; ?>">BCC</a>
                            </li> 
                            <li class="nav-item">
                                <a class="nav-link" href="users_wblist.php?user=<?php echo $user; ?>">White/Black List</a>
                            </li>  
                        </ul>

                        <p>&nbsp;</p>
                        
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" name="username" class="form-control" value="<?php echo $userDetail['uid'][0]; ?>">
                        </div>
                        <div class="form-group">
                            <label for="firstname">Firstname:</label>
                            <input type="text" name="firstname" class="form-control" value="<?php echo $userDetail['givenname'][0]; ?>">
                        </div>
                        <div class="form-group">
                            <label for="lastname">Lastname:</label>
                            <input type="text" name="lastname" class="form-control" value="<?php echo $userDetail['sn'][0]; ?>">
                        </div>
                        <div class="form-group">
                            <label for="displayname">Display name:</label>
                            <input type="text" name="displayname" class="form-control" value="<?php echo $userDetail['displayname'][0]; ?>">
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter to change">
                        </div>
                        <div class="mb-3">
                            <label for="description">Description:</label>
                            <textarea name="description" class="form-control"><?php echo $userDetail['description'][0]; ?></textarea>
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Quota:</span></div>
                            <?php
                            if( $_SESSION['admin_level'] == "global" ) {
                                $disabled = "";
                            } else {
                                $disabled = "disabled";
                            }
                            ?>
                            <input type="text" name="mailQuota" class="form-control" value="<?php echo $userDetail['mailquota'][0]; ?>" <?php echo $disabled; ?>>
                            <div class="input-group-append"><span class="input-group-text">Bytes</span></div>
                        </div>
                        <p>&nbsp;</p>
                        <?php if( $_SESSION['admin_level'] == "global" ) { ?>
                            <div class="form-check">
                                <?php
                                if( isset( $userDetail['domainglobaladmin'] ) ) {
                                    $checked = "checked";
                                } else {
                                    $checked = "";
                                }
                                ?>
                                <input class="form-check-input" type="checkbox" value="" id="admin" name="admin" <?php echo $checked; ?>>
                                <label class="form-check-label" for="admin">
                                    <span style="color:red">*</span>Global Administrator
                                </label>

                                <?php
                                $checkDomainAdmin = ldap_search( $ds , $userDetail['dn'] , "(enabledService=domainAdmin)" );
                                $result = ldap_get_entries( $ds , $checkDomainAdmin );
                                $checked = "";
                                if( isset( $result[0]['enabledservice'] ) ) {
                                    foreach( $result[0]['enabledservice'] as $service ) {
                                        if( $service == "domainAdmin" ) {
                                            $checked = "checked";
                                        }
                                    }
                                }
                                ?>
                                <br />
                                <input class="form-check-input" type="checkbox" value="" id="domainAdmin" name="domainAdmin" <?php echo $checked; ?>>
                                <label class="form-check-label" for="domainAdmin">
                                    <span style="color:orange">*</span>Domain Administrator
                                </label>
                            </div>
                        <?php } ?>
                        <?php plugins_process( "users_edit" , "form" ); ?>
                        <p>&nbsp;</p>
                        <p><button type="submit" name="submit" class="btn btn-success"><i class="fas fa-save"></i>&nbsp;Save</button></p>
                        
                        
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>