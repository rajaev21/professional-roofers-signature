<?php
include("../backend/nodes.php");
include("../backend/controllers/Dashboard.php");
include("../components/system/config.php");

$dashboard = new Dashboard($conn, $helpers);
$dashboardCounts = $dashboard->get_count_data($current_user);

$pageTitle = "Dashboard";
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
      <?php if (isset($_GET["page"])):
        $page = $_GET["page"];
        include("./components/dashboard/more.php");
      else: ?>
        <div class="container-lg px-4">
          <?php ?>
          <!-- Last 6 Months -->
          <!-- <div class="card">
          <div class="card-header">
            <div class="card-title">
              <p class="fs-4 m-0 fw-semibold"> Last 6 months</p>
            </div>
          </div>
          <div class="card-body"> -->
          <div class="row g-4">
            <div class="col-12 col-sm-6 col-xl-4 col-xxl-3">
              <div class="card overflow-hidden">
                <div class="card-body p-0 d-flex align-items-center">
                  <div class="bg-secondary text-white py-4 px-5 me-3">
                    <svg class="icon icon-xxl">
                      <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-settings" ?>"></use>
                    </svg>
                  </div>
                  <div>
                    <div class="fs-3 fw-semibold text-secondary">
                      <span> <?= count($dashboardCounts["action"]) ?> </span>
                    </div>
                    <div class="text-body-secondary text-uppercase fw-semibold small">
                      Action Required
                    </div>
                  </div>
                </div>
                <div class="card-footer px-3 py-2">
                  <a href="<?= SERVER_NAME . "/views/dashboard?page=action" ?>" class="btn-block text-body-secondary d-flex justify-content-between align-items-center">
                    <span class="small fw-semibold">View More</span>
                    <svg class="icon">
                      <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-chevron-right" ?>"></use>
                    </svg>
                  </a>
                </div>
              </div>
            </div>
            <!-- /.col-->
            <div class="col-12 col-sm-6 col-xl-4 col-xxl-3">
              <div class="card overflow-hidden">
                <div class="card-body p-0 d-flex align-items-center">
                  <div class="bg-warning text-white py-4 px-5 me-3">
                    <svg class="icon icon-xxl">
                      <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-clock" ?>"></use>
                    </svg>
                  </div>
                  <div>
                    <div class="fs-3 fw-semibold text-warning">
                      <span> <?= count($dashboardCounts["waiting"]) ?> </span>
                    </div>
                    <div class="text-body-secondary text-uppercase fw-semibold small">
                      Waiting for Others
                    </div>
                  </div>
                </div>
                <div class="card-footer px-3 py-2">
                  <a href="<?= SERVER_NAME . "/views/dashboard?page=waiting" ?>" class="btn-block text-body-secondary d-flex justify-content-between align-items-center">
                    <span class="small fw-semibold">View More</span>
                    <svg class="icon">
                      <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-chevron-right" ?>"></use>
                    </svg>
                  </a>
                </div>
              </div>
            </div>
            <!-- /.col-->
            <div class="col-12 col-sm-6 col-xl-4 col-xxl-3">
              <div class="card overflow-hidden">
                <div class="card-body p-0 d-flex align-items-center">
                  <div class="bg-danger text-white py-4 px-5 me-3">
                    <svg class="icon icon-xxl">
                      <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-warning" ?>"></use>
                    </svg>
                  </div>
                  <div>
                    <div class="fs-3 fw-semibold text-danger">
                      <span> <?= count($dashboardCounts["expiring"]) ?> </span>
                    </div>
                    <div class="text-body-secondary text-uppercase fw-semibold small">
                      Expiring Soon
                    </div>
                  </div>
                </div>
                <div class="card-footer px-3 py-2">
                  <a href="<?= SERVER_NAME . "/views/dashboard?page=expiring" ?>" class="btn-block text-body-secondary d-flex justify-content-between align-items-center">
                    <span class="small fw-semibold">View More</span>
                    <svg class="icon">
                      <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-chevron-right" ?>"></use>
                    </svg>
                  </a>
                </div>
              </div>
            </div>
            <!-- /.col-->
            <div class="col-12 col-sm-6 col-xl-4 col-xxl-3">
              <div class="card overflow-hidden">
                <div class="card-body p-0 d-flex align-items-center">
                  <div class="bg-success text-white py-4 px-5 me-3">
                    <svg class="icon icon-xxl">
                      <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-check-circle" ?>"></use>
                    </svg>
                  </div>
                  <div>
                    <div class="fs-3 fw-semibold text-success">
                      <span> <?= count($dashboardCounts["completed"]) ?> </span>
                    </div>
                    <div class="text-body-secondary text-uppercase fw-semibold small">
                      Completed
                    </div>
                  </div>
                </div>
                <div class="card-footer px-3 py-2">
                  <a href="<?= SERVER_NAME . "/views/dashboard?page=completed" ?>" class="btn-block text-body-secondary d-flex justify-content-between align-items-center">
                    <span class="small fw-semibold">View More</span>
                    <svg class="icon">
                      <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-chevron-right" ?>"></use>
                    </svg>
                  </a>
                </div>
              </div>

            </div>
            <!-- /.col-->
          </div>
          <!-- </div>
        </div> -->

          <div class="card mt-3">
            <div class="card-header">
              <div class="card-title">
                <p class="fs-4 m-0 fw-semibold">Activity</p>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped" id="templatesTable">
                  <thead>
                    <tr>
                      <th class="d-table-cell d-md-none">More</th>
                      <th>Template Name</th>
                      <th>Assigned User</th>
                      <th class="d-none d-md-table-cell">Status</th>
                      <th class="d-none d-md-table-cell">Date Created</th>
                      <th class="d-none d-md-table-cell">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $params = "id IS NOT NULL AND is_void = 0 AND expired = 0";

                    if ($current_user->role === "admin") {
                      $params .= " AND sender_id = '$current_user->id'";
                    }

                    $sent_templates = $helpers->select_all_with_params("sent_templates", "$params GROUP BY signing_id ORDER BY id DESC LIMIT 10");
                    if (count($sent_templates) > 0):
                      foreach ($sent_templates as $sent_template):
                        $template = $helpers->select_custom_fields_individual("templates", ["name"], "id = '$sent_template->template_id'");

                        $recipients = $helpers->select_custom_fields_with_params("sent_templates", ["name", "signed_finished_date"], "template_id = '$sent_template->template_id' AND signing_id = '$sent_template->signing_id' ORDER BY email_order ASC");

                        $recipientsText = $helpers->format_recipients_name($recipients);

                        $isSignedAll = count(array_filter($recipients, function ($recipient) {
                          return !empty($recipient->signed_finished_date);
                        })) === count($recipients);

                        $sender = $helpers->get_full_name($sent_template->sender_id);
                        $encrypted_signing_id = $helpers->encrypt($sent_template->signing_id);

                        $status = "Ongoing";
                        $statusColor = "warning";

                        if ($isSignedAll) {
                          $status = "Signed";
                          $statusColor = "success";
                        }
                    ?>
                        <tr>
                          <td class="d-table-cell d-md-none"><button class="btn btn-transparent" onclick="viewMore(this)"> <i class="bi bi-three-dots"></i></button></td>
                          <td>
                            <?= $template->name ?>
                            <p class="text-muted mb-0 mt-2 text-capitalize">
                              <small><?= strtolower($recipientsText) ?></small>
                            </p>
                          </td>
                          <td><?= $sender ?></td>
                          <td class="d-none d-md-table-cell"><span class="badge bg-<?= $statusColor ?> ms-2"><?= $status ?></span></td>
                          <td class="d-none d-md-table-cell"><?= date("F j, Y g:i a", strtotime($sent_template->email_sent_date)) ?></td>
                          <td class="d-none d-md-table-cell">
                            <a href="<?= SERVER_NAME . "/views/reports?page=progress&s=$encrypted_signing_id" ?>" type="button" class="btn btn-primary my-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Preview">
                              <i class="bi bi-eye"></i>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="7" class="text-center text-muted">
                          No report found.
                        </td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

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