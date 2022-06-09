<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
globalOnly();
require 'inc/bind.php';

if( isset( $_POST['yes'] ) ) {
    $rid = filter_var( $_GET['rid'] , FILTER_SANITIZE_NUMBER_INT );
    $sid = filter_var( $_GET['sid'] , FILTER_SANITIZE_NUMBER_INT );
    $delete = $amavisd->prepare( "DELETE FROM `wblist` WHERE `rid` =:rid AND `sid` =:sid1 LIMIT 1" );
    $delete->execute( [ ':rid' => $rid , ':sid1' => $sid ] );
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
                        <p>Are you sure you want to delete this policy?</p>
                        <a href="server.php" class="btn btn-danger">No</a>
                        <button type="submit" name="yes" class="btn btn-success">Yes</button>
                    </form>
                    
                </div>
            </div>
        </div>
    </body>
</html>