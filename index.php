<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
if( isset( $_POST['submit_wblist'] ) ) {
    $address = filter_var( $_POST['address'] , FILTER_SANITIZE_STRING );
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
            
            <div class="row">&nbsp;</div>

            <div class="row">
                <div class="col">
                    <div class="card">
                        <div class="card-header"><strong>Quick Block/Allow:</strong></div>
                        <div class="card-body">
                            <?php
                            if( isset( $quickBlockDone ) ) {
                                echo "<div class='alert alert-success'>Quick Block/Allow added!</div>";
                            }
                            ?>
                            <form method="post">
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Address:</span></div>
                                    <input class="form-control" name="address">
                                    <select name="wb">
                                        <option value="B">Block</option>
                                        <option value="W">Allow</option>
                                    </select>
                                    <button type="submit" name="submit_wblist" class="btn btn-success">Add</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </body>
</html>