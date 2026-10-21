<div class="sidebar sidebar-dark sidebar-fixed border-end" id="sidebar">
  <div class="sidebar-header border-bottom d-flex justify-content-center align-items-center">
    <div class="sidebar-brand">
      <a href="<?= SERVER_NAME . "/views/dashboard" ?>">
        <img src="<?= SERVER_NAME . "/images/professional-roofers-logo-dark.png" ?>" alt="Logo" class="d-inline-block align-text-top img-fluid" width="150px">
      </a>
    </div>
    <button class="btn-close d-lg-none" type="button" data-coreui-theme="dark" aria-label="Close" onclick='coreui.Sidebar.getInstance(document.querySelector("#sidebar")).toggle()'></button>
  </div>
  <ul class="sidebar-nav" data-coreui="navigation" data-simplebar>
    <?php
    $get_sidebar_data = $helpers->side_bar_data($current_user->role);

    foreach ($get_sidebar_data as $menu) :
      $menu = (object)$menu;
      $config = (object)$menu->config;
    ?>
      <li class="nav-item">
        <a class="nav-link <?= $helpers->is_active_menu($config->url, $_SERVER["REQUEST_URI"]) ?>" href="<?= $config->url ?>">
          <i class="nav-icon <?= $config->icon ?>"></i>
          <span><?= $menu->title ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</div>