<div class="navbox">
    <div class="navbox-tiles">
      <a href="index.php" class="tile">
        <div class="icon">
         <i class="fa fa-home"></i>
        </div>
        <span class="title">Home</span>
      </a>

      <a href="<?php echo APP_MAIL; ?>" target="_blank" class="tile">
        <div class="icon">
         <i class="fa fa-envelope"></i>
        </div>
        <span class="title">Mail</span>
      </a>
      <a href="<?php echo APP_DRIVE; ?>" target="_blank" class="tile">
        <div class="icon">
         <i class="fa fa-folder"></i>
        </div>
        <span class="title">Drive</span>
      </a>

      <?php if( $_SESSION['admin_level'] !== "self" ) { ?>
        <a href="users.php" class="tile">
            <div class="icon">
            <i class="fa fa-id-badge"></i>
            </div>
            <span class="title"><span style="color:orange">*</span>&nbsp;Users</span>
        </a>
        
        <a href="groups.php" class="tile">
            <div class="icon">
            <i class="fa fa-group"></i>
            </div>
            <span class="title"><span style="color:orange">*</span>&nbsp;Groups</span>
        </a>
        
        <a href="alias.php" class="tile">
            <div class="icon">
            <i class="fa fa-mask"></i>
            </div>
            <span class="title"><span style="color:orange">*</span>&nbsp;Aliases</span>
        </a>
      <?php } ?>
      <?php if( $_SESSION['admin_level'] == "global" ) { ?>
        <a href="domains.php" class="tile">
            <div class="icon">
            <i class="fa fa-building"></i>
            </div>
            <span class="title"><span style="color:red">*</span>&nbsp;Domains</span>
        </a>
        <a href="server.php" class="tile">
            <div class="icon">
            <i class="fa fa-server"></i>
            </div>
            <span class="title"><span style="color:red">*</span>&nbsp;Server</span>
        </a>
      <?php } ?>
      <?php if( $_SESSION['admin_level'] !== "self" && $_SESSION['admin_level'] !== "global" ) { ?>
        <a href="domain_edit.php" class="tile">
            <div class="icon">
            <i class="fa fa-building"></i>
            </div>
            <span class="title"><span style="color:orange">*</span>&nbsp;Organisation</span>
        </a>
      <?php } ?>
      <?php plugins_process( "user_menu" , "main" ); ?>
      <a href="settings.php" class="tile">
        <div class="icon">
          <i class="fa fa-cogs"></i>
        </div>
        <span class="title">Settings</span>
      </a>
  </div>