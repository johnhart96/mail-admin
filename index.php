<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
plugins_process( "groups_edit" , "submit" );
plugins_process( "index" , "submit" );
?>
<html>
    <head>
        <?php
        require 'inc/header.php';
        if( empty( $_SESSION['ldap']['displayname'][0] ) ) {
            $_SESSION['ldap']['displayname'] = array( 0 => $_SESSION['ldap']['cn'][0] );
        }
        ?>
    </head>
    <body>
        <?php require 'inc/topbar.php'; ?>
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1>
                        Welcome <?php echo ucfirst( $_SESSION['ldap']['displayname'][0] ); ?>!</h1>
                    <?php
                    if( DEBUG ) {
                        echo "<p id='dialog-error' class='white'><strong>WARNING!</strong> Debug is enabled. Do not leave debug enabled in production enviroments. It may give away secure info!</p>";
                        echo "<p>Admin Level: " . $_SESSION['admin_level'] . "</p>";
                        echo "<pre>";
                        print_r( $_SESSION['ldap'] );
                        echo "</pre>";
                    }
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="appdraw">
                        <a href="<?php echo APP_MAIL; ?>" target="_blank" class="btn btn-success"><i class="fa fa-envelope"></i>&nbsp;Mail</a>
                        <a href="<?php echo APP_DRIVE; ?>" target="_blank" class="btn btn-success"><i class="fa fa-folder"></i>&nbsp;Drive</a>
                    </div>
                </div>
            </div>
            <?php plugins_process( "index" , "form" ); ?>
        </div>
    </body>
</html>