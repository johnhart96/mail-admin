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
    $userSenderBccAddress = filter_var( $_POST['userSenderBccAddress'] , FILTER_SANITIZE_STRING );
    $userRecipientBccAddress = filter_var( $_POST['userRecipientBccAddress'] , FILTER_SANITIZE_STRING );
    $info['usersenderbccaddress'][0] = $userSenderBccAddress;
    $info['userrecipientbccaddress'][0] = $userRecipientBccAddress;
    ldap_modify( $ds , $dn , $info );
    plugins_process( "users_bcc" , "submit" );
    watchdog( "Editing user `" . $user . "`" );
    header( "Location:users_bcc.php?user=" . $user . "&saved" );
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
                                <a class="nav-link" href="users_forwarding.php?user=<?php echo $user; ?>">Forwarding</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link active" href="users_bcc.php?user=<?php echo $user; ?>">BCC</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="users_wblist.php?user=<?php echo $user; ?>">White/Black List</a>
                            </li>  
                        </ul>
                        <p>&nbsp;</p>
                        <div class="mb-3">
                            <?php
                            $userSenderBccAddress = NULL;
                            $userRecipientBccAddress = NULL;
                            if( isset( $entry['userrecipientbccaddress'] ) ) {
                                $userSenderBccAddress = $entry['userrecipientbccaddress'][0];
                            }
                            if( isset( $entry['userrecipientbccaddress'] ) ) {
                                $userRecipientBccAddress = $entry['userrecipientbccaddress'][0];
                            }
                            
                            ?>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Relay received email to:</span>
                                </div>
                                <input type="text" name="userRecipientBccAddress" class="form-control" value="<?php echo $userRecipientBccAddress; ?>">
                            </div>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Relay sent email to:</span>
                                </div>
                                <input type="text" name="userSenderBccAddress" class="form-control" value="<?php echo $userSenderBccAddress; ?>">
                            </div>
                            <?php plugins_process( "users_bcc" , "form" ); ?>
                            <p>&nbsp;</p>
                            <button class="btn btn-success" name="submit" type="submit"><i class="fas fa-save"></i>&nbsp;Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>