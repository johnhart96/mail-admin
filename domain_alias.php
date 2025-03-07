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

// Delete an alias
if( isset( $_GET['delete'] ) ) {
    $delete = filter_var( $_GET['delete'] , FILTER_UNSAFE_RAW );
    $filter = array( "domainaliasname" => $delete );
    ldap_mod_del( $ds , $dn , $filter );
    header( "Location: domain_alias.php?domain=$domainToFind" );
}
// Add new alias
if( isset( $_POST['submit'] ) ) {
    $add = filter_var( $_POST['domainToAdd'] , FILTER_SANITIZE_STRING );
    $info['domainaliasname'] = $add;
    ldap_mod_add( $ds , $dn , $info );
    $found = FALSE;
    foreach( $domain[0]['enabledservice'] as $service ) {       
        if( $service == "domainalias" ) {
            $found = TRUE;
        }
    }
    if( isset( $_POST['accountstatus'] ) ) {
        $serviceCount = 0;
        if( ! $found ) {
            ldap_mod_add( $ds , $dn , array( "enabledservice" => "domainalias" ) );
        }
    } else {
        if( $found ) {
            ldap_mod_del( $ds , $dn , array( "enabledservice" => "domainalias" ) );
        }
    }
    plugins_process( "domain_alias" , "submit" );
    $saved = TRUE;
    $result = ldap_search( $ds , LDAP_BASEDN , $filter );
    $domain = ldap_get_entries( $ds , $result );
    $dn = $domain[0]['dn'];
    $count = (int)$domain['count'];
    //watchdog( "Editing domain `" . $domain[0]['domainName'][0] . "`" );           FIX THIS LATER
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
                        <h1><?php echo $title; ?></h1>
                        <p><em><?php echo $domain['domainname'][0]; ?></em></p>
                        <?php
                        if( isset( $saved ) ) {
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
                                <a class="nav-link active" href="domain_alias.php?domain=<?php echo $domainToFind; ?>">Aliases</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="domain_bcc.php?domain=<?php echo $domainToFind; ?>">BCC</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="domain_catchall.php?domain=<?php echo $domainToFind; ?>">Catch-all</a>
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
                        <div class="alert alert-info">Domains listed below will redirect emails to the primary domain.</div>

                        <div class="form-check">
                            <?php
                            $checked = "";
                            foreach( $domain['enabledservice'] as $service ) {
                                if( $service == "domainalias" ) {
                                    $checked = "checked";
                                }
                            }
                            ?>
                            <input class="form-check-input" type="checkbox" value="" id="accountstatus" name="accountstatus" <?php echo $checked; ?>>
                            <label class="form-check-label" for="accountstatus">
                                Enable alias domains
                            </label>
                        </div>
                        <table class="table table-bordered table-stripped">
                            <?php
                            if( isset( $domain['domainaliasname'] ) ) {
                                unset( $domain['domainaliasname']['count'] );
                                foreach( $domain['domainaliasname'] as $alias ) {
                                    echo "<tr>";
                                    echo "<td>" . $alias . "</td>";
                                    echo "<td width='1'><a href='domain_alias.php?domain=$domainToFind&delete=$alias' class='btn btn-danger'><i class='fas fa-trash'></i></td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                            <tr>
                                <td><input type="text" name="domainToAdd" class="form-control"></td>
                                <td width="1"><button type="submit" name="submit" class="btn btn-success"><i class="fas fa-plus"></i></button></td>
                            </tr>
                        </table>
                        <p>&nbsp;</p>
                        
                        <?php plugins_process( "domain_alias" , "form" ); ?>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>