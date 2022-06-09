<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
$user = filter_var( $_GET['user'] , FILTER_SANITIZE_STRING );
$part = explode( "@" , $user );
$domain = $part[1];
// Get entry
$filter = "(mail=$user)";
$search = ldap_search( $ds , LDAP_BASEDN , $filter );
$entry = ldap_get_entries( $ds , $search );
unset( $entry['count'] );
$entry = $entry[0];
if( isset( $_POST['submit'] ) ) {
    $dn = $entry['dn'];
    // Remove existing forwarders
    ldap_mod_del( $ds , $dn , array( "mailforwardingaddress" => array() ) );
    // Add new entrys
    $address = explode( "," , filter_var( $_POST['forwards'] , FILTER_SANITIZE_STRING ) );
    $count = 0;
    $info = array();
    foreach( $address as $a ) {
        $info['mailforwardingaddress'][$count] = $a;
        $count ++;
    }
    ldap_mod_add( $ds , $dn , $info );
    plugins_process( "users_forwarding" , "submit" );
    watchdog( "Editing user `" . $user . "`" );
    header( "Location:users_forwarding.php?user=" . $user . "&saved" );
}

function checkbox( $h ) {
    global $entry;
    foreach( $entry['enabledservice'] as $service ) {
        if( $service == $h ) {
            echo "checked";
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
                        if( isset( $_GET['saved'] ) ) {
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
                                <a class="nav-link" href="users_alias.php?user=<?php echo $user; ?>">Aliases</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="users_services.php?user=<?php echo $user; ?>">Services</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="users_forwarding.php?user=<?php echo $user; ?>">Forwarding</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="users_bcc.php?user=<?php echo $user; ?>">BCC</a>
                            </li> 
                            <li class="nav-item">
                                <a class="nav-link" href="users_wblist.php?user=<?php echo $user; ?>">White/Black List</a>
                            </li> 
                        </ul>
                        <p>&nbsp;</p>
                        <div class="mb-3">
                            <?php
                            if( isset( $entry['mailforwardingaddress'] ) ) {
                                $al = "";
                                unset( $entry['mailforwardingaddress']['count'] );
                                foreach( $entry['mailforwardingaddress'] as $address ) {
                                    $al .= $address . ",";
                                }
                                $al = substr( $al , 0 , -1 );
                            } else {
                                $al = "";
                            }
                            ?>
                            <label for="forwards" class="form-label">Forwarding addresses (comma seperated):</label>
                            <textarea class="form-control" id="forwards" rows="3" name="forwards"><?php echo $al; ?></textarea>
                            <?php plugins_process( "users_forwarding" , "form" ); ?>
                            <p>&nbsp;</p>
                            <button class="btn btn-success" name="submit" type="submit"><i class="fas fa-save"></i>&nbsp;Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>