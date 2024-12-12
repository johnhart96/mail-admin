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
        // WBList
        if( isset( $_POST['submit_wblist'] ) ) {
            $address = filter_var( $_POST['address'] , FILTER_SANITIZE_STRING );
            $wb = filter_var( $_POST['wb'] , FILTER_SANITIZE_STRING );
            
            // Search for the address
            $searchAddress = $amavisd->prepare( "SELECT * FROM `mailaddr` WHERE `email` =:email LIMIT 1" );
            $searchAddress->execute( [ ':email' => $address ] );
            $result = $searchAddress->fetch( PDO::FETCH_ASSOC );
            if( isset( $result['id'] ) ) {
                // Found one
                $sid = $result['id'];
            } else {
                // Did not find one
                $addMailAddr = $amavisd->prepare( "INSERT INTO `mailaddr` (`priority`,`email`) VALUES(:priority,:email)" );
                $addMailAddr->execute( [ ':priority' => 10 , ':email' => $address ] );
                $getLastEntry = $amavisd->query( "SELECT `id` FROM `mailaddr` ORDER BY `id` DESC LIMIT 1" );
                $lastEntry = $getLastEntry->fetch( PDO::FETCH_ASSOC );
                $sid = $lastEntry['id'];
            }
            // Search for RID
            $getGlobal = $amavisd->prepare( "SELECT * FROM `users` WHERE `email` =:source LIMIT 1" );
            $getGlobal->execute( [ ':source' => $_SESSION['ldap']['mail'][0] ] );
            $result = $getGlobal->fetch( PDO::FETCH_ASSOC );
            if( isset( $result['id'] ) ) {
                // Found one
                $rid = $result['id'];
            } else {
                $insertRID = $amavisd->prepare( "INSERT INTO `users`(`priority`,`policy_id`,`email`) VALUES(10,0,:email)" );
                $insertRID->execute( [ ':email' => $_SESSION['ldap']['mail'][0] ] );
                $getLastEntry = $amavisd->query( "SELECT `id` FROM `users` ORDER BY `id` DESC LIMIT 1" );
                $lastEntry = $getLastEntry->fetch( PDO::FETCH_ASSOC );
                $rid = $lastEntry['id'];
            }
        
            // Insert policy
            
            $insert = $amavisd->prepare( "INSERT INTO `wblist` (`sid`,`rid`,`wb`) VALUES(:sid1,:rid,:wb)" );
            $insert->execute( [ ':sid1' => $sid , ':rid' => $rid , ':wb' => $wb ] );
            $quickBlockDone = TRUE;
        }
        ?>
    </head>
    <body>
        <?php require 'inc/new_topbar.php'; ?>
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
                    <p>&nbsp;</p>
                    <h2>White/Black List</h2>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Address</th>
                                <th>Action</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Search for RID
                            $getGlobal = $amavisd->prepare( "SELECT * FROM `users` WHERE `email` =:source LIMIT 1" );
                            $getGlobal->execute( [ ':source' => $_SESSION['ldap']['mail'][0] ] );
                            $result = $getGlobal->fetch( PDO::FETCH_ASSOC );
                            if( isset( $result['id'] ) ) {
                                // Found one
                                $rid = $result['id'];
                            } else {
                                $insertRID = $amavisd->prepare( "INSERT INTO `users`(`priority`,`policy_id`,`email`) VALUES(10,0,:email)" );
                                $insertRID->execute( [ ':email' => $_SESSION['ldap']['mail'][0] ] );
                                $getLastEntry = $amavisd->query( "SELECT `id` FROM `users` ORDER BY `id` DESC LIMIT 1" );
                                $lastEntry = $getLastEntry->fetch( PDO::FETCH_ASSOC );
                                $rid = $lastEntry['id'];
                            }
                            $getWB = $amavisd->prepare( "SELECT * FROM `wblist` WHERE `rid` =:rid" );
                            $getWB->execute( [ ':rid' => $rid ] );
                            $getSID = $amavisd->prepare( "SELECT * FROM `mailaddr` WHERE `id` =:id LIMIT 1" );
                            while( $row = $getWB->fetch( PDO::FETCH_ASSOC ) ) {
                                echo "<tr>";
                                $sid = $row['sid'];
                                $getSID->execute( [ ':id' => $sid ] );
                                $result = $getSID->fetch( PDO::FETCH_ASSOC );
                                $address = $result['email'];
                                echo "<td>" . $address . "</td>";
                                echo "<td>";
                                switch( $row['wb'] ) {
                                    case "B":
                                        echo "Block";
                                        break;
                                    case "W":
                                        echo "Allow";
                                        break;
                                }
                                echo "</td>";
                                echo "<td width='1'><a href='wblist_delete.php?rid=" . $rid . "&sid=" . $sid . "' class='btn btn-danger'>Delete</a></td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <form method="post">
                                    <td><input style="width: 100%" name="address"></td>
                                    <td>
                                        <select style="width: 100%" name="wb">
                                            <option value="B">Block</option>
                                            <option value="W">Allow</option>
                                        </select>
                                    </td>
                                    <td width="1"><button style="width: 100%" class="btn btn-success" type="submit" name="submit_wblist">Save</button></td>
                                </form>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>