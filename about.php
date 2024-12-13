<?php
require_once 'inc/functions.php';
require_once 'inc/common_header.php';
require_once 'api/api.php';
securePage();
require 'inc/bind.php';
?>
<html>
    <head>
        <?php
        require 'inc/header.php';
        ?>
    </head>
    <body>
        <?php require 'inc/new_topbar.php'; ?>
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1 class="text-center"><i class="fas fa-envelope-open"></i>&nbsp;Mail-Admin</h1>
                    <p class="text-center"><em>Version: <?php echo (string)MAILADMIN_VERSION; ?></em></p>
                    <p class="text-center">Written by: John Hart</p>
                    <p class="text-center"><a href="https://github.com/johnhart96/mail-admin/">https://github.com/johnhart96/mail-admin/</a></p>
                    <p class="text-center">Released under the Apache 2.0 licence</p>
                    <?php
                    $licence = fopen( "LICENSE" , "r" );
                    $file = fread( $licence , filesize( "LICENSE" ) );
                    fclose( $licence );
                    ?>
                    <center>
                        <textarea style="width: 50%; background:none; height: 800px; margin: 0 auto;"><?php echo $file; ?></textarea>
                    </center>
                </div>
            </div>
        </div>
    </body>
</html>