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
        $alias = filter_var( $_GET['alias'] , FILTER_SANITIZE_STRING );
        $filter = "(mail=$alias)";
        $part = explode( "@" , $alias );
        $domain = $part[1];
        $dn = "mail=" . $alias . ",ou=Aliases,domainName=" . $domain . "," . LDAP_DOMAINDN;
        $getCurrent = ldap_search( $ds , $dn , $filter );
        $current = ldap_get_entries( $ds , $getCurrent );
        unset( $current['count'] );
        if( isset( $_POST['submit'] ) ) {
            if( ! empty( $_POST['description'] ) ) {
                $description = filter_var( $_POST['description'] , FILTER_SANITIZE_STRING );
            }
            ldap_mod_del( $ds , $dn , array( "mailforwardingaddress" => array() ) );
            $addressess = explode( "," , filter_var( $_POST['destinations'] , FILTER_SANITIZE_STRING ) );
            foreach( $addressess as $address ) {
                if( ! empty( $address ) ) {
                    ldap_mod_add( $ds , $dn , array( "mailforwardingaddress" => $address ) );
                }
            }
            ldap_modify( $ds , $dn , array( "description" => $description ) );
            $saved = TRUE;
            $getCurrent = ldap_search( $ds , $dn , $filter );
            $current = ldap_get_entries( $ds , $getCurrent );
            plugins_process( "alias_edit" , "submit" );
        }
        ?>
    </head>
    <body>
        <?php require 'inc/topbar.php'; ?>
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1>Edit Aliases</h1>
                    <?php
                    if( isset( $saved ) ) {
                        echo "<div class='alert alert-success'>Changes saved!</div>";
                    }
                    ?>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="alias.php">Alias</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo $alias; ?></li>
                        </ol>
                    </nav>
                    <form method="post">
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Description</span></div>
                            <?php
                            $filter = "(mail=" . $alias . ")";
                            $findAlias = ldap_search( $ds , LDAP_BASEDN , $filter );
                            $aliasDetail = ldap_get_entries( $ds , $findAlias );
                            if( ! empty( $aliasDetail[0]['description'] ) ) {
                                $description = $aliasDetail[0]['description'][0];
                            } else {
                                $description = "";
                            }
                            ?>
                            <input type="text" name="description" value="<?php echo $description; ?>" class="form-control">
                        </div>
                        <div class="mb-3">
                            <?php
                            $destinations = "";
                            unset( $current[0]['mailforwardingaddress']['count'] );
                            foreach( $current[0]['mailforwardingaddress'] as $address ) {
                                $destinations .= $address . ",";
                            }
                            $destinations = substr( $destinations , 0  , -1 );
                            ?>
                            <label for="destinations" class="form-label">Destinations (comma seperated):</label>
                            <textarea class="form-control" id="destinations" rows="3" name="destinations"><?php echo $destinations; ?></textarea>
                        </div>
                        <?php plugins_process( "alias_edit" , "form" ); ?>
                        <p><button type="submit" name="submit" class="btn btn-success"><i class="fas fa-save"></i>&nbsp;Save</button></p>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>