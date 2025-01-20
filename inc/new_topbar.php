<nav class="navbar bg-primary navbar-dark navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><?php echo BRANDING; ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="<?php echo APP_MAIL; ?>"><i class="fas fa-mail-bulk"></i>&nbsp;Mail</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo APP_DRIVE; ?>"><i class="fas fa-folder"></i>&nbsp;Drive</a></li>
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
                    if( $_SESSION['admin_level'] == "global" ) {
                        nav_item( "<i class='fas fa-building'></i>&nbsp;Domains" , "domains.php" );
                    } else {
                        nav_item( "<i class='fas fa-building'></i>&nbsp;Org Settings" , "domain_edit.php" );
                    }
                    nav_item( "<i class='fas fa-inbox'></i>&nbsp;Mailboxes" , "users.php" );
                    nav_item( "<i class='fa fa-group'></i>&nbsp;Groups" , "groups.php" );
                    nav_item( "<i class='fas fa-mask'></i>&nbsp;Aliases" , "alias.php" );
                    if( $_SESSION['admin_level'] == "global" ) {
                        nav_item( "<i class='fas fa-server'></i>&nbsp;Server" , "server.php" );
                    }
                }
                nav_item( "<i class='fas fa-user-alt'></i>&nbsp;Account" , "settings.php" );
                ?>
            </ul>
        </div>
    </div>
</nav>
