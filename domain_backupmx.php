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
    $domainToFind = filter_var( $_GET['domain'] , FILTER_UNSAFE_RAW );
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
    if( isset( $_POST['backupMX'] ) ) {
        $ip = filter_var( $_POST['ip'] , FILTER_VALIDATE_IP );
        $port = filter_var( $_POST['port'] , FILTER_SANITIZE_NUMBER_INT );
        $relay = "relay:[" . $ip . ":" . $port . "]";
        ldap_modify( $ds , $dn , array( "mtatransport" => $relay ) );
        if( isset( $domain[0]['domainbackupmx'][0] ) ) {
            ldap_modify( $ds , $dn , array( "domainbackupmx" => "yes" ) );
        } else {
            ldap_mod_add( $ds , $dn , array( "domainbackupmx" => "yes" ) );
        }
    } else {
        // Remove the entry and set transport to dovcot
        ldap_modify( $ds , $dn , array( "mtatransport" => "dovecot" ) );
        if( isset( $domain[0]['domainbackupmx'][0] ) ) {
            ldap_mod_del( $ds , $dn , array( "domainbackupmx" => "yes" ) );
        }
    }
    $filter = "(domainName=$domainToFind)";
    $result = ldap_search( $ds , LDAP_BASEDN , $filter );
    $domain = ldap_get_entries( $ds , $result );
    //watchdog( "Editing domain `" . $domain['domainName'][0] . "`" );          FIX THIS LATER
    plugins_process( "domain_backupmx" , "submit" );
    $saved = TRUE;
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
                                <a class="nav-link" href="domain_catchall.php?domain=<?php echo $domainToFind; ?>">Catch-all</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="domain_backupmx.php?domain=<?php echo $domainToFind; ?>">Backup MX</a>
                            </li>   
                            <li class="nav-item">
                                <a class="nav-link" href="domain_wblist.php?domain=<?php echo $domainToFind; ?>">White/Black List</a>
                            </li>   
                            <li class="nav-item">
                                <a class="nav-link" href="domain_greylisting.php?domain=<?php echo $domainToFind; ?>">Greylisting</a>
                            </li>
                        </ul>
                        <p>&nbsp;</p>
                        <div class="alert alert-info">If backup MX is enabled, this server will not store mail for this domain on this server, but will attempt to deliver mail to the server below.</div>
                        <?php
                        if( isset( $domain['domainbackupmx'][0] ) ) {
                            $checked = "checked";
                            $transport = str_replace( "relay:[" , "" , $domain['mtatransport'][0] );
                            $transport = str_replace( "]" , "" , $transport );
                            $bit = explode( ":" , $transport );
                            $ip = $bit[0];
                            $port = $bit[1];
                        } else {
                            $checked = "";
                            $port = NULL;
                            $ip = NULL;
                        }
                        if( isset( $saved ) ) {
                            echo "<div class='alert alert-success'>Changes saved!</div>";
                        }
                        ?>
                        <input class="form-check-input" type="checkbox" value="" id="backupMX" name="backupMX" <?php echo $checked; ?>>
                        <label class="form-check-label" for="backupMX">
                            Enable as backup mx
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Primary MX:</span></div>
                            <input class="form-control" name="ip" value="<?php echo $ip; ?>" placeholder="IP Address">
                            <div class="input-group-append">
                                <input class="form-control" name="port" value="<?php echo $port; ?>" placeholder="Port">
                            </div>
                        </div>
                        <?php plugins_process( "domain_backupmx" , "form" ); ?>
                        <p>&nbsp;</p>
                        <button type="submit" class="btn btn-success" name="submit"><i class="fas fa-save"></i>&nbsp;Save</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>