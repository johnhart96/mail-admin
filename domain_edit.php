<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
if( $_SESSION['admin_level'] !== "global" && $_SESSION['admin_level'] !== "self" ) {
    $dn = $_SESSION['admin_level'];
    $getDomain = ldap_search( $ds , $dn , "(domainName=*)" );
    $domain = ldap_get_entries( $ds , $getDomain );
    $domain = $domain[0];
    $domainToFind = $domain['domainname'][0];
    $title = "Organisation:";
} else {
    $domainToFind = filter_var( $_GET['domain'] , FILTER_SANITIZE_STRING );
    $title = "Domain:";
}
//$domainToFind = $domain[0]['domainname'][0];
$filter = "(domainName=$domainToFind)";
$result = ldap_search( $ds , LDAP_BASEDN , $filter );
$domain = ldap_get_entries( $ds , $result );
$dn = $domain[0]['dn'];
$count = (int)$domain['count'];


if( isset( $_POST['submit'] ) ) {
    $cn = filter_var( $_POST['cn'] , FILTER_SANITIZE_STRING );
    $disclaimer = filter_var( $_POST['disclaimer'] , FILTER_SANITIZE_STRING );
    if( isset( $_POST['minPasswordLength'] ) ) {
        $minPasswordLength = filter_var( $_POST['minPasswordLength'] , FILTER_SANITIZE_NUMBER_INT );
    } else {
        $minPasswordLength = 8;
    }
    if( isset( $_POST['defaultQuota'] ) ) {
        $defaultQuota = filter_var( $_POST['defaultQuota'] , FILTER_SANITIZE_NUMBER_INT );
    } else {
        $defaultQuota = 1024;
    }
    if( isset( $_POST['accountstatus'] ) ) {
        $accountstatus = "active";
    } else {
        $accountstatus = "inactive";
    }
    if( ! empty( $disclaimer ) && isset( $_POST['disclaimer'] ) ) {
        $info['disclaimer'] = $disclaimer;
    } else {
        $info['disclaimer'] = " ";
    }
    $info['accountStatus'] = $accountstatus;
    $info['cn'] = $cn;
    $info['accountsetting'][0] = "minPasswordLength:" . $minPasswordLength;
    $info['accountsetting'][1] = "defaultQuota:" . $defaultQuota;
    if( ! empty( $_POST['description'] ) ) {
        $info['description'] = filter_var( $_POST['description'] , FILTER_SANITIZE_STRING );
    }
    if( ldap_modify( $ds , $dn , $info ) ) {
        $saved = true;
        $result = ldap_search( $ds , LDAP_BASEDN , $filter );
        $domain = ldap_get_entries( $ds , $result );
        plugins_process( "domain_edit" , "submit" );
        watchdog( "Editing domain `" . $domain . "`" );
    } else {
        die( "Error updating!" );
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

                        // NULLING UNSET VALUES
                        if( empty( $domain['domainRecipientBccAddress'][0] ) ) {
                            $domain['domainRecipientBccAddress'][0] = NULL;
                        }
                        if( empty( $domain['domainSenderBccAddress'][0] ) ) {
                            $domain['domainSenderBccAddress'][0] = NULL;
                        }
                        // Check for an empty CN
                        if( empty( $domain['cn'][0] ) ) {
                            $cn = NULL;
                        } else {
                            $cn = $domain['cn'][0];
                        }
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
                                <a class="nav-link active" aria-current="page" href="domain_edit.php?domain=<?php echo $domainToFind; ?>">General</a>
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
                        </ul>
                        <div class="btn-group">
                            <a data-bs-toggle="tooltip" data-bs-placement="bottom" title="Users" href="users.php?domain=<?php echo $domainToFind; ?>" class="btn btn-primary"><i class="fa fa-id-badge"></i></a>
                            <a data-bs-toggle="tooltip" data-bs-placement="bottom" title="Aliases" href="alias.php?domain=<?php echo $domainToFind; ?>" class="btn btn-primary"><i class="fa fa-mask"></i></a>
                            <a data-bs-toggle="tooltip" data-bs-placement="bottom" title="Groups" href="groups.php?domain=<?php echo $domainToFind; ?>" class="btn btn-primary"><i class="fa fa-group"></i></a>
                        </div>
                        <p>&nbsp;</p>

                        <div class="form-check">
                            <?php
                            $accountStatus = $domain['accountstatus'][0];
                            if( $accountStatus == "active" ) {
                                $checked = "checked";
                            } else {
                                $checked = NULL;
                            }
                            ?>
                            <input class="form-check-input" type="checkbox" value="" id="accountstatus" name="accountstatus" <?php echo $checked; ?>>
                            <label class="form-check-label" for="accountstatus">
                                Enable this domain
                            </label>
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Company/Organization Name</span></div>
                            <input name="cn" class="form-control" value="<?php echo $cn; ?>">
                        </div>
                        <div class="input-group">
                            <?php
                            if( ! empty( $domain['description'] ) ) {
                                $description = $domain['description'][0];
                            } else {
                                $description = NULL;
                            }
                            ?>
                            <div class="input-group-prepend"><span class="input-group-text">Description</span></div>
                            <input name="description" class="form-control" value="<?php echo $description; ?>">
                        </div>
                        
                        <p>&nbsp;</p>
                        <div class="mb-3">
                            <label for="disclaimer" class="form-label">Disclaimer:</label>
                            <textarea class="form-control" id="disclaimer" rows="3" name="disclaimer"><?php if( ! empty( $domain['disclaimer'][0] ) ) { echo $domain['disclaimer'][0]; } ?></textarea>
                        </div>
                        
                        <?php
                        unset( $domain['accountsetting']['count'] );
                        if( isset( $domain['accountsetting'] ) ) {
                            foreach( $domain['accountsetting'] as $setting ) {
                                $part = explode( ":" , $setting );
                                switch( $part[0] ) {
                                    case "minPasswordLength":
                                        $label = "Minimum password lenght";
                                        break;
                                    case "defaultQuota":
                                        $label = "Default Quota (MB)";
                                        break;
                                    default:
                                        $label = $part[0];
                                        break;
                                }
                                echo "<div class='input-group'>";
                                echo "<div class='input-group-prepend'><span class='input-group-text'>" . $label . ":</span></div>";
                                echo "<input name='" . $part[0] . "' class='form-control' value='" . $part[1] . "'>";
                                echo "</div>";
                            }
                        }
                        ?>
                        <?php plugins_process( "domain_edit" , "form" ); ?>
                        <p>&nbsp;</p>
                        <p><button type="submit" name="submit" class="btn btn-success"><i class='fas fa-save'></i>&nbsp;Save</button></p>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>