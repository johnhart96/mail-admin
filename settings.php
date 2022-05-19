<?php
require 'inc/functions.php';
require 'inc/common_header.php';
require 'inc/bind.php';
securePage();

?>
<html>
    <head>
        <?php
        require 'inc/header.php';

        // Password Change
        if( ! empty( $_POST['password_new'] ) ) {
            $current = filter_var( $_POST['password_current'] , FILTER_SANITIZE_STRING );
            $new = filter_var( $_POST['password_new'] , FILTER_SANITIZE_STRING );
            $confirm = filter_var( $_POST['password_confirm'] , FILTER_SANITIZE_STRING );

            $hash_current = hash_password( $current );
            $hash_new = hash_password( $new );
            if( $new !== $confirm ) {
                $passwordMismatch = true;
            } else {
                $dn = $_SESSION['dn'];
                $info = array();
                $info['userPassword'] = $hash_new;
                $try = ldap_connect( LDAP_SERVER );
                ldap_set_option( $try, LDAP_OPT_PROTOCOL_VERSION, 3 );
                $bind = ldap_bind( $try , $_SESSION['dn'] , $current );
                if( $bind ) {
                    ldap_modify( $ds , $dn , $info ) or die( "Cannot update password!" );
                    $updated = true;
                } else {
                    $passwordMismatch = true;
                }
            }
        }
        // External Access
        $dn = $_SESSION['dn'];
        if( isset( $_POST['submit'] ) ) {
            error_reporting(0);
            if( isset( $_POST['smtp'] ) ) {
                ldap_mod_add( $ds , $dn , array( "enabledservice" => "smtpsecured" ) );
                ldap_mod_add( $ds , $dn , array( "enabledservice" => "smtptls" ) ); 
            } else {
                ldap_mod_del( $ds , $dn , array( "enabledservice" => "smtpsecured" ) );
                ldap_mod_del( $ds , $dn , array( "enabledservice" => "smtptls" ) ); 
            }
            if( isset( $_POST['imap'] ) ) {
                ldap_mod_add( $ds , $dn , array( "enabledservice" => "imapsecured" ) );
                ldap_mod_add( $ds , $dn , array( "enabledservice" => "imaptls" ) );
            } else {
                ldap_mod_del( $ds , $dn , array( "enabledservice" => "imapsecured" ) );
                ldap_mod_del( $ds , $dn , array( "enabledservice" => "imaptls" ) ); 
            }
            if( isset( $_POST['pop'] ) ) {
                ldap_mod_add( $ds , $dn , array( "enabledservice" => "pop3secured" ) );
                ldap_mod_add( $ds , $dn , array( "enabledservice" => "pop3tls" ) );
            } else {
                ldap_mod_del( $ds , $dn , array( "enabledservice" => "pop3secured" ) );
                ldap_mod_del( $ds , $dn , array( "enabledservice" => "pop3tls" ) );
            }
            if( isset( $_POST['sieve'] ) ) {
                ldap_mod_add( $ds , $dn , array( "enabledservice" => "sievetls" ) );
                ldap_mod_add( $ds , $dn , array( "enabledservice" => "sievesecured" ) );
            } else {
                ldap_mod_del( $ds , $dn , array( "enabledservice" => "sievetls" ) );
                ldap_mod_del( $ds , $dn , array( "enabledservice" => "sievesecured" ) );
            }
            error_reporting( E_ALL );
        }
        ?>
    </head>
    <body>
        <?php require 'inc/topbar.php'; ?>
        <div class="container">
            <div class="row">
                <div class="col">
                    <form method="post">
                        <h1>Settings</h1>
                        <?php
                        if( isset( $passwordMismatch ) ) {
                            echo "<div class='alert alert-success'>Your new password did not match!</div>";
                        } else if( isset( $updated ) ) {
                            echo "<div class='alert alert-success'>Settings updated!</div>";
                        }
                        $getUser = ldap_search( $ds , $_SESSION['dn'] , "(uid=*)" );
                        $entry = ldap_get_entries( $ds , $getUser );
                        $entry = $entry[0];
                        function checkbox( $h ) {
                            global $entry;
                            foreach( $entry['enabledservice'] as $service ) {
                                if( $service == $h ) {
                                    echo "checked";
                                }
                            }
                        }
                        ?>


                        <h2>Change Password</h2>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Current Password:</span></div>
                            <input type="password" name="password_current" class="form-control">
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">New Password:</span></div>
                            <input type="password" name="password_new" class="form-control">
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Confirm Password:</span></div>
                            <input type="password" name="password_confirm" class="form-control">
                        </div>
                        <p>&nbsp;</p>

                        <h2>External Access</h2>
                        <?php
                        // Check external access
                        $filter = "(enabledService=externalAccessSettings)";
                        $search = ldap_search( $ds , $_SESSION['dn'] , $filter );
                        $result = ldap_get_entries( $ds , $search );
                        if( $result['count'] == 1 ) {
                            ?>
                                <div class="alert alert-info">External access allows you to access your email outside of webmail.</div>
                                <table class="table">
                                    <tr>
                                        <td width="1"><input type="checkbox" name="smtp" <?php checkbox( "smtptls" ); ?>></td>
                                        <td>SMTP</th>
                                    </tr>
                                    <tr>
                                        <td width="1"><input type="checkbox" name="imap" <?php checkbox( "imaptls" ); ?>></td>
                                        <td>IMAP</th>
                                    </tr>
                                    <tr>
                                        <td width="1"><input type="checkbox" name="pop" <?php checkbox( "pop3tls" ); ?>></td>
                                        <td>POP</th>
                                    </tr>
                                    <tr>
                                        <td width="1"><input type="checkbox" name="sieve" <?php checkbox( "sievetls" ); ?>></td>
                                        <td>Sieve</th>
                                    </tr>
                                </table>
                            <?php
                        } else {
                            echo "<div class='alert alert-warning'>External access is disabled by your administrator!</div>";
                        }
                        ?>




                        <button type="submit" name="submit" class="btn btn-success">Change</button>


                    </form>
                </div>
            </div>
        </div>
    </body>
</html>