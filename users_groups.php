<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
$user = filter_var( $_GET['user'] , FILTER_UNSAFE_RAW );
$part = explode( "@" , $user );
$domain = $part[1];


// Get currently selected user
$filter = "(mail=$user)";
$findMembers = ldap_search( $ds , LDAP_BASEDN , $filter );
$userDetail = ldap_get_entries( $ds , $findMembers );
unset( $userDetail['count'] );
$userDetail = $userDetail[0];
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
                        <h1>Edit Mailbox</h1>
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
                                <a class="nav-link active" aria-current="page" href="users_groups.php?user=<?php echo $user; ?>">Groups</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="users_alias.php?user=<?php echo $user; ?>">Addresses</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="users_services.php?user=<?php echo $user; ?>">Permissions</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="users_forwarding.php?user=<?php echo $user; ?>">Forwarding</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="users_bcc.php?user=<?php echo $user; ?>">BCC</a>
                            </li> 
                            <li class="nav-item">
                                <a class="nav-link" href="users_wblist.php?user=<?php echo $user; ?>">White/Black List</a>
                            </li>  
                        </ul>

                        <p>&nbsp;</p>
                        <table class="table table-bordered table-stripped">
                            <?php
                            unset( $userDetail['memberofgroup']['count'] );
                            foreach( $userDetail['memberofgroup'] as $group ) {
                                echo "<tr>";
                                echo "<td><a href='groups_members.php?group=$group'>" . $group . "</a></td>";
                                echo "</tr>";
                            }
                            ?>
                        </table>
                        
                        
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>