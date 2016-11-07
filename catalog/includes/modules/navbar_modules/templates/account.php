<?php
use OSC\OM\OSCOM;
?>
<li class="dropdown">
  <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo isset($_SESSION['customer_id']) ? OSCOM::getDef('module_navbar_account_logged_in', ['customer_first_name' => $_SESSION['customer_first_name']]) : OSCOM::getDef('module_navbar_account_logged_out'); ?></a>
  <ul class="dropdown-menu">
    <?php
    if (isset($_SESSION['customer_id'])) {
      echo '<li><a href="' . OSCOM::link('logoff.php') . '">' . OSCOM::getDef('module_navbar_account_logoff') . '</a></li>';
    }
    else {
      echo '<li><a href="' . OSCOM::link('login.php') . '">' . OSCOM::getDef('module_navbar_account_login') . '</a></li>';
      echo '<li><a href="' . OSCOM::link('create_account.php') . '">' . OSCOM::getDef('module_navbar_account_register') . '</a></li>';
    }
    ?>
    <li class="divider"></li>
    <li><?php echo '<a href="' . OSCOM::link('account.php') . '">' . OSCOM::getDef('module_navbar_account') . '</a>'; ?></li>
    <li><?php echo '<a href="' . OSCOM::link('account_history.php') . '">' . OSCOM::getDef('module_navbar_account_history') . '</a>'; ?></li>
    <li><?php echo '<a href="' . OSCOM::link('address_book.php') . '">' . OSCOM::getDef('module_navbar_account_address_book') . '</a>'; ?></li>
    <li><?php echo '<a href="' . OSCOM::link('account_password.php') . '">' . OSCOM::getDef('module_navbar_account_password') . '</a>'; ?></li>
  </ul>
</li>