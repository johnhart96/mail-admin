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
                    $getOutgoing = $apd->query( "SELECT * FROM `throttle` WHERE `account` ='@.' AND `kind` ='outgoing' LIMIT 1" );
                    $getIncoming = $apd->query( "SELECT * FROM `throttle` WHERE `account` ='@.' AND `kind` ='incoming' LIMIT 1" );
                    $outgoing = $getOutgoing->fetch( PDO::FETCH_ASSOC );
                    $incoming = $getIncoming->fetch( PDO::FETCH_ASSOC );
                    ?>
                    <form method="post">
                        <div class="card">
                            <div class="card-header"><strong>Throttling rules</strong></div>
                            <div class="card-body">
                                <?php
                                if( isset( $added ) ) {
                                    echo "<div class='alert alert-success'>Rule Added!</div>";
                                }
                                ?>
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
        </div>
    </body>
</html>