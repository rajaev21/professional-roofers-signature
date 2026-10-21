<header class="header header-sticky p-0 mb-4">
  <div class="container-fluid border-bottom px-4">
    <button class="header-toggler" type="button" onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()" style="margin-inline-start: -14px">
      <svg class="icon icon-lg">
        <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-menu" ?>"></use>
      </svg>
    </button>
    <!-- <ul class="header-nav ms-auto">
      <li class="nav-item">
        <a class="nav-link" href="#">
          <svg class="icon icon-lg">
            <use xlink:href="<?= SERVER_NAME . "/assets/vendors//@coreui/icons/sprites/free.svg#cil-bell" ?>"></use>
          </svg></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">
          <svg class="icon icon-lg">
            <use xlink:href="<?= SERVER_NAME . "/assets/vendors//@coreui/icons/sprites/free.svg#cil-list-rich" ?>"></use>
          </svg></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">
          <svg class="icon icon-lg">
            <use xlink:href="<?= SERVER_NAME . "/assets/vendors//@coreui/icons/sprites/free.svg#cil-envelope-open" ?>"></use>
          </svg></a>
      </li>
    </ul> -->
    <ul class="header-nav">
      <li class="nav-item dropdown">
        <button class="btn btn-link nav-link py-2 px-2 d-flex align-items-center" type="button" aria-expanded="false" data-bs-toggle="dropdown">
          <svg class="icon icon-lg theme-icon-active">
            <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-contrast" ?>"></use>
          </svg>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" style="--cui-dropdown-min-width: 8rem">
          <li>
            <button class="dropdown-item d-flex align-items-center" type="button" data-coreui-theme-value="light">
              <svg class="icon icon-lg me-3">
                <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-sun" ?>"></use>
              </svg>
              <span>
                Light
              </span>
            </button>
          </li>
          <li>
            <button class="dropdown-item d-flex align-items-center" type="button" data-coreui-theme-value="dark">
              <svg class="icon icon-lg me-3">
                <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-moon" ?>"></use>
              </svg>
              <span>
                Dark
              </span>
            </button>
          </li>
          <li>
            <button class="dropdown-item d-flex align-items-center active" type="button" data-coreui-theme-value="auto">
              <svg class="icon icon-lg me-3">
                <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-contrast" ?>"></use>
              </svg>
              <span>
                Auto
              </span>
            </button>
          </li>
        </ul>
      </li>
      <li class="nav-item py-1">
        <div class="vr h-100 mx-2 text-body text-opacity-75"></div>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link py-0 pe-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
          <div class="avatar avatar-md">
            <img class="avatar-img" src="<?= $helpers->get_avatar_link($current_user->id) ?>" />
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-end pt-0">
          <div class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold my-2">
            <div class="fw-semibold">
              Settings
            </div>
          </div>
          <a class="dropdown-item" href="<?= SERVER_NAME . "/views/profile" ?>">
            <svg class="icon me-2">
              <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-user" ?>"></use>
            </svg>
            Profile
          </a>
          <a class="dropdown-item" href="<?= SERVER_NAME . "/views/help-center" ?>">
            <i class="bi bi-info-lg"></i>
            Help Center
          </a>
          <!-- <a class="dropdown-item" href="#">
            <svg class="icon me-2">
              <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-settings" ?>"></use>
            </svg>
            Settings
          </a> -->
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="javascript:void(0)" onclick="handleLogout()">
            <svg class="icon me-2">
              <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-account-logout" ?>"></use>
            </svg>
            Logout
          </a>
        </div>
      </li>
    </ul>
  </div>
  <?php
  if (isset($_GET['page'])):
    $page = $_GET["page"];
    $uri = $_SERVER["REQUEST_URI"];

    $visibleInPage = array(
      "templates" => array(
        "signature",
        "view",
      ),
      "reports" => array(
        "fullscreen"
      )
    );
    $visiblePageInReport = array(
      "fullscreen"
    );

    if (
      in_array($page, $visibleInPage["templates"]) && str_contains($uri, "/views/templates") ||
      in_array($page, $visibleInPage["reports"]) && str_contains($uri, "/views/reports")
    ):
  ?>
      <div class="container-fluid px-4">
        <div id="signature-toolbar-container" class="w-100"></div>
      </div>
    <?php endif; ?>
  <?php endif; ?>

</header>

<?php include(__DIR__ . "/../../components/loading-screen.php") ?>

<script>
  function handleLogout() {
    swal.fire({
      title: "Logout",
      text: "Are you sure you want to logout?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, logout",
      cancelButtonText: "No, stay signed in",
      reverseButtons: true,
      allowOutsideClick: false
    }).then((result) => {
      if (result.isConfirmed) {
        let fd = new FormData();
        fd.append("action", "logout");

        $.ajax({
          type: "POST",
          url: "<?= SERVER_NAME . "/backend/nodes" ?>",
          processData: false,
          contentType: false,
          cache: false,
          data: fd,
          dataType: 'JSON',
          success: (response) => {
            if (response.success) {
              window.location.href = response.redirect;
            } else {
              alert({
                icon: 'error',
                title: 'Error',
                html: response.message,
                theme: $("html").attr("data-bs-theme"),
              });
            }
          },
          error: () => {
            alert({
              icon: 'error',
              title: 'Oops...',
              text: 'Something went wrong!',
              theme: $("html").attr("data-bs-theme"),
            });
          }
        });
      }
    });
  }
</script>