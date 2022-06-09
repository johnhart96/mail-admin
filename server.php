<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
globalOnly();
require 'inc/bind.php';

if( isset( $_POST['submit_throttle'] ) ) {
    $account = filter_var( $_POST['account'] , FILTER_SANITIZE_STRING );
    $kind = filter_var( $_POST['kind'] , FILTER_SANITIZE_STRING );
    $priority = filter_var( $_POST['priority'] , FILTER_SANITIZE_NUMBER_INT );
    $period = filter_var( $_POST['period'] , FILTER_SANITIZE_NUMBER_INT );
    $msg_size = filter_var( $_POST['msg_size'] , FILTER_SANITIZE_NUMBER_INT );
    $max_msgs = filter_var( $_POST['max_msgs'] , FILTER_SANITIZE_NUMBER_INT );
    $max_quota = filter_var( $_POST['max_quota'] , FILTER_SANITIZE_NUMBER_INT );
    $max_rcpts = filter_var( $_POST['max_rcpts'] , FILTER_SANITIZE_NUMBER_INT );

    $insert = $apd->prepare("
        INSERT INTO throttle (account, kind, priority, period, msg_size, max_msgs, max_quota)
        VALUES(:account,:kind,:priority,:period,:msg_size,:max_msgs,:max_quota)
    ");
    $insert->execute(
        [
            ':account' => $account,
            ':kind' => $kind,
            ':priority' => $priority,
            ':period' => $period,
            ':msg_size' => $msg_size,
            ':max_msgs' => $max_msgs,
            ':max_quota' => $max_quota
        ]
    );
    $added = true;
}
if( isset( $_POST['rdns_submit'] ) ) {
    $domain = filter_var( $_POST['domain'] , FILTER_SANITIZE_STRING );
    $wb = filter_var( $_POST['wb'] , FILTER_SANITIZE_STRING );
    $insert = $apd->prepare( "INSERT INTO `wblist_rdns` (`rdns`,`wb`) VALUES(:rdns,:wb)" );
    $insert->execute( [ ':rdns' => $domain , ':wb' => $wb ] );
    $added = true;
}
if( isset( $_POST['submit_newWB'] ) ) {
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
    // Insert policy
    $getGlobal = $amavisd->query( "SELECT * FROM `users` WHERE `email` ='@.' LIMIT 1" );
    $result = $getGlobal->fetch( PDO::FETCH_ASSOC );
    $rid = $result['id'];
    $insert = $amavisd->prepare( "INSERT INTO `wblist` (`sid`,`rid`,`wb`) VALUES(:sid1,:rid,:wb)" );
    $insert->execute( [ ':sid1' => $sid , ':rid' => $rid , ':wb' => $wb ] );

}
// Greylisting
if( isset( $_POST['submit_greylisting'] ) ) {
    $delete = $apd->query( "DELETE FROM `greylisting` WHERE `account` ='@.'" );
    if( isset( $_POST['greylisting'] ) ) {
        $enable = $apd->query( "INSERT INTO `greylisting`(`id`,`account`,`priority`,`sender`,`active`) VALUES(1,'@.',0,'@.',1)" );
    } else {
        $disable = $apd->query( "INSERT INTO `greylisting`(`id`,`account`,`priority`,`sender`,`active`) VALUES(1,'@.',0,'@.',0)" );
    }
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
                    <h1>Server</h1>
                    <?php
                    if( isset( $added ) ) {
                        echo "<div class='alert alert-success'>Rule Added!</div>";
                    }
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <form method="post">
                        <div class="card">
                            <div class="card-header"><strong>Throttling rules:</strong></div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Scope</th>
                                            <th>Kind</th>
                                            <th>Priority</th>
                                            <th>Period (seconds)</th>
                                            <th>Max single message size (bytes)</th>
                                            <th>Max messages per period</th>
                                            <th>Max total message size per period (bytes)</th>
                                            <th>Max recipients</th>
                                            <th>&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $getRules = $apd->query( "SELECT * FROM `throttle`" );
                                        while( $rule = $getRules->fetch( PDO::FETCH_ASSOC ) ) {
                                            echo "<tr>";
                                            echo "<td>" . $rule['account'] . "</td>";
                                            echo "<td>" . ucfirst( $rule['kind'] ) . "</td>";
                                            echo "<td>" . $rule['priority'] . "</td>";
                                            echo "<td>" . $rule['period'] . "</td>";
                                            echo "<td>" . $rule['msg_size'] . "</td>";
                                            echo "<td>" . $rule['max_msgs'] . "</td>";
                                            echo "<td>" . $rule['max_quota'] . "</td>";
                                            echo "<td>" . $rule['max_rcpts'] . "</td>";
                                            echo "<td><a class='btn btn-danger' href='throttle_delete.php?id=" . $rule['id'] . "'>Delete</a></td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                            <th><input style="width: 100%;" type="text" name="account"></th>
                                            <th>
                                                <select style="width: 100%;" name="kind">
                                                    <option value="outbound">Outbound</option>
                                                    <option value="inbound">Inbound</option>
                                                    <option value="external">External</option>
                                                </select>
                                            </th>
                                            <th><input style="width: 100%;" type="text" name="priority" value="10"></th>
                                            <th><input style="width: 100%;" type="text" name="period" value="10"></th>
                                            <th><input style="width: 100%;" type="text" name="msg_size"></th>
                                            <th><input style="width: 100%;" type="text" name="max_msgs"></th>
                                            <th><input style="width: 100%;" type="text" name="max_quota"></th>
                                            <th><input style="width: 100%;" type="text" name="max_rcpts" value="-1"></th>
                                            <th><button style="width: 100%;" name="submit_throttle" type="submit" class="btn btn-success">Save</button></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="card-footer">
                                <table width="100%">
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="1" align="right"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">&nbsp;</div>

            <div class="row">
                <div class="col">
                    <form method="post">
                        <div class="card">
                            <div class="card-header"><strong>Reverse DNS White/Black List:</strong></div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Domain</th>
                                            <th>Type</th>
                                            <th width="1">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $getRDNS = $apd->query( "SELECT * FROM `wblist_rdns`" );
                                        while( $rule = $getRDNS->fetch( PDO::FETCH_ASSOC ) ) {
                                            echo "<tr>";
                                            echo "<td>" . $rule['rdns'] . "</td>";
                                            echo "<td>";
                                            switch( $rule['wb'] ) {
                                                case "W":
                                                    echo "Whitelist";
                                                    break;
                                                case "B":
                                                    echo "Blacklist";
                                                    break;
                                            }
                                            echo "</td>";
                                            echo "<td><a href='rdns_delete.php?id=" . $rule['id'] . "' class='btn btn-danger'>Delete</a></td>"; 
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th><input style="width: 100%" name="domain"></th>
                                            <th>
                                                <select name="wb" style="width: 100%">
                                                    <option value="B">Blacklist</option>
                                                    <option value="W">Whitelist</option>
                                                </select>
                                            </th>
                                            <th>
                                                <button style="width: 100%" class="btn btn-success" name="rdns_submit">Save</button>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">&nbsp;</div>

            <div class="row">
                <div class="col">
                    <div class="card">
                        <div class="card-header"><strong>Server Level Filter Policies:</strong></div>
                        <div card="card-body">
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
                                    $getGlobal = $amavisd->query( "SELECT * FROM `users` WHERE `email` ='@.' LIMIT 1" );
                                    $result = $getGlobal->fetch( PDO::FETCH_ASSOC );
                                    $user = $result['id'];
                                    $getWB = $amavisd->prepare( "SELECT * FROM `wblist` WHERE `rid` =:user" );
                                    $getWB->execute( [ ':user' => $user ] );
                                    $getSID = $amavisd->prepare( "SELECT * FROM `mailaddr` WHERE `id` =:addr LIMIT 1" );
                                    while( $row = $getWB->fetch( PDO::FETCH_ASSOC ) ) {
                                        echo "<tr>";
                                        $address = $row['sid'];
                                        $getSID->execute( [ ':addr' => $address ] );
                                        $sid = $getSID->fetch( PDO::FETCH_ASSOC );
                                        echo "<td>" . $sid['email'] . "</td>";
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
                                        echo "<td width='1'><a class='btn btn-danger' href='wblist_delete.php?rid=" . $row['rid'] . "&sid=" . $row['sid'] . "'>Delete</a></td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <form method="post">
                                        <tr>
                                            <td><input style="width: 100%" name="address"></td>
                                            <td>
                                                <select name="wb" style="width: 100%">
                                                    <option value="B">Block</option>
                                                    <option value="W">Allow</option>
                                                </select>
                                            </td>
                                            <td width="1"><button style="width: 100%" name="submit_newWB" class="btn btn-success">Save</button></td>
                                        </tr>
                                    </form>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">&nbsp;</div>

            <div class="row">
                <div class="col">
                    <div class="card">
                        <div class="card-header"><strong>Greylisting:</strong></div>
                        <div class="card-body">
                            <form method="post">
                                <?php
                                // Check global policy
                                $check = $apd->query( "SELECT * FROM `greylisting` WHERE `account` ='@.' LIMIT 1" );
                                $result = $check->fetch( PDO::FETCH_ASSOC );
                                $checked = "";
                                if( isset( $result['active'] ) ) {
                                    if( $result['active'] == 1 ) {
                                        $checked = "checked";
                                    }
                                }
                                ?>
                                <input type="checkbox" name="greylisting" <?php echo $checked; ?>>
                                <label for="greylisting">Enable</label> <br />
                                <button class="btn btn-success" type="submit" name="submit_greylisting">Save</button> &nbsp;
                                <a href="server_greylisting.php" class="btn btn-primary">Settings</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </body>
</html>