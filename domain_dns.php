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
$entity = "@" . $domain[0]['domainname'][0];

$filter = "(cn=catch-all)";
$search = ldap_search( $ds , "ou=Users," . $dn , $filter );
$entry = ldap_get_entries( $ds , $search );
$catchCount = (int)$entry['count'];
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
                                <a class="nav-link" href="domain_backupmx.php?domain=<?php echo $domainToFind; ?>">Backup MX</a>
                            </li>   
                            <li class="nav-item">
                                <a class="nav-link" href="domain_wblist.php?domain=<?php echo $domainToFind; ?>">White/Black List</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="domain_greylisting.php?domain=<?php echo $domainToFind; ?>">Greylisting</a>
                            </li> 
                            <li class="nav-item">
                                <a class="nav-link active" href="domain_dns.php?domain=<?php echo $domainToFind; ?>">DNS</a>
                            </li> 
                        </ul>
                        <div class="alert alert-info">
                            The following DNS entries are auto generated based on your server configuration. Please check them before applying them!
                        </div>
                        <table class="table table-striped">
                            <?php
                            $root = $domain['domainname'][0];
                            ?>
                            <thead>
                                <tr>
                                    <th>Zone</th>
                                    <th>TTL</th>
                                    <th>Type</th>
                                    <th>Priority</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo $root; ?>.</td>
                                    <td>60</td>
                                    <td>MX</td>
                                    <td>10</td>
                                    <td><?php echo SERVERHOSTNAME; ?>.</td>
                                </tr>
                                <tr>
                                    <td>mail.<?php echo $root; ?>.</th>
                                    <td>60</td>
                                    <td>A</td>
                                    <td></td>
                                    <td><?php echo file_get_contents("http://ipecho.net/plain"); ?></td>
                                </tr>
                                <tr>
                                    <td>autodiscover.<?php echo $root; ?>.</td>
                                    <td>60</td>
                                    <td>CNAME</th>
                                    <td></td>
                                    <td>mail.<?php echo $root; ?>.</td>
                                </tr>
                                <tr>
                                    <td>autoconfig.<?php echo $root; ?>.</td>
                                    <td>60</td>
                                    <td>CNAME</th>
                                    <td></td>
                                    <td>mail.<?php echo $root; ?>.</td>
                                </tr>
                                <tr>
                                    <td><?php echo $root; ?>.</td>
                                    <td>60</td>
                                    <td>TXT</td>
                                    <td></td>
                                    <td>v=spf1 mx a ~all</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <p><strong>Service records:</strong></p>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Zone</th>
                                    <th>TTL</th>
                                    <th>Type</th>
                                    <th>Priority</th>
                                    <th>Weight</th>
                                    <th>Port</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>_imap._tcp.<?php echo $root; ?>.</td>
                                    <td>60</td>
                                    <td>SRV</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>143</td>
                                    <td><?php echo SERVERHOSTNAME; ?>.</td>
                                </tr>
                                <tr>
                                    <td>_imaps._tcp.<?php echo $root; ?>.</td>
                                    <td>60</td>
                                    <td>SRV</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>993</td>
                                    <td><?php echo SERVERHOSTNAME; ?>.</td>
                                </tr>
                                <tr>
                                    <td>_smtp._tcp.<?php echo $root; ?>.</td>
                                    <td>60</td>
                                    <td>SRV</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>587</td>
                                    <td><?php echo SERVERHOSTNAME; ?>.</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <p><strong>DKIM:</strong></p>
                        <table class="table">
                            <tr>
                                <td>
                                    <?php
                                    if( file_exists( "usr/dkim_$root.txt" ) ) {
                                        unlink( "usr/dkim_$root.txt" );
                                    }
                                    shell_exec( "amavisd showkeys $root > usr/dkim_$root.txt" );
                                    $file = file( "usr/dkim_$root.txt" );
                                    unset( $file[0] );
                                    foreach( $file as $line ) {
                                        echo $line . "<br />";
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                        
                        
                        <?php plugins_process( "domain_dns" , "form" ); ?>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>