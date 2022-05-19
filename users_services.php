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
    // Remove all entries
    error_reporting( 0 );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "mail" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "smtp" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "smtpsecured" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "smtptls" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "pop3" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "pop3secured" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "pop3tls" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "imap" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "imapsecured" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "imaptls" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "deliver" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "sogo" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "sieve" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "sievesecured" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "sievetls" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "displayedInGlobalAddressBook" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "shadowaddress" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "forward" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "recipientbcc" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "senderbcc" ) );
    ldap_mod_del( $ds , $dn , array( "enabledservice" => "externalAccessSettings" ) );
    // Add new entries
    foreach( $_POST as $service => $value ) { 
        ldap_mod_del( $ds , $dn , array( "enabledservice" => $service ) );
    }
    foreach( $_POST as $service => $value ) {
        if( $value == "on" ) {
            ldap_mod_add( $ds , $dn , array( "enabledservice" => $service ) );
        }
    }
    if( ! isset( $_POST['externalAccessSettings'] ) ) {
        ldap_mod_del( $ds , $dn , array( "enabledservice" => "pop3secured" ) );
        ldap_mod_del( $ds , $dn , array( "enabledservice" => "pop3tls" ) );
        ldap_mod_del( $ds , $dn , array( "enabledservice" => "imapsecured" ) );
        ldap_mod_del( $ds , $dn , array( "enabledservice" => "imaptls" ) );
        ldap_mod_del( $ds , $dn , array( "enabledservice" => "sievesecured" ) );
        ldap_mod_del( $ds , $dn , array( "enabledservice" => "sievetls" ) );
    }
    plugins_process( "users_services" , "submit" );
    watchdog( "Editing user `" . $user . "`" );
    header( "Location:users_services.php?user=" . $user . "&saved" );
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
                                <a class="nav-link active" href="users_services.php?user=<?php echo $user; ?>">Services</a>
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
                            <table class="table">
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="mail" <?php echo checkbox( "mail" ) ?>>
                                    </td>
                                    <td>Mail Service (Check this box in order to enable other services)</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="smtp" <?php echo checkbox( "smtp" ) ?>>
                                    </td>
                                    <td>Sending mails via SMTP</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="smtpsecured" <?php echo checkbox( "smtpsecured" ) ?>>
                                    </td>
                                    <td>Sending mails via SMTP over TLS/SSL</td>
                                    <td><pre>(External Access)</pre></td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="smtptls" <?php echo checkbox( "smtptls" ) ?>>
                                    </td>
                                    <td>Sending mails via SMTP over STARTTLS</td>
                                    <td><pre>(External Access)</pre></td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="pop3" <?php echo checkbox( "pop3" ) ?>>
                                    </td>
                                    <td>Fetching mails via POP3</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="pop3secured" <?php echo checkbox( "pop3secured" ) ?>>
                                    </td>
                                    <td>Fetching mails via POP3 over TLS/SSL</td>
                                    <td><pre>(External Access)</pre></td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="pop3tls" <?php echo checkbox( "pop3tls" ) ?>>
                                    </td>
                                    <td>Fetching mails via POP3 over STARTTLS</td>
                                    <td><pre>(External Access)</pre></td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="imap" <?php echo checkbox( "imap" ) ?>>
                                    </td>
                                    <td>Fetching mails via IMAP</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="imapsecured" <?php echo checkbox( "imapsecured" ) ?>>
                                    </td>
                                    <td>Fetching mails via IMAP over TLS/SSL</td>
                                    <td><pre>(External Access)</pre></td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="imaptls" <?php echo checkbox( "imaptls" ) ?>>
                                    </td>
                                    <td>Fetching mails via IMAP over STARTTLS</td>
                                    <td><pre>(External Access)</pre></td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="deliver" <?php echo checkbox( "deliver" ) ?>>
                                    </td>
                                    <td>Accepting mails sent to this account on mail server</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="sogo" <?php echo checkbox( "sogo" ) ?>>
                                    </td>
                                    <td>SOGo Groupware (Calendar, Contacts, Tasks)</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="sieve" <?php echo checkbox( "sieve" ) ?>>
                                    </td>
                                    <td>Customize mail filter rule</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="sievesecured" <?php echo checkbox( "sievesecured" ) ?>>
                                    </td>
                                    <td>Customize mail filter rule over TLS/SSL</td>
                                    <td><pre>(External Access)</pre></td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="sievetls" <?php echo checkbox( "sievetls" ) ?>>
                                    </td>
                                    <td>Customize mail filter rule over STARTTLS</td>
                                    <td><pre>(External Access)</pre></td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="displayedInGlobalAddressBook" <?php echo checkbox( "displayedInGlobalAddressBook" ) ?>>
                                    </td>
                                    <td>Display mail address in global LDAP address book</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="shadowaddress" <?php echo checkbox( "shadowaddress" ) ?>>
                                    </td>
                                    <td>Alias addresses</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="forward" <?php echo checkbox( "forward" ) ?>>
                                    </td>
                                    <td>Forwarding mails to other addresses</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="recipientbcc" <?php echo checkbox( "recipientbcc" ) ?>>
                                    </td>
                                    <td>BCC incoming emails to other address</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="senderbcc" <?php echo checkbox( "senderbcc" ) ?>>
                                    </td>
                                    <td>BCC outgoing emails to other address</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="1">
                                        <input type="checkbox" name="externalAccessSettings" <?php echo checkbox( "externalAccessSettings" ) ?>>
                                    </td>
                                    <td>Allow external access</td>
                                    <td>&nbsp;</td>
                                </tr>

                                
                                
                            </table>
                            <?php plugins_process( "users_services" , "form" ); ?>
                            <button class="btn btn-success" name="submit" type="submit"><i class="fas fa-save"></i>&nbsp;Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>