<nav class="navbar bg-primary navbar-dark navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><?php echo BRANDING; ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="<?php echo APP_MAIL; ?>"><strong>Mail</strong></a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo APP_DRIVE; ?>"><strong>Drive</strong></a></li>
                <?php
                function nav_item( $page , $link ) {
                    $current_page = str_replace( "/" , "" , $_SERVER['SCRIPT_NAME'] );
                    if( $current_page == $link ) {
                        echo "<li class='nav-item'><a href='$link' class='nav-link active'>$page</a></li>";
                    } else {
                        echo "<li class='nav-item'><a href='$link' class='nav-link'>$page</a></li>";
                    }
                }
            if( $_SESSION['admin_level'] !== "self" ) {
                nav_item( "Domains" , "domains.php" );
                nav_item( "Mailboxes" , "users.php" );
                nav_item( "Groups" , "groups.php" );
                nav_item( "Aliases" , "alias.php" );
                if( $_SESSION['admin_level'] == "global" ) {
                    nav_item( "Server" , "server.php" );
                }
            }
            nav_item( "Account" , "settings.php" );
            ?>
            </ul>
        </div>
    </div>
</nav>