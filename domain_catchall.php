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
    $domainToFind = filter_var( $_GET['domain'] , FILTER_SANITIZE_STRING );
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

$filter = "(cn=catch-all)";
$search = ldap_search( $ds , "ou=Users," . $dn , $filter );
$entry = ldap_get_entries( $ds , $search );
$catchCount = (int)$entry['count'];
if( isset( $_POST['submit'] ) ) {
    if( ! isset( $_POST['enabled'] ) ) {
        // Delete the entry if it exists
        if( (int)$entry['count'] !== 0 ) {
            ldap_delete( $ds , $entry[0]['dn'] );
        }
        header( "Location:domain_catchall.php?saved&domain=" . $domainToFind );
    } else {
        // Add
        if( (int)$entry['count'] !== 0 ) {
            ldap_delete( $ds , $entry[0]['dn'] );
        }
        $forwards = filter_var( $_POST['addresses'] , FILTER_SANITIZE_STRING );
        $addresses = explode( "," , $forwards );
        $count = 0;
        foreach( $addresses as $address ) {
            $dets['mailforwardingaddress'][$count] = $address;
            $count ++;
        }
        $dets['objectclass'][0] = "inetOrgPerson";
        $dets['mail'][0] = "@" . $domainToFind;
        $dets['objectclass'][1] = "mailUser";
        $dets['cn'] = "catch-all";
        $dets['sn'] = "catch-all";
        $dets['accountstatus'] = "active";
        $dets['uid'] = "catch-all";

        $fullDNToAdd = "mail=catch-all,ou=Users," . $dn;
        ldap_add( $ds , $fullDNToAdd , $dets );
        plugins_process( "domain_catchall" , "submit" );
        watchdog( "Editing domain `" . $domainToFind . "`" );
        header( "Location:domain_catchall.php?saved&domain=" . $domainToFind );
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
                                <a class="nav-link active" href="domain_catchall.php?domain=<?php echo $domainToFind; ?>">Catch-all</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="domain_backupmx.php?domain=<?php echo $domainToFind; ?>">Backup MX</a>
                            </li>   
                            <li class="nav-item">
                                <a class="nav-link" href="domain_wblist.php?domain=<?php echo $domainToFind; ?>">White/Black List</a>
                            </li> 
                            <li class="nav-item">
                                <a class="nav-link" href="domain_greylisting.php?domain=<?php echo $domainToFind; ?>">Greylisting</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="domain_dns.php?domain=<?php echo $domainToFind; ?>">DNS</a>
                            </li> 
                        </ul>
                        <p>&nbsp;</p>
                        <div class="alert alert-info">
                            Any email address for this domain that is not known, will be redirected to the catch all address.
                        </div>

                        <div class="form-check">
                            <?php
                            $checked = "";
                            if( $catchCount == 1 ) {
                                $checked = "checked";
                            }
                            ?>
                            <input class="form-check-input" type="checkbox" value="" id="enabled" name="enabled" <?php echo $checked; ?>>
                            <label class="form-check-label" for="enabled">
                                Enable catch-all
                            </label>
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Catch-all Addresses:</span></div>
                            <?php
                            $csv = "";
                            if( isset( $entry[0]['mailforwardingaddress'] ) ) {
                                unset( $entry[0]['mailforwardingaddress']['count'] );
                                foreach( $entry[0]['mailforwardingaddress'] as $forward ) {
                                    $csv .= $forward . ",";
                                }
                            }
                            ?>
                            <input type="text" class="form-control" name="addresses" value="<?php echo $csv; ?>">
                        </div>
                        <?php plugins_process( "domain_catchall" , "form" ); ?>

                        <p>&nbsp;</p>
                        <p><button type="submit" name="submit" class="btn btn-success"><i class='fas fa-save'></i>&nbsp;Save</button></p>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>