<div id="navigation-bar" class="navigation-bar">
  <div class="bar">
    <button id="navbox-trigger" class="navbox-trigger"><i class="fa fa-lg fa-th"></i></button>
    <div class="spacefiller"></div>
    <p style="color: white; display: inline-block;"><strong><?php echo BRANDING; ?></strong></p>
    <div class="notif">
      
    </div>
    <div class="user">
      <img src="images/default.jpg">
    </div>
    <div id="user_menu">
      <?php plugins_process( "user_menu" , "item" ); ?>
      <p>
        <a href="logout.php">Logout</a>
      </p>
    </div>
  </div>
  <div class="navbox">
    <div class="navbox-tiles">
      <a href="index.php" class="tile">
        <div class="icon">
         <i class="fa fa-home"></i>
        </div>
        <span class="title">Home</span>
      </a>
      
      <a href="domains.php" class="tile">
        <div class="icon">
          <i class="fa fa-building"></i>
        </div>
        <span class="title">Domains</span>
      </a>
    
      <a href="users.php" class="tile">
        <div class="icon">
          <i class="fa fa-id-badge"></i>
        </div>
        <span class="title">Users</span>
      </a>
      
      <a href="groups.php" class="tile">
        <div class="icon">
          <i class="fa fa-group"></i>
        </div>
        <span class="title">Groups</span>
      </a>
      
      <a href="alias.php" class="tile">
        <div class="icon">
          <i class="fa fa-mask"></i>
        </div>
        <span class="title">Aliases</span>
      </a>
      <?php plugins_process( "user_menu" , "main" ); ?>
      <!--<a href="settings.php" class="tile">
        <div class="icon">
          <i class="fa fa-cogs"></i>
        </div>
        <span class="title">Settings</span>
    </a>-->
  </div>
</div>
</script></div>
<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script> 
<script>
(function () {
    $(document).ready(function () {
        $('#navbox-trigger').click(function () {
            return $('#navigation-bar').toggleClass('navbox-open');
        });
        $('.user').click(function() {
          $('#user_menu').toggle();
        });
        return $(document).on('click', function (e) {
            var $target;
            $target = $(e.target);
            if (!$target.closest('.navbox').length && !$target.closest('#navbox-trigger').length) {
                return $('#navigation-bar').removeClass('navbox-open');
            }
        });
    });
}.call(this));
</script>
