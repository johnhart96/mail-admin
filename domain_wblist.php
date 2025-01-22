<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
if( $_SESSION['admin_level'] !== "global" && $_SESSION['admin_level'] !== "self" ) {
    // domain admin
    $dn = $_SESSION['admin_level'];
    $getDomain = ldap_search( $ds , $dn , "(domainname=*)" );
    $domain = ldap_get_entries( $ds , $getDomain );
    $count = (int)$domain['count'];
    $title = "Organisation:";
    $filter = "(domainName=" . $domain[0]['domainname'][0] . ")";
} else {
    // Global admin
    $domainToFind = filter_var( $_GET['domain'] , FILTER_UNSAFE_RAW );
    $filter = "(domainName=$domainToFind)";
    $result = ldap_search( $ds , LDAP_BASEDN , $filter );
    $domain = ldap_get_entries( $ds , $result );
    $dn = $domain[0]['dn'];
    $count = (int)$domain['count'];
    unset( $domain['count'] );
    $title = "Domain:";
}
$domainToFind = $domain[0]['domainname'][0];
unset( $domain['count'] );
$entity = "@" . $domain[0]['domainname'][0];

$filter = "(cn=catch-all)";
$search = ldap_search( $ds , "ou=Users," . $dn , $filter );
$entry = ldap_get_entries( $ds , $search );
$catchCount = (int)$entry['count'];

// Delete entry
if( isset( $_GET['rid'] ) && isset( $_GET['sid'] ) ) {
    $rid = filter_var( $_GET['rid'] , FILTER_SANITIZE_NUMBER_INT );
    $sid = filter_var( $_GET['sid'] , FILTER_SANITIZE_NUMBER_INT );
    $delete = $amavisd->prepare( "DELETE FROM `wblist` WHERE `rid` =:rid AND `sid` =:sid1 LIMIT 1" );
    $delete->execute( [ ':rid' => $rid , ':sid1' => $sid ] );
}

// New entry
if( isset( $_POST['submit_wblist'] ) ) {
    $address = filter_var( $_POST['address'] , FILTER_UNSAFE_RAW );
    $wb = filter_var( $_POST['wb'] , FILTER_UNSAFE_RAW );
    // Get RID
    $searchForDomain = $amavisd->prepare( "SELECT * FROM `users` WHERE `email` =:domain LIMIT 1" );
    $searchForDomain->execute( [ ':domain' => $entity ] );
    $result = $searchForDomain->fetch( PDO::FETCH_ASSOC );
    if( isset( $result['id'] ) ) {
        // Found one
        $rid = $result['id'];
    } else {
        $addDomain = $amavisd->prepare( "INSERT INTO `users`(`priority`,`policy_id`,`email`) VALUES(5,0,:email)" );
        $addDomain->execute( [ ':email' => $entity ] );
        $getLast = $amavisd->query( "SELECT `id` FROM `users` ORDER BY `id` DESC LIMIT 1" );
        $result = $getLast->fetch( PDO::FETCH_ASSOC );
        $rid = $result['id'];
    }
    // Get sid
    $searchForSid = $amavisd->prepare( "SELECT `id` FROM `mailaddr` WHERE `email` =:email LIMIT 1" );
    $searchForSid->execute( [ ':email' => $address ] );
    $result = $searchForSid->fetch( PDO::FETCH_ASSOC );
    if( isset( $result['id'] ) ) {
        // Found
        $sid = $result['id'];
    } else {
        $addSID = $amavisd->prepare( "INSERT INTO `mailaddr` (`priority`,`email`) VALUES(10,:email)" );
        $addSID->execute( [ ':email' => $address ] );
        $getLast = $amavisd->query( "SELECT `id` FROM `mailaddr` ORDER BY `id` DESC LIMIT 1" );
        $result = $getLast->fetch( PDO::FETCH_ASSOC );
        $sid = $result['id'];
    }
    // Insert
    $insert = $amavisd->prepare( "INSERT INTO `wblist` (`rid`,`sid`,`wb`) VALUES(:rid,:sid1,:wb)" );
    $insert->execute( [ ':rid' => $rid , ':sid1' => $sid , ':wb' => $wb ] );
    $added = TRUE;
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
                        <?php
                        if( $count !== 1 ) {
                            die( "Error: too many domains returned!" );
                        }
                        $domain = $domain[0];
                        $dn = $domain['dn'];

                        ?>
                        <h1><?php echo $title ?></h1>
                        <p><em><?php echo $domain['domainname'][0]; ?></em></p>
                        <?php
                        if( isset( $_GET['saved'] ) ) {
                            echo "<div class='alert alert-success'>Changes saved!</div>";
                        }
                        ?>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="domains.php">Domains</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $domain['domainname'][0]; ?></li>
                            </ol>
                        </nav>
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="domain_edit.php?domain=<?php echo $domainToFind; ?>">General</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="domain_alias.php?domain=<?php echo $domainToFind; ?>">Aliases</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="domain_bcc.php?domain=<?php echo $domainToFind; ?>">BCC</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="domain_catchall.php?domain=<?php echo $domainToFind; ?>">Catch-all</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="domain_backupmx.php?domain=<?php echo $domainToFind; ?>">Backup MX</a>
                            </li>   
                            <li class="nav-item">
                                <a class="nav-link active" href="domain_wblist.php?domain=<?php echo $domainToFind; ?>">White/Black List</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="domain_greylisting.php?domain=<?php echo $domainToFind; ?>">Greylisting</a>
                            </li> 
                        </ul>
                        <p>&nbsp;</p>
                        <?php
                        if( isset( $added ) ) {
                            echo "<div class='alert alert-success'>Item added!</div>";
                        }
                        ?>
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
                                // Get RID
                                $searchForDomain = $amavisd->prepare( "SELECT * FROM `users` WHERE `email` =:domain LIMIT 1" );
                                $searchForDomain->execute( [ ':domain' => $entity ] );
                                $result = $searchForDomain->fetch( PDO::FETCH_ASSOC );
                                if( isset( $result['id'] ) ) {
                                    // Found one
                                    $rid = $result['id'];
                                } else {
                                    $addDomain = $amavisd->prepare( "INSERT INTO `users`(`priority`,`policy_id`,`email`) VALUES(5,0,:email)" );
                                    $addDomain->execute( [ ':email' => $entity ] );
                                    $getLast = $amavisd->query( "SELECT `id` FROM `users` ORDER BY `id` DESC LIMIT 1" );
                                    $result = $getLast->fetch( PDO::FETCH_ASSOC );
                                    $rid = $result['id'];
                                }
                                $getList = $amavisd->prepare( "SELECT * FROM `wblist` WHERE `rid` =:rid" );
                                $getList->execute( [ ':rid' => $rid ] );
                                $lookupSID = $amavisd->prepare( "SELECT * FROM `mailaddr` WHERE `id` =:sid1 LIMIT 1" );
                                while( $row = $getList->fetch( PDO::FETCH_ASSOC ) ) {
                                    echo "<tr>";
                                    $lookupSID->execute( [ ':sid1' => $row['sid'] ] );
                                    $result = $lookupSID->fetch( PDO::FETCH_ASSOC );
                                    echo "<td>" . $result['email'] . "</td>";
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
                                    echo "<td><a href='domain_wblist.php?domain=$domainToFind&rid=" . $rid . "&sid=" . $row['sid'] . "' class='btn btn-danger'><i class='fas fa-trash'></i></a></td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td><input name="address" class="form-control"></td>
                                    <td>
                                        <select class="form-control" name="wb">
                                            <option value="B">Block</option>
                                            <option value="W">Allow</option>
                                        </select>
                                    </td>
                                    <td width="1"><button style="width: 100%;" type="submit" name="submit_wblist" class="btn btn-success"><i class="fas fa-plus"></i><button></td>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <?php plugins_process( "domain_wblist" , "form" ); ?>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>