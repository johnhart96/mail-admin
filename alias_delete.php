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
        if( isset( $_GET['confirm'] ) ) {
            plugins_process( "alias_delete" , "submit" );
            if( alias_delete( $alias ) ) {
                go( "alias.php?deleted" );
            } else {
                die( "cannot delete!" );
            }
        }
        ?>
    </head>
    <body>
        <?php require 'inc/topbar.php'; ?>
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1>Delete Aliases</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="alias.php">Alias</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Delete</li>
                        </ol>
                    </nav>
                    <?php plugins_process( "alias_delete" , "submit" ); ?>
                    <div class="alert alert-warning">Are you sure you want to delete `<?php echo $alias; ?>`?</div>
                    <p>
                        <a href="alias_delete.php?alias=<?php echo $alias; ?>&confirm" class="btn btn-danger"><i class="fas fa-check"></i>&nbsp;Yes</a>
                        <a href="alias.php" class="btn btn-success"><i class="fas fa-times"></i>&nbsp;No</a>
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>