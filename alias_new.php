<?php
require 'inc/functions.php';
require 'inc/common_header.php';
securePage();
require 'inc/bind.php';
?>
<html>
    <head>
        <?php
        require 'inc/header.php';
        if( isset( $_POST['submit'] ) ) {
            $domain = filter_var( $_POST['domain'] , FILTER_SANITIZE_STRING );
            $address = filter_var( $_POST['address'] , FILTER_SANITIZE_STRING ) . "@" . $domain;
            $destinations = explode( "," , filter_var( $_POST['destinations'] , FILTER_SANITIZE_STRING ) );

            $filter = "(mail=" . $address . ")";
            $searchForExisting = ldap_search( $ds , LDAP_BASEDN , $filter );
            $result = ldap_get_entries( $ds , $searchForExisting );
            if( (int)$result['count'] == 0 ) {
                $info['accountstatus'] = "active";
                $info['enabledservice'][0] = "mail";
                $info['enabledservice'][1] = "deliver";
                $info['objectclass'][0] = "mailAlias";
                $info['objectclass'][1] = "top";
                $count = 0;
                foreach( $destinations as $forward ) {
                    $info['mailforwardingaddress'][$count] = $forward;
                    $count ++;
                }
                $dn = "mail=" . $address . ",ou=Aliases,domainName=" . $domain . "," . LDAP_DOMAINDN;
                if( ldap_add( $ds , $dn , $info ) ) {
                    plugins_process( "alias_new" , "submit" );
                    go( "alias.php?saved" );
                } else {
                    die( "Cannot add" );
                }
            } else {
                $alreadyExists = true;
            }
        }
        ?>
    </head>
    <body>
        <?php require 'inc/topbar.php'; ?>
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1>New Aliases</h1>
                    <?php
                    if( isset( $alreadyExists ) ) {
                        echo "<div class='alert alert-danger'><strong>ERROR:</strong> This email already exists!</div>";
                    }
                    ?>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="alias.php">Alias</a></li>
                            <li class="breadcrumb-item active" aria-current="page">New</li>
                        </ol>
                    </nav>
                    <form method="post">
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Address:</span></div>
                            <input required type="text" name="address" class="form-control">
                            <span class="input-group-text">@</span>
                            <select required name="domain" class="form-control">
                                <option selected disabled>--Select--</option>
                                <?php
                                $filter = "(domainName=*)";
                                $getDomains = ldap_search( $ds , LDAP_DOMAINDN , $filter );
                                $entries = ldap_get_entries( $ds , $getDomains );
                                unset( $entries['count'] );
                                foreach( $entries as $domain ) {
                                    echo "<option>" . $domain['domainname'][0] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <p>&nbsp;</p>
                        <div class="mb-3">
                            <?php plugins_process( "alias_new" , "form" ); ?>
                            <label for="destinations" class="form-label">Destinations (comma seperated):</label>
                            <textarea class="form-control" id="destinations" rows="3" name="destinations"></textarea>
                        </div>
                        <p><button type="submit" name="submit" class="btn btn-success"><i class="fas fa-save"></i>&nbsp;Save</button></p>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>