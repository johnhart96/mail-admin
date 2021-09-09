<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
$domainToFind = filter_var( $_GET['domain'] , FILTER_SANITIZE_STRING );
$filter = "(domainName=$domainToFind)";
$result = ldap_search( $ds , LDAP_BASEDN , $filter );
$domain = ldap_get_entries( $ds , $result );
$dn = $domain[0]['dn'];
$count = (int)$domain['count'];
unset( $domain['count'] );


if( isset( $_POST['submit'] ) ) {
    $aliases = filter_var( $_POST['aliases'] , FILTER_SANITIZE_STRING );
    if( $aliases !== "," ) {
        $alias = explode( "," , $aliases );
        $count = 0;
        foreach( $alias as $add ) {
            $info['domainaliasname'][$count] = $add;
            $count ++;
        }
        ldap_modify( $ds , $dn , $info );
    }

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
    watchdog( "Editing domain `" . $domain['domainName'][0] . "`" );
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
                        <h1>Domain: <?php echo $domain['cn'][0]; ?></h1>
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
                        </ul>
                        <p>&nbsp;</p>

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
                        <?php
                        $aliases = "";
                        unset( $domain['domainaliasname']['count'] );
                        if( isset( $domain['domainaliasname'] ) ) {
                            foreach( $domain['domainaliasname'] as $alias ) {
                                $aliases .= $alias . ",";
                            }
                        }
                        ?>
                        <p>&nbsp;</p>
                        <div class="mb-3">
                            <label for="aliases" class="form-label">Alias domains (comma seperated):</label>
                            <textarea class="form-control" id="aliases" rows="3" name="aliases"><?php echo $aliases; ?></textarea>
                        </div>
                        
                        <?php plugins_process( "domain_alias" , "form" ); ?>

                        <p>&nbsp;</p>
                        <p><button type="submit" name="submit" class="btn btn-success"><i class='fas fa-save'></i>&nbsp;Save</button></p>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>