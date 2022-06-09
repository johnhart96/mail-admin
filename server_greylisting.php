<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
globalOnly();
require 'inc/bind.php';
// Domain Add
if( isset( $_POST['domain_add'] ) ) {
    $domain = filter_var( $_POST['domain'] , FILTER_SANITIZE_STRING );
    $insert = $apd->prepare( "INSERT INTO `greylisting_whitelist_domains` (`domain`) VALUE(:domain)" );
    $insert->execute( [ ':domain' => $domain ] );
}
// SPF Add
if( isset( $_POST['spf_add'] ) ) {
    $sender = filter_var( $_POST['sender'] , FILTER_SANITIZE_STRING );
    $comment = filter_var( $_POST['comment'] , FILTER_SANITIZE_STRING );
    $add = $apd->prepare( "INSERT INTO `greylisting_whitelist_domain_spf` (`account`,`sender`,`comment`) VALUES('@.',:sender,:comment)" );
    $add->execute( [ ':sender' => $sender , ':comment' => $comment ] );
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
                    <h1>Greylisting</h1>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="card">
                        <div class="card-header"><strong>Whitelist (Domains)</strong></div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Sender</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $getDomains = $apd->query( "SELECT * FROM `greylisting_whitelist_domains` ORDER BY `domain` ASC" );
                                    while( $row = $getDomains->fetch( PDO::FETCH_ASSOC ) ) {
                                        echo "<tr>";
                                        echo "<td>" . $row['domain'] . "</td>";
                                        echo "<td width='1'><a href='greylisting_domain_delete.php?id=" . $row['id'] . "' class='btn btn-danger'>Delete</a></td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <form method="post">
                                            <td><input placeholder="Domain" type="text" name="domain" style="width: 100%"></td>
                                            <td><button style="width: 100%;" type="submit" name="domain_add" class="btn btn-success">Save</button></td>
                                        </form>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col">
                    <div class="card">
                        <div class="card-header"><strong>Whitelist (SPF)</strong></div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Sender</th>
                                        <th>Comment</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $getDomains = $apd->query( "SELECT * FROM `greylisting_whitelist_domain_spf` ORDER BY `sender` ASC" );
                                    while( $row = $getDomains->fetch( PDO::FETCH_ASSOC ) ) {
                                        echo "<tr>";
                                        echo "<td>" . $row['sender'] . "</td>";
                                        echo "<td>" . $row['comment'] . "</td>";
                                        echo "<td width='1'><a href='greylisting_spf_delete.php?id=" . $row['id'] . "' class='btn btn-danger'>Delete</a></td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <form method="post">
                                            <td><input placeholder="Domain or IP Address" type="text" name="sender" style="width: 100%"></td>
                                            <td><input type="text" name="comment" style="width: 100%"></td>
                                            <td><button style="width: 100%;" type="submit" name="spf_add" class="btn btn-success">Save</button></td>
                                        </form>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>