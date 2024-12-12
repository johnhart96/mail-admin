<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
$user = filter_var( $_GET['user'] , FILTER_SANITIZE_STRING );
$part = explode( "@" , $user );
$domain = $part[1];
// Get entry
$filter = "(mail=$user)";
$search = ldap_search( $ds , LDAP_BASEDN , $filter );
$entry = ldap_get_entries( $ds , $search );
unset( $entry['count'] );
$entry = $entry[0];
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
                        <h1>Edit User</h1>
                        <?php
                        if( isset( $_GET['saved'] ) ) {
                            echo "<div class='alert alert-success'>Changes saved!</div>";
                        }
                        ?>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="users.php">Mailboxes</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $user; ?></li>
                            </ol>
                        </nav>
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="users_edit.php?user=<?php echo $user; ?>">General</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="users_alias.php?user=<?php echo $user; ?>">Aliases</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="users_services.php?user=<?php echo $user; ?>">Services</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="users_forwarding.php?user=<?php echo $user; ?>">Forwarding</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="users_bcc.php?user=<?php echo $user; ?>">BCC</a>
                            </li> 
                            <li class="nav-item">
                                <a class="nav-link active" href="users_wblist.php?user=<?php echo $user; ?>">White/Black List</a>
                            </li> 
                        </ul>
                        <p>&nbsp;</p>
                        <div class="mb-3">
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
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>