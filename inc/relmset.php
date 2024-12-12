<?php
if( $_SESSION['admin_level'] == "global" ) {
    $relm = LDAP_DOMAINDN;
} else if( $_SESSION['admin_level'] == "self" ) {
    $relm = $_SESSION['dn'];
} else {
    $relm = $_SESSION['admin_level'];
}

?>