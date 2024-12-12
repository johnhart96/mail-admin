<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
$user = filter_var( $_GET['user'] , FILTER_SANITIZE_STRING );
$part = explode( "@" , $user );
$domain = $part[1];
if( isset( $_POST['addAlias'] ) ) {
    $dn = "mail=" . $user . ",ou=Users,domainName=" . $domain . "," . LDAP_DOMAINDN;
    $existing_alias = filter_var( $_POST['existing_alias'] , FILTER_SANITIZE_STRING );
    $new_alias = filter_var( $_POST['new_alias'] , FILTER_UNSAFE_RAW );
    $aliases = $existing_alias . $new_alias;
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
            watchdog( "Editing mailbox `" . $user . "`" );
            plugins_process( "users_alias" , "submit" );
            $saved = TRUE;
        }
    }
}
if( isset( $_GET['deleteAlias'] ) ) {
    $deleteAlias = filter_var( $_GET['deleteAlias'] , FILTER_UNSAFE_RAW );
    $dn = "mail=" . $user . ",ou=Users,domainName=" . $domain . "," . LDAP_DOMAINDN;
    $removal = array(
        "shadowAddress" => $deleteAlias
    );
    ldap_mod_del( $ds , $dn , $removal );
    header( "Location: users_alias.php?user=" . filter_var( $_GET['user'] , FILTER_UNSAFE_RAW ) );
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
                            <li class="nav-item">
                                <a class="nav-link" href="users_wblist.php?user=<?php echo $user; ?>">White/Black List</a>
                            </li> 
                        </ul>
                        <p>&nbsp;</p>
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
                        <table class="table table-stripe table-bordered">
                            <?php
                            $parts = explode( "," , $al );
                            $full_list = "";
                            foreach( $parts as $part ) {
                                echo "<tr>";
                                echo "<td>" . $part . "</td>";
                                echo "<td width='1'><a class='btn btn-danger' href='users_alias.php?user=$user&deleteAlias=" . $part . "'><i class='fas fa-trash'></i></a>";
                                echo "</tr>";
                                $full_list .= $part . ",";
                            }
                            ?>
                            <tr>
                                <td><input type='hidden' name='existing_alias' value='<?php echo $full_list; ?>'><input type="text" autofocus class="form-control" name="new_alias"></td>
                                <td><button class="btn btn-success" type="submit" name="addAlias"><i class="fas fa-plus"></i></button></td>
                            </tr>
                        </table>
                        <?php plugins_process( "users_alias" , "form" ); ?>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>