<?php
if( isset( $_POST['submit'] ) ) {
    $BRANDING = filter_var( $_POST['BRANDING'] , FILTER_UNSAFE_RAW );
    $MAILQUOTA = filter_var( $_POST['MAILQUOTA'] , FILTER_SANITIZE_NUMBER_INT );
    $SERVERHOSTNAME = filter_var( $_POST['SERVERHOSTNAME'] , FILTER_UNSAFE_RAW );

    $APP_MAIL = filter_var( $_POST['APP_MAIL'] , FILTER_VALIDATE_URL );
    $APP_DRIVE = filter_var( $_POST['APP_DRIVE'] , FILTER_VALIDATE_URL );

    $LDAP_SERVER = filter_var( $_POST['LDAP_SERVER'] , FILTER_UNSAFE_RAW );
    $LDAP_BASEDN = filter_var( $_POST['LDAP_BASEDN'] , FILTER_UNSAFE_RAW );
    $LDAP_ADMINUSER = filter_var( $_POST['LDAP_ADMINUSER'] , FILTER_UNSAFE_RAW );
    $LDAP_ADMINPASSWORD = filter_var( $_POST['LDAP_ADMINPASSWD'] , FILTER_UNSAFE_RAW );

    $IAPD_HOST = filter_var( $_POST['IAPD_HOST'] , FILTER_UNSAFE_RAW );
    $IAPD_USER = filter_var( $_POST['IAPD_USER'] , FILTER_UNSAFE_RAW );
    $IAPD_PASSWORD = filter_var( $_POST['IAPD_PASSWORD'] , FILTER_UNSAFE_RAW );
    $IAPD_DB = filter_var( $_POST['IAPD_DB'] , FILTER_UNSAFE_RAW );

    $AMA_HOST = filter_var( $_POST['AMA_HOST'] , FILTER_UNSAFE_RAW );
    $AMA_USER = filter_var( $_POST['AMA_USER'] , FILTER_UNSAFE_RAW );
    $AMA_PASSWORD = filter_var( $_POST['AMA_PASSWORD'] , FILTER_UNSAFE_RAW );
    $AMA_DB = filter_var( $_POST['AMA_DB'] , FILTER_UNSAFE_RAW );

    $DOV_HOST = filter_var( $_POST['DOV_HOST'] , FILTER_UNSAFE_RAW );
    $DOV_USER = filter_var( $_POST['DOV_USER'] , FILTER_UNSAFE_RAW );
    $DOV_PASSWORD = filter_var( $_POST['DOV_PASSWORD'] , FILTER_UNSAFE_RAW );
    $DOV_DB = filter_var( $_POST['DOV_DB'] , FILTER_UNSAFE_RAW );


    $config_file = "
        <?php
        define( 'BRANDING' , '$BRANDING' ); // Set the brand name
        define( 'MAILQUOTA' , $MAILQUOTA );
        define( 'DEBUG' , FALSE );
        define( 'SERVERHOSTNAME' , '$SERVERHOSTNAME' ); 

        // Apps
        define( 'APP_MAIL' , '$APP_MAIL' );
        define( 'APP_DRIVE' , '$APP_DRIVE' );

        // LDAP
        define( 'LDAP_SERVER' , '$LDAP_SERVER' ); // LDAP server location
        define( 'LDAP_BASEDN' , '$LDAP_BASEDN' ); // LDAP Base DN
        define( 'LDAP_ADMINUSER' , '$LDAP_ADMINUSER' ); // FULL DN for admin user
        define( 'LDAP_ADMINPASSWD' , '$LDAP_ADMINPASSWORD' );

        define( 'LDAP_DOMAINDN' , 'o=domains,' . LDAP_BASEDN ); // Base DN to add domains

        // iRedAPD
        define( 'IAPD_HOST' , '$IAPD_HOST' );
        define( 'IAPD_USER' , '$IAPD_USER' );
        define( 'IAPD_PASSWORD' , '$IAPD_PASSWORD' );
        define( 'IAPD_DB' , '$IAPD_DB' );

        // Amavisd
        define( 'AMA_HOST' , '$AMA_HOST' );
        define( 'AMA_USER' , '$AMA_USER' );
        define( 'AMA_PASSWORD' , '$AMA_PASSWORD' );
        define( 'AMA_DB' , '$AMA_DB' );

        // Dovecot
        define( 'DOV_HOST' , '$DOV_HOST' );
        define( 'DOV_USER' , '$DOV_USER' );
        define( 'DOV_PASSWORD' , '$DOV_PASSWORD' );
        define( 'DOV_DB' , '$DOV_DB' );
        ?>
    ";
    if( file_exists( "../usr/config.php" ) ) {
        unlink( "../usr/config.php" );
    }
    $make_file = fopen( "../usr/config.php" , "w" );
    fwrite( $make_file , $config_file );
    fclose( $make_file );
    header( "Location: /" );

}
?>

<html>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        <title>Mail-admin - Installer</title>
        <link rel="stylesheet" href="../css/font-awesome.min.css">
    </head>
    <body style="background: #888;">
        <div class="container-fluid">
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col">
                    <div class="card" style="width: 800px; margin: 0 auto;">
                        <div class="card-header">
                            <h1 class="text-center">Mail-Admin - Installer</h1>
                        </div>
                        <div class="card-body">
                            <p>
                                Welcome to the Mail-Admin installer. We noticed that you do not have a configuration file. So we launched the installer to help you create one.
                            </p>
                            <?php
                            if( file_exists( "../usr/config.php" ) ) {
                                echo "<div class='alert alert-warning'><strong>WARNING:</strong> An existing config file already exists. By submitting this installer. It will override the existing config file!</div>";
                            }
                            if( ! is_writable( dirname( "../usr" ) ) ) {
                                echo "<div class='alert alert-danger'><strong>Error: Unable to write to the user directory. Unfortunatly the installer won't work until you can write to usr/</div>";
                            }
                            $install_ok = TRUE;
                            ?>
                            <p>
                                <strong>Pre-install checks:</strong>
                                <table class="table table-borderd table-stripped">
                                    <tr>
                                        <td>User directory is writable</td>
                                        <td width="10">
                                            <?php
                                            if( is_writable( dirname( "../usr" ) ) ) {
                                                echo "<span style='color: green;'>YES!</span>";
                                            } else {
                                                echo "<span style='color: red;'>NO!</span>";
                                                $install_ok = FALSE;
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Are you running at least PHP 8.0?</td>
                                        <td width="10">
                                            <?php
                                            $version = phpversion();
                                            $version = (double)$version;
                                            if( $version >= 8.0 ) {
                                                echo "<span style='color: green;'>YES!</span>";
                                            } else {
                                                echo "<span style='color: red;'>NO!</span>";
                                                $install_ok = FALSE;
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>LDAP PHP extension installed?</td>
                                        <td width="10">
                                            <?php
                                            $extensions = get_loaded_extensions();
                                            $check = array_search( "ldap" , $extensions );
                                            if( $check == 0 OR empty( $check ) ) {
                                                $install_ok = FALSE;
                                                echo "<span style='color: red;'>NO!</span>";
                                            } else {
                                                echo "<span style='color: green;'>YES!</span>";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>PDO MySQL PHP extension installed?</td>
                                        <td width="10">
                                        <?php
                                            $extensions = get_loaded_extensions();
                                            $check = array_search( "pdo_mysql" , $extensions );
                                            if( $check == 0 OR empty( $check ) ) {
                                                $install_ok = FALSE;
                                                echo "<span style='color: red;'>NO!</span>";
                                            } else {
                                                echo "<span style='color: green;'>YES!</span>";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </p>
                            <form method="post">
                                <p><strong>General details:</strong></p>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Branding</span></div>
                                    <input class="form-control" name="BRANDING" value="Mail-Admin">
                                </div>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Default mailbox quota</span></div>
                                    <input class="form-control" name="MAILQUOTA" value="6442450944">
                                </div>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Server hostname</span></div>
                                    <input class="form-control" name="SERVERHOSTNAME" value="<?php echo shell_exec( "hostname -f" ); ?>">
                                </div>
                                <br />

                                <p><strong>Apps:</strong></p>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Webmail URL:</span></div>
                                    <input class="form-control" name="APP_MAIL" value="">
                                </div>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Nextcloud URL:</span></div>
                                    <input class="form-control" name="APP_DRIVE" value="">
                                </div>
                                <br />

                                <p><strong>LDAP:</strong></p>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">LDAP Server:</span></div>
                                    <input class="form-control" name="LDAP_SERVER" value="ldap://127.0.0.1:389">
                                </div>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Base DN:</span></div>
                                    <input class="form-control" name="LDAP_BASEDN" value="dc=orgname,dc=local">
                                </div>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">User bind DN:</span></div>
                                    <input class="form-control" name="LDAP_ADMINUSER" value="cn=vmailadmin,dc=orgname,dc=local">
                                </div>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">User password:</span></div>
                                    <input class="form-control" name="LDAP_ADMINPASSWD" value="">
                                </div>
                                <br />

                                <p><strong>iRedAPD database:</strong></p>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Host:</span></div>
                                    <input class="form-control" name="IAPD_HOST" value="127.0.0.1">
                                </div>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">User:</span></div>
                                    <input class="form-control" name="IAPD_USER" value="">
                                </div>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Password:</span></div>
                                    <input class="form-control" name="IAPD_PASSWORD" value="">
                                </div>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Database:</span></div>
                                    <input class="form-control" name="IAPD_DB" value="iredapd">
                                </div>
                                <br />

                                <p><strong>Amavisd database:</strong></p>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Host:</span></div>
                                    <input class="form-control" name="AMA_HOST" value="127.0.0.1">
                                </div>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">User:</span></div>
                                    <input class="form-control" name="AMA_USER" value="">
                                </div>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Password:</span></div>
                                    <input class="form-control" name="AMA_PASSWORD" value="">
                                </div>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Database:</span></div>
                                    <input class="form-control" name="AMA_DB" value="amavisd">
                                </div>
                                <br />

                                <p><strong>Dovecot database:</strong></p>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Host:</span></div>
                                    <input class="form-control" name="DOV_HOST" value="127.0.0.1">
                                </div>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">User:</span></div>
                                    <input class="form-control" name="DOV_USER" value="">
                                </div>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Password:</span></div>
                                    <input class="form-control" name="DOV_PASSWORD" value="">
                                </div>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Database:</span></div>
                                    <input class="form-control" name="DOV_DB" value="vmail">
                                </div>
                                <br />
                                
                                <p><strong>Commit to install:</strong></p>
                                <?php
                                if( $install_ok ) {
                                    echo "<div class='alert alert-info'>When you have checked your details, click the install button below.</div>";
                                    echo "<button type='submit' name='submit' class='btn btn-success'>Install!</button>";
                                } else {
                                    echo "<div class='alert alert-danger'>Unable to start the install, please check the pre-install checks!</div>";
                                    echo "<button type='button' class='btn btn-secondary'>Install!</button>";
                                }
                                ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>