<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
if( isset( $_POST['submit'] ) ) {
    $address = filter_var( $_POST['address'] , FILTER_SANITIZE_STRING );
    $domain = filter_var( $_POST['domain'] , FILTER_SANITIZE_STRING );
    $firstname = filter_var( $_POST['firstname'] , FILTER_SANITIZE_STRING );
    $lastname = filter_var( $_POST['lastname'] , FILTER_SANITIZE_STRING );
    $password = filter_var( $_POST['password'] , FILTER_SANITIZE_STRING );
    $username = filter_var( $_POST['username'] , FILTER_SANITIZE_STRING );
    $displayname = filter_var( $_POST['displayname'] , FILTER_SANITIZE_STRING );
    $description = filter_var( $_POST['description'] , FILTER_SANITIZE_STRING );

    // Check that user dont already exist
    $filter = "(mail=" . $address . "@" . $domain . ")";
    $search = ldap_search( $ds , LDAP_BASEDN , $filter );
    $entry = ldap_get_entries( $ds , $search );
    if( isset( $entry[0]['mail'][0] ) ) {
        $addressAlreadyExists = true;
    } else {
        // Create the user
        $info['cn'] = $displayname;
        $info['givenName'] = $firstname;
        $info['surname'] = $lastname;
        $info['uid'] = $username;
        $info['displayName'] = $displayname;

        // Object classes
        $info['objectClass'][0] = "inetOrgPerson";
        $info['objectClass'][1] = "mailUser";
        $info['objectClass'][2] = "shadowAccount";
        $info['objectClass'][3] = "amavisAccount";

        // Enable services
        $info['enabledService'][0] = "internal";
        $info['enabledService'][1] = "doveadm";
        $info['enabledService'][2] = "lib-storage";
        $info['enabledService'][3] = "indexer-worker";
        $info['enabledService'][4] = "dsync";
        $info['enabledService'][5] = "quota-status";
        $info['enabledService'][6] = "mail";
        $info['enabledService'][7] = "smtp";
        $info['enabledService'][8] = "smtpsecured";
        $info['enabledService'][9] = "smtptls";
        $info['enabledService'][10] = "pop3";
        $info['enabledService'][11] = "pop3secured";
        $info['enabledService'][12] = "pop3tls";
        $info['enabledService'][13] = "imap";
        $info['enabledService'][14] = "imapsecured";
        $info['enabledService'][15] = "imaptls";
        $info['enabledService'][16] = "managesieve";
        $info['enabledService'][17] = "managesievesecured";
        $info['enabledService'][18] = "managesievetls";
        $info['enabledService'][19] = "sieve";
        $info['enabledService'][20] = "sievesecured";
        $info['enabledService'][21] = "sievetls";
        $info['enabledService'][22] = "deliver";
        $info['enabledService'][23] = "lda";
        $info['enabledService'][24] = "lmtp";
        $info['enabledService'][25] = "recipientbcc";
        $info['enabledService'][26] = "senderbcc";
        $info['enabledService'][27] = "forward";
        $info['enabledService'][28] = "shadowaddress";
        $info['enabledService'][29] = "displayedInGlobalAddressBook";
        $info['enabledService'][30] = "sogo";

            
        // Misc
        $info['accountSetting']  = "timezone:Europe/London";
        $info['mailboxfolder'] = "Maildir";
        $info['mailboxformat'] = "maildir";
        $info['accountStatus'] = "active";
        $info['amavisLocal'] = "TRUE";
        $info['mail'] = $address . "@" . $domain;
        $info['homeDirectory'] = "/var/vmail/vmail1/$domain/" . substr( $address , 0 , 1 ) . "/" . substr( $address , 1 , 1 ) . "/" .  substr( $address , 2 , 1 ) . "/" . $address . "-" . date( "Y.m.d.h.i.s" ) . "/";
        $info['userPassword'] = hash_password( $password );
        $info['description'] = $description;

        // Quota
        $getDomain = ldap_search( $ds , LDAP_DOMAINDN , "(domainName=$domain)" );
        $entries = ldap_get_entries( $ds , $getDomain );
        $quota = MAILQUOTA;
        foreach( $entries[0]['accountsetting'] as $setting ) {
            $part = explode( $setting , ":" );
            if( $part[0] == "defaultQuota" ) {
                $quota = $part[1];
            }
        }
        $info['mailQuota'] = $quota;

        // Add the user
        $dn = "mail=" . $address . "@" . $domain . ",ou=Users,domainName=" . $domain . "," . LDAP_DOMAINDN;
        if( ldap_add( $ds , $dn , $info ) ) {
            plugins_process( "users_new" , "submit" );
            watchdog( "Adding user `" . $address . "@" . $domain . "`" );
            header( "Location: users.php?saved" );
        } else {
            die( "cannot add" );
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
                        <h1>New Users</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="users.php">Users</a></li>
                                <li class="breadcrumb-item active" aria-current="page">New</li>
                            </ol>
                        </nav>

                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Address:</span></div>
                            <input type="text" name="address" class="form-control">
                            <?php
                            if( $_SESSION['admin_level'] !== 'global' ) {
                                require 'inc/relmset.php';
                                $domain = str_replace( LDAP_DOMAINDN , "" , $relm );
                                $domain = str_replace( "domainName=" , "" , $domain );
                                $domain = str_replace( "," , "" , $domain );
                                echo "<input type='hidden' name='domain' value='" . filter_var( $domain , FILTER_SANITIZE_STRING ) . "'>";
                                echo "<div class='input-group-append'><span class='input-group-text'>@" . filter_var( $domain , FILTER_SANITIZE_STRING ) . "</span></div>";
                            } else {
                                echo "<span class='input-group-text'>@</span>";
                                echo "<select name='domain' class='form-control'>";
                                $filter = "(domainName=*)";
                                $getDomains = ldap_search( $ds , LDAP_DOMAINDN , $filter );
                                $domains = ldap_get_entries( $ds , $getDomains );
                                unset( $domains['count'] );
                                foreach( $domains as $domain ) {
                                    echo "<option>" . $domain['domainname'][0] . "</option>";
                                }
                                echo "</select>";
                            }
                            ?>
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Username:</span></div>
                            <input type="text" name="username" class="form-control">
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Firstname:</span></div>
                            <input type="text" name="firstname" class="form-control">
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Lastname:</span></div>
                            <input type="text" name="lastname" class="form-control">
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Display name:</span></div>
                            <input type="text" name="displayname" class="form-control">
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Password:</span></div>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Description:</span></div>
                            <input type="text" name="description" class="form-control">
                        </div>

                        <?php plugins_process( "users_new" , "form" ); ?>
                        <p>&nbsp;</p>
                        <p><button type="submit" name="submit" class="btn btn-success"><i class="fas fa-save"></i>&nbsp;Save</button></p>
                        
                        
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>