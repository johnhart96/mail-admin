<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();

// Get domains
if( ! isset( $_GET['domain'] ) ) {
    
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
                        <h1>Settings</h1>
                        
                        
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>