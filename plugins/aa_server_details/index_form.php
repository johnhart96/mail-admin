<?php if( $_SESSION['admin_level'] == "global" ) { ?>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header"><strong>Server Details:</strong></div>
                <div class="card-body">
                    <img align="left" style="height: 100px; margin-right: 10px;" class="img-thumbnail float-left" src="images/mail_server.png">
                    <p>
                        <strong>Server name:</strong> <?php echo SERVERHOSTNAME; ?> <br />
                    </p>
                    <p>
                        <strong>CPU:</strong>
                        <?php
                        $file = file('/proc/cpuinfo');
                        $proc_details = $file[4];
                        $proc_details = str_replace( "model name" , "" , $proc_details );
                        $proc_details = str_replace( ":" , "" , $proc_details );
                        echo $proc_details;
                        ?>
                    </p>
                    <p>
                        <strong>Memory:</strong>
                        <?php
                        $memory = getSystemMemInfo();
                        echo $memory['MemTotal'];
                        ?>
                    </p>
                    <p>
                        <strong>OS:</strong>
                        <?php echo php_uname()?>
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php } ?>