<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
$user = filter_var( $_GET['user'] , FILTER_SANITIZE_STRING );
$part = explode( "@" , $user );
$domain = $part[1];
if( isset( $_POST['submit'] ) ) {
    $dn = "mail=" . $user . ",ou=Users,domainName=" . $domain . "," . LDAP_DOMAINDN;
    $aliases = filter_var( $_POST['aliases'] , FILTER_SANITIZE_STRING );
    if( $aliases !== "," ) {
        $alias = explode( "," , $aliases );
        $count = 0;
        foreach( $alias as $add ) {
            $info['shadowaddress'][$count] = $add;
            $count ++;
        }
        if( ldap_modify( $ds , $dn , $info ) ) {
            $saved = TRUE;
        }
    } else {
        $info['shadowaddress'] = NULL;
        if( ldap_modify( $ds , $dn , $info ) ) {
            watchdog( "Editing user `" . $user . "`" );
            plugins_process( "users_alias" , "submit" );
            $saved = TRUE;
        }
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
                        <h1>Edit User</h1>
                        <?php
                        if( isset( $saved ) ) {
                            echo "<div class='alert alert-success'>Changes saved!</div>";
                        }
                        ?>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="users.php">Users</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $user; ?></li>
                            </ol>
                        </nav>
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="users_edit.php?user=<?php echo $user; ?>">General</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="users_alias.php?user=<?php echo $user; ?>">Aliases</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="users_services.php?user=<?php echo $user; ?>">Services</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="users_forwarding.php?user=<?php echo $user; ?>">Forwarding</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="users_bcc.php?user=<?php echo $user; ?>">BCC</a>
                            </li> 
                        </ul>
                        <p>&nbsp;</p>
                        <div class="mb-3">
                            <?php
                            $filter = "(mail=$user)";
                            $search = ldap_search( $ds , "ou=Users,domainName=" . $domain . "," . LDAP_DOMAINDN , $filter );
                            $entry = ldap_get_entries( $ds , $search );
                            unset( $entry['count'] );
                            if( isset( $entry[0]['shadowaddress'] ) ) {
                                $aliases = $entry[0]['shadowaddress'];
                                unset( $aliases['count'] );
                                $al = "";
                                foreach( $aliases as $alias ) {
                                    $al .= $alias . ",";
                                }
                            } else {
                                $al = NULL;
                            }
                            $al = substr( $al , 0 , -1 );
                            ?>
                            <label for="aliases" class="form-label">Aliases (comma seperated):</label>
                            <textarea class="form-control" id="aliases" rows="3" name="aliases"><?php echo $al; ?></textarea>
                            <?php plugins_process( "users_alias" , "form" ); ?>
                            <p>&nbsp;</p>
                            <button type="submit" class="btn btn-success" name="submit"><i class="fas fa-save"></i>&nbsp;Save</button>
                        </div>
                        
                        
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>