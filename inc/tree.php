<div class="col-3 border-end">
    <ul id="myTree">
        <li>
            <span class="caret"><?php echo SERVERHOSTNAME; ?></span>
            <ul class="nested">
                <?php
                require 'inc/bind.php';
                $filter = "(domainName=*)";
                $result = ldap_search( $ds , LDAP_BASEDN , $filter ) or exit("Unable to search");
                $entries = ldap_get_entries( $ds , $result );
                $count = $entries['count'];
                unset( $entries['count'] );
                foreach( $entries as $domain ) { 
                    $dn = $domain['dn'];
                    $domainName = $domain['domainname'][0];
                    echo "<li>";
                    echo "<span class='caret'><a href='domain_edit.php?domain=" . $domainName . "'>" . $domainName . "</a></span>";
                    echo "<ul class='nested'>";

                    // Mailboxes
                    echo "<li>";
                    echo "<span class='caret'>Users</span>";
                    echo "<ul class='nested'>";
                    $filter = "(uid=*)";
                    $getUsers = ldap_search( $ds , $dn , $filter );
                    $users = ldap_get_entries( $ds , $getUsers );
                    foreach( $users as $user ) {
                        if( ! empty( $user['mail'][0] ) && ! empty( $user['cn'][0] ) ) {
                            echo "<li>";
                            echo "<a href='users_edit.php?user=" . $user['mail'][0] . "'>" . $user['cn'][0] . "</a>";
                            echo "</li>";
                        }
                    }

                    echo "</ul>";
                    echo "</li>";


                    // Groups
                    echo "<li>";
                    echo "<span class='caret'>Groups</span>";
                    echo "<ul class='nested'>";
                    $filter = "(objectclass=mailList)";
                    $getGroups = ldap_search( $ds , $dn , $filter );
                    $entries = ldap_get_entries( $ds , $getGroups );
                    foreach( $entries as $group ) {
                        if( ! empty( $group['mail'][0] ) ) {
                            echo "<li>";
                            echo "<a href='groups_edit.php?group=" . $group['mail'][0] . "'>" . $group['mail'][0] . "</a>";
                            echo "</li>";
                        }
                    }
                    echo "</ul>";
                    echo "</li>";

                    // Aliases
                    echo "<li>";
                    echo "<span class='caret'>Aliases</span>";
                    echo "<ul class='nested'>";
                    $filter = "(objectclass=mailalias)";
                    $result = ldap_search( $ds , $dn , $filter ) or exit("Unable to search");
                    $entries = ldap_get_entries( $ds , $result );
                    foreach( $entries as $alias ) {
                        if( ! empty( $alias['mail'][0] ) ) {
                            echo "<li>";
                            echo "<a href='alias_edit.php?alias=" . $alias['mail'][0] . "'>" . $alias['mail'][0] . "</a>";
                            echo "</li>";
                        }
                    }

                    echo "</ul>";
                    echo "</li>";

                    echo "</ul>";
                    echo "</li>";
                }
                ?>
            </ul>
        </li>
    </ul>
</div>