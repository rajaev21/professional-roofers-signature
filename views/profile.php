<?php
include("../backend/nodes.php");
include("../components/system/config.php");

$pageTitle = "Profile";

$userData = null;
$isCurrentUser = false;

$id = null;
if (!isset($_GET["id"])) {
  $userData = $current_user;
  $isCurrentUser = true;
} else {
  $id = $helpers->decrypt($_GET["id"]);
  $userData = $helpers->select_all_individual("users", "id = '$id'");
  $isCurrentUser = false;
}
$avatar_src = $helpers->get_avatar_link($userData->id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php include("../components/system/head.php") ?>
</head>

<body>
  <?php include("./components/side-bar.php") ?>
  <div class="wrapper d-flex flex-column min-vh-100">
    <?php include("./components/header.php") ?>

    <div class="body flex-grow-1">
      <div class="container">
        <div class="row mb-2">
          <div class="col-6 col-md-6">
            <h1 class="h3"><?= $pageTitle ?></h1>
          </div>
          <div class="col-6 col-md-6 text-end">
            <button type="button" class="btn btn-secondary text-white" onclick="window.history.back()">
              <i class="bi bi-chevron-left me-1"></i>
              Go Back
            </button>
          </div>
        </div>

        <div class="row">
          <!-- Overview / Profile Card -->
          <div class="col-12 ">
            <div class="card">
              <div class="card-header d-flex justify-content-end align-items-center flex-wrap gap-2 mb-3">
                <ul class="nav nav-tabs card-header-tabs " role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-overview" data-bs-toggle="tab" data-bs-target="#overview-pane" type="button" role="tab" aria-controls="overview-pane" aria-selected="false">
                      Overview
                    </button>
                  </li>
                  <?php if ($isCurrentUser): ?>
                    <li class="nav-item" role="presentation">
                      <button class="nav-link " id="tab-edit" data-bs-toggle="tab" data-bs-target="#form-edit" type="button" role="tab" aria-controls="form-edit" aria-selected="true">
                        Edit Profile
                      </button>
                    </li>
                    <li class="nav-item" role="presentation">
                      <button class="nav-link" id="tab-password" data-bs-toggle="tab" data-bs-target="#form-password" type="button" role="tab" aria-controls="form-password" aria-selected="false">
                        Change Password
                      </button>
                    </li>
                  <?php endif; ?>
                </ul>
              </div>

              <div class="card-body">
                <div class="tab-content">
                  <!-- Overview Tab Pane -->
                  <div class="tab-pane fade show active" id="overview-pane" role="tabpanel" aria-labelledby="tab-overview">
                    <div class="row gy-4">
                      <div class="col-12 col-md-3 d-flex flex-column align-items-center text-center">
                        <div class="position-relative d-inline-block mb-3">
                          <img id="ov-avatar" src="<?= $avatar_src ?>" alt="avatar" class="rounded-circle img-fluid object-fit-contain" style="width:150px; height:150px;">
                        </div>

                        <h4 class="mb-1 fw-semibold"><?= htmlspecialchars($userData->fname . ' ' . $userData->lname) ?></h4>
                        <div class="mb-2 w-100 d-flex justify-content-center">
                          <span id="ov-role" class="badge bg-primary small px-2 py-1"><?= htmlspecialchars($userData->role) ?></span>
                        </div>
                      </div>

                      <div class="col-md-1 d-none d-md-flex align-items-center justify-content-center px-0">
                        <div class="vr" style="height:200px; opacity:0.15;"></div>
                      </div>

                      <div class="col-12 col-md-8">
                        <div class="profile-info px-2">
                          <div class="row gy-3">
                            <div class="col-12 col-sm-6">
                              <small class="text-muted d-block">Email</small>
                              <div id="ov-email" class="fw-semibold text-break"><?= $userData->email ?? "--" ?></div>
                            </div>

                            <div class="col-12 col-sm-6">
                              <small class="text-muted d-block">Contact</small>
                              <div id="ov-contact" class="fw-semibold"><?= $userData->contact ?? "--" ?></div>
                            </div>

                            <div class="col-12">
                              <small class="text-muted d-block">Address</small>
                              <div id="ov-address" class="text-break"><?= $userData->address ?? "--" ?></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <?php if ($isCurrentUser): ?>
                    <!-- Edit Profile Form -->
                    <form id="form-edit" class="tab-pane fade" enctype="multipart/form-data" method="POST">
                      <input type="hidden" name="user_id" value="<?= $userData->id ?>">
                      <div class="row gx-4 gy-3 align-items-start">
                        <div class="col-12 col-md-3 d-flex flex-column align-items-center text-center">
                          <div class="position-relative mb-2 profile-avatar">
                            <img id="avatar-preview" src="<?= $avatar_src ?>" alt="avatar preview" class="rounded-circle img-fluid object-fit-contain" style="width:150px; height:150px;">

                            <label for="avatar" class="btn btn-primary btn-sm rounded-circle p-0 position-absolute bottom-0 end-0 d-flex align-items-center justify-content-center" title="Change avatar" aria-label="Change avatar" style="width:35px;height:35px;">
                              <i class="bi bi-pencil" aria-hidden="true"></i>
                            </label>
                            <input type="file" name="avatar" id="avatar" accept="image/*" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" aria-hidden="true">
                          </div>

                          <div class="mb-2 w-100 px-3 d-flex flex-column align-items-center text-center">
                            <small class="text-muted">Supported: JPG, PNG</small>
                            <label class="form-check mt-2 d-flex align-items-center justify-content-center">
                              <input class="form-check-input me-2" type="checkbox" name="remove_avatar" value="1" id="remove-avatar">
                              <span class="form-check-label">Remove current avatar</span>
                            </label>
                          </div>
                        </div>

                        <div class="col-md-1 d-none d-md-flex align-items-center justify-content-center px-0">
                          <div class="vr" style="height:200px; opacity:0.15;"></div>
                        </div>

                        <div class="col-12 col-md-8">
                          <div class="row g-3">
                            <div class="col-12 col-sm-6">
                              <label for="first_name" class="form-label">First name</label>
                              <input type="text" name="first_name" id="first_name" class="form-control" required value="<?= $userData->fname ?? "" ?>" placeholder="First name">
                            </div>

                            <div class="col-12 col-sm-6">
                              <label for="last_name" class="form-label">Last name</label>
                              <input type="text" name="last_name" id="last_name" class="form-control" required value="<?= $userData->lname ?? "" ?>" placeholder="Last name">
                            </div>

                            <div class="col-12 col-sm-6">
                              <label for="email" class="form-label">Email</label>
                              <input type="email" name="email" id="email" class="form-control" required value="<?= $userData->email ?? "" ?>" placeholder="Email address">
                            </div>

                            <div class="col-12 col-sm-6">
                              <label for="contact" class="form-label">Contact</label>
                              <input type="text" name="contact" id="contact" class="form-control" value="<?= $userData->contact ?? "" ?>" placeholder="Contact number">
                            </div>

                            <div class="col-12">
                              <label for="address" class="form-label">Address</label>
                              <input type="text" name="address" id="address" class="form-control" value="<?= $userData->address ?? "" ?>" placeholder="Address">
                            </div>
                          </div>

                          <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="submit" class="btn btn-primary" id="btn-save-profile">
                              <i class="bi bi-save-fill me-2"></i>
                              <span>Save changes</span>
                            </button>
                          </div>
                        </div>
                      </div>
                    </form>
                    <!-- Change Password Form -->
                    <form id="form-password" method="POST" class="tab-pane fade">
                      <input type="hidden" name="user_id" value="<?= $userData->id ?>">
                      <div class="row mb-3">
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="current_password">Current password</label>
                          <input type="password" name="current_password" id="current_password" class="form-control" required>
                        </div>
                      </div>

                      <div class="row mb-3">
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="new_password">New password</label>
                          <input type="password" name="new_password" id="new_password" class="form-control" required minlength="8" placeholder="At least 8 characters">
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="confirm_password">Confirm new password</label>
                          <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="8">
                        </div>

                        <div class="col-12 col-md-6 mt-3">
                          <label class="form-check">
                            <input class="form-check-input me-2" type="checkbox" id="showPasswords">
                            <span class="form-check-label">Show passwords</span>
                          </label>
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                          <button type="submit" class="btn btn-warning" id="btn-change-password">
                            <i class="bi bi-key-fill me-2"></i>
                            Change password
                          </button>
                        </div>
                      </div>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
    if (!isset($_GET["page"]) || (isset($_GET["page"]) && $_GET["page"] != "signature")) {
      include("./components/footer.php");
    }
    ?>
  </div>
  <?php include("../components/system/scripts.php") ?>
  <script>
    $("#showPasswords").on("change", function() {
      var type = this.checked ? "text" : "password";
      $("#current_password, #new_password, #confirm_password").attr("type", type);
    });

    $(function() {
      const STORAGE_KEY = 'admin_profile_active_tab';

      try {
        var saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
          var $btn = $('#' + saved);
          if ($btn.length) new bootstrap.Tab($btn.get(0)).show();
        }
      } catch (e) {}
      try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
          const btn = document.getElementById(saved);
          if (btn) new bootstrap.Tab(btn).show();
        }
      } catch (e) {}

      $('button[data-bs-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function(e) {
        try {
          localStorage.setItem(STORAGE_KEY, e.target.id);
        } catch (e) {}
      });
    });

    $('#form-edit').off('submit');
    $('#form-password').off('submit');
    $('#avatar').off('change');

    $('#avatar').on('change', function(e) {
      var f = this.files && this.files[0];
      if (!f) return;
      if (!f.type.match('image.*')) return;
      var reader = new FileReader();
      reader.onload = function(ev) {
        $('#avatar-preview').attr('src', ev.target.result);
        $('#remove-avatar').prop('checked', false);
      };
      reader.readAsDataURL(f);
    });

    $('#form-edit').on('submit', function(e) {
      e.preventDefault();
      swal.showLoading();

      let fd = new FormData(this);
      fd.append("action", "update_profile");

      $.ajax({
        url: '<?= SERVER_NAME . "/backend/nodes" ?>',
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(resp) {
          swal.fire({
            title: resp && resp.success ? 'Success' : 'Error',
            text: resp && resp.message ? resp.message : (resp && resp.success ? 'Profile updated successfully.' : 'Failed to update profile.'),
            icon: resp && resp.success ? 'success' : 'error',
            confirmButtonText: 'OK'
          }).then(() => {
            if (resp && resp.success) {
              location.reload();
            }
          })
        },
        error: function(jqXHR, textStatus, errorThrown) {
          swal.fire({
            title: 'Error',
            text: 'Network or server error while updating profile.',
            icon: 'error'
          });
        }
      });
    });

    $('#form-password').on('submit', function(e) {
      e.preventDefault();
      swal.showLoading();

      var newP = $('[name=new_password]').val(),
        conf = $('[name=confirm_password]').val();

      if (newP !== conf || newP.length < 8) {
        swal.fire({
          title: 'Error',
          text: newP.length < 8 ? 'New password must be at least 8 characters long.' : 'New password and confirmation do not match.',
          icon: 'error'
        });
        return;
      }

      let fd = new FormData(this);
      fd.append("action", "change_password");

      $.ajax({
        url: '<?= SERVER_NAME . "/backend/nodes" ?>',
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(resp) {
          swal.fire({
            title: resp && resp.success ? 'Success' : 'Error',
            text: resp && resp.message ? resp.message : (resp && resp.success ? 'Password changed successfully.' : 'Failed to change password.'),
            icon: resp && resp.success ? 'success' : 'error',
            confirmButtonText: 'OK'
          }).then(() => {
            if (resp && resp.success) {
              $('#form-password')[0].reset();
              $("#current_password, #new_password, #confirm_password").attr("type", "password");
            }
          })
        },
        error: function(jqXHR, textStatus, errorThrown) {
          swal.fire({
            title: 'Error',
            text: 'Network or server error while updating profile.',
            icon: 'error'
          });
        }
      });
    });
  </script>
</body>

</html>