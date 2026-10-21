<?php
include("../backend/nodes.php");
include("../components/system/config.php");

$pageTitle = "Users";
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
      <div class="container-lg px-4">
        <?php
        if (isset($_GET["page"])):
          $page = $_GET["page"];
          if ($page == "add") {
            include("./components/users/add.php");
          } elseif ($page == "edit") {
            if (isset($_GET["id"])) {
              $decrypted_id = $helpers->decrypt($_GET["id"]);
              $user = $helpers->select_all_individual("users", "id = '$decrypted_id'");

              if ($user == null) {
                http_response_code(404);
                header("Location: " . SERVER_NAME . "/error/404");
                exit();
              } else {
                include("./components/users/edit.php");
              }
            } else {
              http_response_code(404);
              header("Location: " . SERVER_NAME . "/error/404");
              exit();
            }
          } elseif ($page == "view") {
            if (isset($_GET["id"])) {
              $decrypted_id = $helpers->decrypt($_GET["id"]);
              $user = $helpers->select_all_individual("users", "id = '$decrypted_id'");

              if ($user == null) {
                http_response_code(404);
                header("Location: " . SERVER_NAME . "/error/404");
                exit();
              } else {
                include("./components/users/view.php");
              }
            } else {
              http_response_code(404);
              header("Location: " . SERVER_NAME . "/error/404");
              exit();
            }
          } else {
            http_response_code(404);
            header("Location: " . SERVER_NAME . "/error/404");
            exit();
          }
        ?>
        <?php else: ?>
          <div class="row mb-2">
            <div class="col-6 col-md-6">
              <h1 class="h3"><?= $pageTitle ?></h1>
            </div>
            <div class="col-6 col-md-6 text-end">
              <a href="<?= SERVER_NAME . "/views/users?page=add" ?>" class="btn btn-success text-white">
                <i class="bi bi-plus me-1"></i>
                Add User
              </a>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped" id="usersTable">
                  <thead>
                    <tr>
                      <th class="d-table-cell d-md-none">More</th>
                      <th>Avatar</th>
                      <th>Name</th>
                      <th>Contact</th>
                      <th class="d-none d-md-table-cell">Address</th>
                      <th class="d-none d-md-table-cell">Email</th>
                      <th class="d-none d-md-table-cell">Role</th>
                      <th class="d-none d-md-table-cell">Company</th>
                      <th class="d-none d-md-table-cell">Position</th>
                      <th class="d-none d-md-table-cell">Status</th>
                      <th class="d-none d-md-table-cell">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $users = $helpers->select_all_with_params("users", "id <> '$current_user->id' ORDER BY id DESC");
                    if (count($users) > 0) :
                      foreach ($users as $user) :
                        $encrypted_id = $helpers->encrypt($user->id);
                        $company = $helpers->select_custom_fields_individual("companies", ["name"], "id = '$user->company_id'");
                    ?>
                        <tr>
                          <td class="d-table-cell d-md-none"><button class="btn btn-transparent btn-sm" onclick="viewMore(this)"> <i class="bi bi-three-dots"></i></button></td>
                          <td class="td-image">
                            <img src="<?= $helpers->get_avatar_link($user->id) ?>" alt="User Avatar" class="rounded-circle avatar" style="width: 80px; height: 80px;" onclick='handleOpenModalImg($(this), `modalUserAvatar`, `userAvatarID`)'>
                          </td>
                          <td><?= $helpers->get_full_name($user->id) ?></td>
                          <td><?= $user->contact ?>
                          </td>
                          <td class="d-none d-md-table-cell"><?= $user->address ?></td>
                          <td class="d-none d-md-table-cell"><?= $user->email ?></td>
                          <td class="d-none d-md-table-cell"><?= $user->role ?></td>
                          <td class="d-none d-md-table-cell"><?= $company->name ?? "" ?></td>
                          <td class="d-none d-md-table-cell"><?= $user->position ?></td>
                          <td class="d-none d-md-table-cell">
                            <?php if ($user->status == "1"): ?>
                              <i class="fa-solid fa-circle-check me-1 text-success"></i>
                              Active
                            <?php else: ?>
                              <i class="fa-solid fa-circle-xmark me-1 text-danger"></i>
                              Inactive
                            <?php endif; ?>
                          </td>
                          <td class="d-none d-md-table-cell text-center">
                            <a href="<?= SERVER_NAME . "/views/profile?id=$encrypted_id" ?>" type="button" class="btn btn-primary my-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="View">
                              <i class="bi bi-eye"></i>
                            </a>

                            <a href="<?= SERVER_NAME . "/views/users?page=edit&id=$encrypted_id" ?>" class="btn btn-warning my-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Edit">
                              <i class="bi bi-pencil-square"></i>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="10" class="text-center text-muted">
                          There are no records to display.
                        </td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        <?php endif; ?>

      </div>
    </div>

    <?= $helpers->generate_modal_img("modalUserAvatar", "userAvatarID") ?>
    <?php include("./components/footer.php") ?>
  </div>
  <script>
    function viewMore(btn) {
      $("tr.extra-row").remove();
      var th = $(btn).closest("table").find("th");
      var tr = $(btn).closest("tr");
      var detailsHtml = "";
      console.log(th)
      tr.find("td").each(function(index) {
        if (index < 4) return;
        detailsHtml += `<div><strong>${$(th[index]).text()}: </strong>${$(this).html()}</div>`;
      });

      tr.after(`
    <tr class="extra-row">
      <td colspan="100%">
        ${detailsHtml}
      </td>
    </tr>
  `);
    }
  </script>
</body>
<?php include("../components/system/scripts.php") ?>

</html>