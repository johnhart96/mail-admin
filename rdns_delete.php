<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
globalOnly();
require 'inc/bind.php';

if( isset( $_POST['yes'] ) ) {
    $id = filter_var( $_GET['id'] , FILTER_SANITIZE_NUMBER_INT );
    $delete = $apd->prepare( "DELETE FROM `wblist_rdns` WHERE `id` =:id LIMIT 1" );
    $delete->execute( [ ':id' => $id ] );
    header( "Location:server.php" );
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
                    <h1>Delete rule?</h1>
                    <form method="post">
                        <p>Are you sure you want to delete this rule?</p>
                        <a href="server.php" class="btn btn-danger">No</a>
                        <button type="submit" name="yes" class="btn btn-success">Yes</button>
                    </form>
                    
                </div>
            </div>
        </div>
    </body>
</html>