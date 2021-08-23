<?php
require 'inc/functions.php';
require 'inc/common_header.php';
if( isset( $_POST['submit'] ) ) {
    require 'inc/bind.php';
    $given_username = filter_var( $_POST['username'] , FILTER_SANITIZE_STRING );
    $given_password = filter_var( $_POST['password'] , FILTER_SANITIZE_STRING );

    require 'inc/bind.php';
    $filter = "(uid=$given_username)";
    $search = ldap_search( $ds , LDAP_BASEDN , $filter );
    $entry = ldap_get_entries( $ds , $search  );
    $entry = $entry[0];
    $dn = $entry['dn'];
    if( ldap_bind( $ds , $dn , $given_password ) ) {
        $_SESSION['mail-admin'] = $given_username;
        if( isset( $entry['domainglobaladmin'][0] ) ) {
            if( isset( $_SESSION['mail-admin'] ) ) {
                header( "Location: index.php" );
            }
        } else {
            header( "Location: login.php?loginerror" );
        }
    } else {
        header( "Location: login.php?loginerror" );
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
                <input id="username" name="username" type="text" placeholder="Username">
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