<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
if( isset( $_POST['submit'] ) ) {
    $cn = filter_var( $_POST['cn'] , FILTER_SANITIZE_STRING );
    $domain = filter_var( $_POST['domain'] , FILTER_SANITIZE_STRING );
    $disclaimer = filter_var( $_POST['disclaimer'] , FILTER_SANITIZE_STRING );
    if( ! empty( $disclaimer ) ) {
        $info['disclaimer'] = $disclaimer;
    }

    if( isset( $_POST['accountstatus'] ) ) {
        $accountstatus = "active";
    } else {
        $accountstatus = "inactive";
    }
    require 'inc/bind.php';

    $info['cn'] = $cn;
    $info['objectClass'] = "mailDomain";
    $info['domainCurrentUserNumber'] = 0;
    $info['domainCurrentQuotaSize'] = 0;
    $info['accountStatus'] = $accountstatus;
    $info['accountSetting'][0] = "minPasswordLength:8";
    $info['accountSetting'][1] = "defaultQuota:1024";
    $info['enabledService'][0] = "mail";
    $info['enabledService'][1] = "smtp";
    $info['enabledService'][2] = "smtps";
    $info['enabledService'][3] = "smtptsl";
    $info['mtaTransport'] = "dovecot";
    $info['domainName'] = $domain;
    $sr = ldap_search( $ds , "dc=JH96,dc=LOCAL" , "(domainName=$domain)" );
    $check = ldap_get_entries( $ds , $sr );
    if( (int)$check['count'] !==0 ) {
        $domainAlreadyExists = true;
    } else {
        // Create the object
        $r = ldap_add( $ds , "domainName=$domain," . LDAP_DOMAINDN , $info );

        // Create OUs
        $ou['objectclass'][0] = "organizationalUnit";
        $ou['objectclass'][1] = "top";
        $ou['ou'] = "Aliases";
        ldap_add( $ds , "ou=Aliases," . "domainName=$domain," . LDAP_DOMAINDN , $ou );
        $ou['ou'] = "Users";
        ldap_add( $ds , "ou=Users," . "domainName=$domain," . LDAP_DOMAINDN , $ou );
        $ou['ou'] = "Computers";
        ldap_add( $ds , "ou=Computers," . "domainName=$domain," . LDAP_DOMAINDN , $ou );
        $ou['ou'] = "Externals";
        ldap_add( $ds , "ou=Externals," . "domainName=$domain," . LDAP_DOMAINDN , $ou );
        $ou['ou'] = "Groups";
        ldap_add( $ds , "ou=Groups," . "domainName=$domain," . LDAP_DOMAINDN , $ou );
        plugins_process( "domain_new" , "submit" );

        // Check entry exists
        $sr = ldap_search( $ds , "dc=JH96,dc=LOCAL" , "(domainName=$domain)" );
        $info = ldap_get_entries( $ds , $sr );
        if( $info['count'] == 1 ) {
            // Object added
            header( "Location:domains.php?added" );
        }
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
                        <h1>New Domain</h1>
                        <?php
                        if( isset( $domainAlreadyExists ) ) {
                            echo "<div class='alert alert-danger'><strong>ERROR:</strong> This domain already exists!</div>";
                        }
                        if( isset( $domainAdded ) ) {
                            echo "<div class='alert alert-success'>This domain created!</div>";
                        }
                        ?>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="domains.php">Domains</a></li>
                                <li class="breadcrumb-item active" aria-current="page">New</li>
                            </ol>
                        </nav>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="accountstatus" name="accountstatus">
                            <label class="form-check-label" for="accountstatus">
                                Enable this domain
                            </label>
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Company/Organization Name:</span></div>
                            <input required name="cn" class="form-control">
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Domain Name:</span></div>
                            <input required name="domain" class="form-control">
                        </div>
                        
                        <p>&nbsp;</p>
                        <div class="mb-3">
                            <label for="disclaimer" class="form-label">Disclaimer:</label>
                            <textarea class="form-control" id="disclaimer" rows="3" name="disclaimer"></textarea>
                        </div>

                        <?php plugins_process( "domain_new" , "form" ); ?>
                        <p><button type="submit" name="submit" class="btn btn-success"><i class='fas fa-save'></i>&nbsp;Save</button></p>
                        
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>