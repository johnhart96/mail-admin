<?php if( is_global() ) { ?>
    <div class="col-3 border-end">
        <ul id="myTree">
            <li>
                <span class="caret"><?php echo SERVERHOSTNAME; ?></span>
                <ul class="nested">
                    <?php
                    require 'inc/bind.php';
                    $filter = "(domainName=*)";
                    $result = ldap_search( $ds , LDAP_BASEDN , $filter ) or exit("Unable to search");
                    $e = ldap_get_entries( $ds , $result );
                    unset( $e['count'] );
                    foreach( $e as $d ) { 
                        $dn = $d['dn'];
                        $dName = $d['domainname'][0];
                        echo "<li>";
                        echo "<span class='caret'><a href='domain_edit.php?domain=" . $dName . "'>" . $dName . "</a></span>";
                        echo "<ul class='nested'>";

                        // Mailboxes
                        echo "<li>";
                        echo "<span class='caret'>Users</span>";
                        echo "<ul class='nested'>";
                        $filter = "(uid=*)";
                        $getUsers = ldap_search( $ds , $dn , $filter );
                        $us = ldap_get_entries( $ds , $getUsers );
                        foreach( $us as $u ) {
                            if( ! empty( $u['mail'][0] ) && ! empty( $u['cn'][0] ) ) {
                                echo "<li>";
                                echo "<a href='users_edit.php?user=" . $u['mail'][0] . "'>" . $u['cn'][0] . "</a>";
                                echo "</li>";
                            }
                        }
                        unset( $u );

                        echo "</ul>";
                        echo "</li>";


                        // Groups
                        echo "<li>";
                        echo "<span class='caret'>Groups</span>";
                        echo "<ul class='nested'>";
                        $filter = "(objectclass=mailList)";
                        $getGroups = ldap_search( $ds , $dn , $filter );
                        $e = ldap_get_entries( $ds , $getGroups );
                        foreach( $e as $g ) {
                            if( ! empty( $g['mail'][0] ) ) {
                                echo "<li>";
                                echo "<a href='groups_edit.php?group=" . $g['mail'][0] . "'>" . $g['mail'][0] . "</a>";
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
                        $e = ldap_get_entries( $ds , $result );
                        foreach( $e as $a ) {
                            if( ! empty( $a['mail'][0] ) ) {
                                echo "<li>";
                                echo "<a href='alias_edit.php?alias=" . $a['mail'][0] . "'>" . $a['mail'][0] . "</a>";
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
<?php } ?>