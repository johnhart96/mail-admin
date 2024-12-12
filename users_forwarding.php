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

// Add new entry
if( isset( $_POST['submit'] ) ) {
    $dn = $entry['dn'];
    $address = explode( "," , filter_var( $_POST['newForward'] , FILTER_VALIDATE_EMAIL ) );
    $count = 0;
    $info = array(
        "mailforwardingaddress" => $address
    );
    ldap_mod_add( $ds , $dn , $info );
    plugins_process( "users_forwarding" , "submit" );
    watchdog( "Editing user `" . $user . "`" );
    header( "Location:users_forwarding.php?user=" . $user . "&saved" );
}
// Remove entry
if( isset( $_GET['deleteForward'] ) ) {
    $deleteForward = filter_var( $_GET['deleteForward'] , FILTER_VALIDATE_EMAIL );
    $remove = array(
        "mailforwardingaddress" => $deleteForward
    );
    $dn = "mail=" . $user . ",ou=Users,domainName=" . $domain . "," . LDAP_DOMAINDN;
    ldap_mod_del( $ds , $dn , $remove );
    header( "Location: users_forwarding.php?user=" . $user );
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
                        <h1>Edit Mailbox</h1>
                        <?php
                        if( isset( $_GET['saved'] ) ) {
                            echo "<div class='alert alert-success'>Changes saved!</div>";
                        }
                        ?>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="users.php">Mailboxes</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $user; ?></li>
                            </ol>
                        </nav>
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="users_edit.php?user=<?php echo $user; ?>">General</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="users_alias.php?user=<?php echo $user; ?>">Addresses</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="users_services.php?user=<?php echo $user; ?>">Permissions</a>
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
                            <div class="alert alert-info">The addresses listed below will receive a forwarded copy of any emails sent to this mailbox. Remember to enable forwarding in the permissions tab.</div>
                            <table class="table table-bordered table-striped">
                                <?php
                                if( isset( $entry['mailforwardingaddress'] ) ) {
                                    unset( $entry['mailforwardingaddress']['count'] );
                                    foreach( $entry['mailforwardingaddress'] as $address ) {
                                        echo "<tr>";
                                        echo "<td>" . $address . "</td>";
                                        echo "<td width='1'><a class='btn btn-danger' href='users_forwarding.php?user=$user&deleteForward=" . $address . "'><i class='fas fa-trash'></i></a>";
                                        echo "</tr>";
                                    }
                                }
                                ?>
                                <tr>
                                    <td><input type="text" name="newForward" class="form-control"></td>
                                    <td><button type="submit" name="submit" class="btn btn-success"><i class="fas fa-plus"></i></button></td>
                                </tr>
                            </table>
                            <?php plugins_process( "users_forwarding" , "form" ); ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>