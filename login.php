<?php
require 'inc/functions.php';
require 'inc/common_header.php';
if( isset( $_POST['submit'] ) ) {
    require 'inc/bind.php';
    $given_username = filter_var( $_POST['username'] , FILTER_SANITIZE_STRING );
    $given_password = filter_var( $_POST['password'] , FILTER_SANITIZE_STRING );

    require 'inc/bind.php';
    $filter = "(mail=$given_username)";
    $search = ldap_search( $ds , LDAP_BASEDN , $filter );
    $entry = ldap_get_entries( $ds , $search  );
    $entry = $entry[0];
    $dn = $entry['dn'];
    if( DEBUG ) {
        echo "<pre>";
        print_r( $entry );
        echo "</pre>";
    }
    if( ldap_bind( $ds , $dn , $given_password ) ) {
        $_SESSION['mail-admin'] = $given_username;
        $_SESSION['dn'] = $dn;
        $filter = "(mail=$given_username)";
        $search = ldap_search( $ds , $dn , $filter );
        $user = ldap_get_entries( $ds , $search );
        $_SESSION['ldap'] = $user[0];
        if( isset( $entry['domainglobaladmin'][0] ) ) {
            $_SESSION['admin_level'] = "global";
            
        } else {
            // Check if user has domain admin privs
            $filter = "(enabledService=domainAdmin)";
            $search = ldap_search( $ds , $dn , $filter );
            $result = ldap_get_entries( $ds , $search );
            if( $result['count'] == 1 ) {
                // Yes
                $admin_relm = str_replace( "mail=" . $entry['mail'][0] . ",ou=Users," , "" , $dn );
                $_SESSION['admin_level'] = $admin_relm;
            } else {
                // No
                $_SESSION['admin_level'] = "self";
            }
        }
    }
    if( isset( $_SESSION['mail-admin'] ) ) {
        header( "Location:index.php" );
    } else {
        header( "Location:login.php?loginerror" );
    }
}
?>
<html>
    <head>
        <title>Login</title>
        <?php
        require 'inc/header.php';
        ?>
        <link href="css/login.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <div class="space"></div>
        <div id="wrap">
            <center><p style="font-size: 100px;"><i class="fas fa-envelope-open"></i></p></center>
            <h1><?php echo BRANDING; ?></h1>
            <?php
            if( isset( $_GET['loginerror'] ) ) {
                echo "<div id='dialog-error'>Login failed!</div>";
            }
            ?>
            <form method="post">
                <input autofocus id="username" name="username" type="text" placeholder="Email">
                <input id="password" name="password" type="password" placeholder="Password">
                <center>
                    <button id="login" type="submit" name="submit">Login</button>
                </center>
                
            </form>
        </div>
        <div id="footer">
            &copy;Copyright John Hart (2021). All rights Reserved!
        </div>
    </body>
</html>