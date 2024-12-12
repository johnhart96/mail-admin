<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
if( isset( $_POST['submit'] ) ) {
    $domain = filter_var( $_POST['domain'] , FILTER_SANITIZE_STRING );
    $address = filter_var( $_POST['address'] , FILTER_SANITIZE_STRING ) . "@" . $domain;
    $cn = filter_var( $_POST['address'] , FILTER_SANITIZE_STRING );
    $dnToAdd = "mail=" . $address . ",ou=Groups,domainName=" . $domain . "," . LDAP_DOMAINDN;
    $filter = "(mail=" . $address . ")";
    $searchForExisting = ldap_search( $ds , LDAP_BASEDN , $filter );
    $result = ldap_get_entries( $ds , $searchForExisting );
    if( (int)$result['count'] == 0 ) {
        $info['accountstatus'] = "active";
        $info['enabledservice'][0] = "mail";
        $info['enabledservice'][1] = "deliver";
        $info['enabledservice'][1] = "displayedInGlobalAddressBook";
        $info['objectclass'][0] = "mailList";
        $info['objectclass'][1] = "top";
        $info['mail'] = $address;
        $info['cn'] = $cn;

        if( ldap_add( $ds , $dnToAdd , $info ) ) {
            plugins_process( "groups_new" , "submit" );
            watchdog( "Adding group `" . $address . "@" . $address . "`" );
            header( "Location: groups.php?saved" );
        } else {
            die( "Cannot add!" );
        }

    } else {
        $alreadyExists = true;
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
                        <h1>Groups</h1>
                        <?php
                        if( isset( $alreadyExists ) ) {
                            echo "<div class='alert alert-danger'><strong>ERROR:</strong> This email already exists!</div>";
                        }
                        ?>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="groups.php">Groups</a></li>
                                <li class="breadcrumb-item active" aria-current="page">New</li>
                            </ol>
                        </nav>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Address:</span></div>
                            <input required type="text" name="address" class="form-control">
                            <span class="input-group-text">@</span>
                            <select required name="domain" class="form-control">
                                <?php
                                if( $_SESSION['admin_level'] !== "global" ) {
                                    require 'inc/relmset.php';
                                    $domain = str_replace( LDAP_DOMAINDN , "" , $relm );
                                    $domain = str_replace( "domainName=" , "" , $domain );
                                    $domain = str_replace( "," , "" , $domain );
                                    $filter = "(domainName=$domain)";
                                } else {
                                    $filter = "(domainName=*)";
                                }
                                $getDomains = ldap_search( $ds , LDAP_DOMAINDN , $filter );
                                $entries = ldap_get_entries( $ds , $getDomains );
                                unset( $entries['count'] );
                                foreach( $entries as $domain ) {
                                    echo "<option>" . $domain['domainname'][0] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <?php plugins_process( "groups_new" , "form" ); ?>
                        <p>&nbsp;</p>
                        <p><button type="submit" name="submit" class="btn btn-success"><i class="fas fa-save"></i>&nbsp;Save</button></p>
                        
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>