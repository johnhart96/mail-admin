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

        // Submit
        if( isset( $_POST['submit'] ) ) {
            if( ! empty( $_POST['description'] ) ) {
                $description = filter_var( $_POST['description'] , FILTER_SANITIZE_STRING );
            } else {
                $description = NULL;
            }
            ldap_modify( $ds , $dn , array( "description" => $description ) );
            if( ! empty( $_POST['addAddress'] ) ) {
                $add = filter_var( $_POST['addAddress'] , FILTER_VALIDATE_EMAIL );
                ldap_mod_add( $ds , $dn , array( "mailforwardingaddress" => $add ) );
            }
            $saved = TRUE;
            $getCurrent = ldap_search( $ds , $dn , $filter );
            $current = ldap_get_entries( $ds , $getCurrent );
            plugins_process( "alias_edit" , "submit" );
        }
        // Remove
        if( isset( $_GET['delete'] ) ) {
            $delete = filter_var( $_GET['delete'] , FILTER_VALIDATE_EMAIL );
            ldap_mod_del( $ds , $dn , array( "mailforwardingaddress" => $delete ) );
            header( "Location: alias_edit.php?alias=" . $alias );
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
                            <span class="input-group-btn"><button type="submit" name="submit" class="btn btn-success"><i class="fas fa-save"></i></button></span>
                        </div>
                        <p></p>
                        <table class="table table-border table-stripped">
                            <tr>
                                <th colspan="2">Forwarding Address:</th>
                            </tr>
                            <?php
                            unset( $current[0]['mailforwardingaddress']['count'] );
                            if( isset( $current[0]['mailforwardingaddress'] ) ) {
                                foreach( $current[0]['mailforwardingaddress'] as $address ) {
                                    echo "<tr>";
                                    echo '<td>' . $address . "</td>";
                                    echo "<td width='1'><a href='alias_edit.php?alias=$alias&delete=" . $address . "' class='btn btn-danger'><i class='fas fa-trash'></i></a></td>";
                                    echo "</tr>";
                                } 
                            }
                            ?>
                            <tr>
                                <td><input type="text" name="addAddress" class="form-control"></td>
                                <td width="1"><button type="submit" name="submit" class="btn btn-success"><i class="fas fa-plus"></i></td>
                            </tr>
                        </table>
                        <?php plugins_process( "alias_edit" , "form" ); ?>
                        
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>