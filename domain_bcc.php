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


if( isset( $_POST['submit'] ) ) {
    // Sender BCC
    $found = FALSE;
    foreach( $domain[0]['enabledservice'] as $service ) {       
        if( $service == "senderbcc" ) {
            $found = TRUE;
        }
    }
    if( isset( $_POST['senderbcc'] ) ) {
        // Search to see if this is already enabled
        if( ! $found ) {
            ldap_mod_add( $ds , $dn , array( "enabledservice" => "senderbcc" ) );
        }
    } else {
        if( $found ) {
            ldap_mod_del( $ds , $dn , array( "enabledservice" => "senderbcc" ) );
        }
    }
    // Recipient BCC
    $found = FALSE;
    foreach( $domain[0]['enabledservice'] as $service ) {       
        if( $service == "recipientbcc" ) {
            $found = TRUE;
        }
    }
    if( isset( $_POST['recipientbcc'] ) ) {
        // Search to see if this is already enabled
        if( ! $found ) {
            ldap_mod_add( $ds , $dn , array( "enabledservice" => "recipientbcc" ) );
        }
    } else {
        if( $found ) {
            ldap_mod_del( $ds , $dn , array( "enabledservice" => "recipientbcc" ) );
        }
    }

    // domainRecipientBccAddress
    $domainRecipientBccAddress = filter_var( $_POST['domainRecipientBccAddress'] , FILTER_SANITIZE_STRING );
    $info['domainRecipientBccAddress'][0] = $domainRecipientBccAddress;
    // domainSenderBccAddress
    $domainSenderBccAddress = filter_var( $_POST['domainSenderBccAddress'] , FILTER_SANITIZE_STRING );
    $info['domainSenderBccAddress'][0] = $domainSenderBccAddress;

    ldap_modify( $ds , $dn , $info );

    // Get new entries
    $result = ldap_search( $ds , LDAP_BASEDN , $filter );
    $domain = ldap_get_entries( $ds , $result );
    plugins_process( "domain_bcc" , "submit" );
    //watchdog( "Editing domain `" . $domain['domainName'][0] . "`" );      FIX THIS LATER
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
                                <a class="nav-link" href="domain_alias.php?domain=<?php echo $domainToFind; ?>">Aliases</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="domain_bcc.php?domain=<?php echo $domainToFind; ?>">BCC</a>
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
                        </ul>
                        <p>&nbsp;</p>

                        <div class="form-check">
                            <?php
                            $checked = "";
                            foreach( $domain['enabledservice'] as $service ) {
                                if( $service == "senderbcc" ) {
                                    $checked = "checked";
                                }
                            }
                            ?>
                            <input class="form-check-input" type="checkbox" value="" id="senderbcc" name="senderbcc" <?php echo $checked; ?>>
                            <label class="form-check-label" for="senderbcc">
                                Enable outgoing BCC
                            </label>
                        </div>
                        <div class="form-check">
                            <?php
                            $checked = "";
                            foreach( $domain['enabledservice'] as $service ) {
                                if( $service == "recipientbcc" ) {
                                    $checked = "checked";
                                }
                            }
                            ?>
                            <input class="form-check-input" type="checkbox" value="" id="recipientbcc" name="recipientbcc" <?php echo $checked; ?>>
                            <label class="form-check-label" for="recipientbcc">
                                Enable incoming BCC
                            </label>
                        </div>
                        
                        <div class="input-group">
                            <?php
                            if( isset( $domain['domainrecipientbccaddress'][0] ) ) {
                                $domainRecipientBccAddress = $domain['domainrecipientbccaddress'][0];
                            } else {
                                $domainRecipientBccAddress = "";
                            }
                            ?>
                            <div class="input-group-prepend"><span class="input-group-text">Incoming BCC address:</span></div>
                            <input class="form-control" name="domainRecipientBccAddress" value="<?php echo $domainRecipientBccAddress; ?>">
                        </div>
                        <div class="input-group">
                            <?php
                            if( isset( $domain['domainsenderbccaddress'][0] ) ) {
                                $domainSenderBccAddress = $domain['domainsenderbccaddress'][0];
                            } else {
                                $domainSenderBccAddress = "";
                            }
                            ?>
                            <div class="input-group-prepend"><span class="input-group-text">Outgoing BCC address:</span></div>
                            <input class="form-control" name="domainSenderBccAddress" value="<?php echo $domainSenderBccAddress; ?>">
                        </div>
                        
                        <?php plugins_process( "domain_bcc" , "form" ); ?>

                        <p>&nbsp;</p>
                        <p><button type="submit" name="submit" class="btn btn-success"><i class='fas fa-save'></i>&nbsp;Save</button></p>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>