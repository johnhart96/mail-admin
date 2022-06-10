<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
if( $_SESSION['admin_level'] !== "global" && $_SESSION['admin_level'] !== "self" ) {
    $dn = $_SESSION['admin_level'];
    $getDomain = ldap_search( $ds , $dn , "(domainName=*)" );
    $domain = ldap_get_entries( $ds , $getDomain );
    $domain = $domain[0];
    $domainToFind = $domain['domainname'][0];
    $title = "Organisation:";
} else {
    $domainToFind = filter_var( $_GET['domain'] , FILTER_SANITIZE_STRING );
    $title = "Domain:";
}
$entity = "@" . $domainToFind;


if( isset( $_POST['submit'] ) ) {
    if( isset( $_POST['enable'] ) ) {
        $active = 1;
    } else {
        $active = 0;
    }
    $delete = $apd->prepare( "DELETE FROM `greylisting` WHERE `account` =:account" );
    $delete->execute( [ ':account' => $entity ] );
    $insert = $apd->prepare( "INSERT INTO `greylisting` (`account`,`priority`,`sender`,`active`)VALUES(:account,60,'@.',:active)" );
    $insert->execute( [ ':account' => $entity , ':active' => $active ] );
}
if( isset( $_POST['add_whitelist'] ) ) {
    $sender = filter_var( $_POST['sender'] , FILTER_SANITIZE_STRING );
    $insert = $apd->prepare( "INSERT INTO `greylisting_whitelists` (`account`,`sender`) VALUES(:account,:sender)" );
    $insert->execute( [ ':account' => $entity , ':sender' => $sender ] );
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
                        <h1><?php echo $title ?></h1>
                        <p><em><?php echo $domainToFind; ?></em></p>
                        <?php
                        if( isset( $saved ) ) {
                            echo "<div class='alert alert-success'>Changes saved!</div>";
                        }
                        ?>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="domains.php">Domains</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $domainToFind; ?></li>
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
                                <a class="nav-link" href="domain_wblist.php?domain=<?php echo $domainToFind; ?>">White/Black List</a>
                            </li> 
                            <li class="nav-item">
                                <a class="nav-link active" href="domain_greylisting.php?domain=<?php echo $domainToFind; ?>">Greylisting</a>
                            </li>   
                        </ul>
                        <p>&nbsp;</p>
                        <?php
                        $getRule = $apd->prepare( "SELECT * FROM `greylisting` WHERE `account` =:relm LIMIT 1" );
                        $getRule->execute( [ ':relm' => $entity ] );
                        $rule = $getRule->fetch( PDO::FETCH_ASSOC );
                        $checked = "";
                        if( isset( $rule['id'] ) ) {
                            // Found
                            if( $rule['active'] == 1 ) {
                                $checked = "checked";
                            }
                        } else {
                            // Not found
                            $getGlobal = $apd->query( "SELECT * FROM `greylisting` WHERE `account` ='@.' LIMIT 1" );
                            $global = $getGlobal->fetch( PDO::FETCH_ASSOC );
                            if( isset( $global['id'] ) ) {
                                if( $global['active'] == 1 ) {
                                    $checked = "checked";
                                }
                            }
                        }
                        ?>
                        <input type="checkbox" name="enable" <?php echo $checked; ?>>&nbsp;<label for="enable">Enable</label>
                        
                        <?php plugins_process( "domain_greylisting" , "form" ); ?>
                        <p>&nbsp;</p>
                        <p><button type="submit" name="submit" class="btn btn-success"><i class='fas fa-save'></i>&nbsp;Save</button></p>
                    </form>
                    <div class="card">
                        <div class="card-header"><strong>Whitelist</strong></div>
                        <div class="card-body">
                           <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Sender</th>
                                        <th width="1">&nbsp;</th>
                                    </tr>
                                </thead>   
                                <tbody>
                                    <?php
                                    $getWhitelist = $apd->prepare( "SELECT * FROM `greylisting_whitelists` WHERE `account` =:account" );
                                    $getWhitelist->execute( [ ':account' => $entity ] );
                                    while( $row = $getWhitelist->fetch( PDO::FETCH_ASSOC ) ) {
                                        echo "<tr>";
                                        echo "<td>" . $row['sender'] . "</td>";
                                        echo "<td width='1'><a href='greylisting_sender_delete.php?id=" . $row['id'] . "&domain=" . str_replace( "@" , "" , $entity ) . "' class='btn btn-danger'>Delete</a></td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <form method="post">
                                        <tr>
                                            <td><input type="text" name="sender" placeholder="someone@someone.com or @someone.com" style="width: 100%"></td>
                                            <td><button style="width: 100%" type="submit" name="add_whitelist" class="btn btn-success">Save</button></td>
                                        </tr>
                                    </form>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>