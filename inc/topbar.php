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
      <p><u><?php echo ucfirst( $_SESSION['ldap']['displayname'][0] ); ?></u></p>
      <p>
        <a href="settings.php">Settings</a> <br />
        <a href="logout.php">Logout</a>
      </p>
    </div>
  </div>
  <?php require 'inc/menu.php'; ?>
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
