<?php
include("../backend/nodes.php");
include("../components/system/config.php");

$pageTitle = "Companies";
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
      <div class="container-sm px-4">
        <?php
        if (isset($_GET["page"])):
          $page = $_GET["page"];
          if ($page == "add") {
            include("./components/companies/add.php");
          } elseif ($page == "edit") {
            if (isset($_GET["id"])) {
              $decrypted_id = $helpers->decrypt($_GET["id"]);
              $company = $helpers->select_all_individual("companies ", "id = '$decrypted_id'");

              if ($company == null) {
                http_response_code(404);
                header("Location: " . SERVER_NAME . "/error/404");
                exit();
              } else {
                include("./components/companies/edit.php");
              }
            } else {
              http_response_code(404);
              header("Location: " . SERVER_NAME . "/error/404");
              exit();
            }
          } elseif ($page == "view") {
            $decrypted_id = $helpers->decrypt($_GET["id"]);
            $company = $helpers->select_all_individual("companies", "id='$decrypted_id'");
            $users = $helpers->select_user_by_company_id($decrypted_id);

            if ($company == null && $users == null) {
              http_response_code(404);
              header("Location: " . SERVER_NAME . "/error/404");
              exit();
            }

            include("./components/companies/view.php");
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
              <a href="<?= SERVER_NAME . "/views/companies?page=add" ?>" class="btn btn-success text-white">
                <i class="bi bi-plus me-1"></i>
                Add Company
              </a>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped" id="companiesTable">
                  <thead>
                    <tr>
                      <th class="d-table-cell d-md-none">More</th>
                      <th>Logo</th>
                      <th>Name</th>
                      <th class="d-none d-md-table-cell">Contact</th>
                      <th class="d-none d-md-table-cell">Address</th>
                      <th class="d-none d-md-table-cell">Email</th>
                      <th class="d-none d-md-table-cell">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $fields = [
                      "id",
                      "logo",
                      "name",
                      "contact",
                      "address",
                      "email",
                    ];
                    $companies = $helpers->select_custom_fields("companies", $fields);

                    if (count($companies) > 0):
                      foreach ($companies as $company) :
                        $encrypted_id = $helpers->encrypt($company->id);
                    ?>
                        <tr>
                          <td class="d-table-cell d-md-none"><button class="btn btn-transparent" onclick="viewMore(this)"> <i class="bi bi-three-dots"></i></button></td>
                          <td class="td-image">
                            <img src="<?= $helpers->get_company_img_link($company->id) ?>" alt="Company Logo" class="rounded img-thumbnail logo" style="width: 85px; height: 85px;" onclick='handleOpenModalImg($(this), `modalCompanyLogo`, `companyID`)'>
                          </td>
                          <td><?= $company->name ?></td>
                          <td class="d-none d-md-table-cell"><?= $company->contact ?></td>
                          <td class="d-none d-md-table-cell"><?= $company->address ?></td>
                          <td class="d-none d-md-table-cell"><?= $company->email ?></td>
                          <td class="d-none d-md-table-cell text-center">
                            <a href="<?= SERVER_NAME . "/views/companies?page=view&id=$encrypted_id" ?>" type="button" class="btn btn-primary my-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="View">
                              <i class="bi bi-eye"></i>
                            </a>

                            <a href="<?= SERVER_NAME . "/views/companies?page=edit&id=$encrypted_id" ?>" class="btn btn-warning my-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Edit">
                              <i class="bi bi-pencil-square"></i>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="6" class="text-center text-muted">
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

    <?= $helpers->generate_modal_img("modalCompanyLogo", "companyID") ?>
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
        if (index < 3) return;
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