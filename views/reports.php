<?php
include("../backend/nodes.php");
include("../components/system/config.php");

$pageTitle = "Reports";
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
      <?php
      if (isset($_GET["page"])):
        $page = $_GET["page"];
        if ($page === "progress") {
          include("./components/reports/progress.php");
        } else if ($page === "fullscreen") {
          include("./components/reports/fullscreen.php");
        } else if ($page === "view") {
          include("./components/reports/view.php");
        } else {
          http_response_code(404);
          header("Location: " . SERVER_NAME . "/error/404");
          exit();
        }
      ?>

      <?php else: ?>
        <div class="container">
          <div class="row mb-2">
            <div class="col-6 col-md-6">
              <h1 class="h3">Signing Activity</h1>
            </div>
          </div>
          <div class="card">
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped" id="templatesTable">
                  <thead>
                    <tr>
                      <th class="d-table-cell d-md-none">More</th>
                      <th>Template Name</th>
                      <th>Assigned User</th>
                      <th>Status</th>
                      <th class="d-none d-md-table-cell">Date Created</th>
                      <th class="d-none d-md-table-cell">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $params = "id IS NOT NULL AND is_void = 0 AND expired = 0";

                    if ($current_user->role === "admin") {
                      $params .= " AND sender_id = '$current_user->id' AND is_void = 0 AND expired = 0";
                    }

                    $sent_templates = $helpers->select_all_with_params("sent_templates", "$params GROUP BY signing_id ORDER BY id DESC");
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
                          <td class="d-table-cell d-md-none"><button class="btn btn-transparent btn-sm" onclick="viewMore(this)"> <i class="bi bi-three-dots"></i></button></td>
                          <td>
                            <?= $template->name ?>
                            <p class="text-muted mb-0 mt-2 text-capitalize">
                              <small><?= strtolower($recipientsText) ?></small>
                            </p>
                          </td>
                          <td><?= $sender ?></td>
                          <td><span class="badge bg-<?= $statusColor ?> ms-2"><?= $status ?></span></td>
                          <td class="d-none d-md-table-cell"><?= date("F j, Y g:i a", strtotime($sent_template->email_sent_date)) ?></td>
                          <td class="d-none d-md-table-cell">
                            <a href="<?= SERVER_NAME . "/views/reports?page=progress&s=$encrypted_signing_id" ?>" type="button" class="btn btn-primary my-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Preview">
                              <i class="bi bi-eye"></i>
                            </a>
                            <button type="button" class="btn btn-danger my-1" onclick="handleVoid('<?= $sent_template->signing_id ?>')" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Void">
                              <i class="bi bi-x-circle"></i>
                            </button>
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

        <div class="container mt-4">
          <div class="row mb-2">
            <div class="col-6 col-md-6">
              <h1 class="h3">Void Documents</h1>
            </div>
          </div>
          <div class="card">
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped" id="templatesTable">
                  <thead>
                    <tr>
                      <th>Template Name</th>
                      <th>Assigned User</th>
                      <th>Date Created</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $params = "id IS NOT NULL AND is_void = 1 AND expired = 0";

                    if ($current_user->role === "admin") {
                      $params .= " AND sender_id = '$current_user->id' AND is_void = 1 AND expired = 0";
                    }

                    $sent_templates = $helpers->select_all_with_params("sent_templates", "$params GROUP BY signing_id ORDER BY id DESC");
                    if (count($sent_templates) > 0):
                      foreach ($sent_templates as $sent_template):
                        $template = $helpers->select_custom_fields_individual("templates", ["name"], "id = '$sent_template->template_id'");

                        $recipients = $helpers->select_custom_fields_with_params("sent_templates", ["name", "signed_finished_date"], "template_id = '$sent_template->template_id' AND signing_id = '$sent_template->signing_id' ORDER BY email_order ASC");

                        $recipientsText = $helpers->format_recipients_name($recipients);

                        $sender = $helpers->get_full_name($sent_template->sender_id);
                        $encrypted_signing_id = $helpers->encrypt($sent_template->signing_id);
                    ?>
                        <tr>
                          <td>
                            <?= $template->name ?>
                            <p class="text-muted mb-0 mt-2 text-capitalize">
                              <small><?= strtolower($recipientsText) ?></small>
                            </p>
                          </td>
                          <td><?= $sender ?></td>
                          <td><?= date("F j, Y g:i a", strtotime($sent_template->email_sent_date)) ?></td>
                          <td>
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

        <div class="container mt-4">
          <div class="row mb-2">
            <div class="col-6 col-md-6">
              <h1 class="h3">Expired Documents</h1>
            </div>
          </div>
          <div class="card">
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped" id="templatesTable">
                  <thead>
                    <tr>
                      <th>Template Name</th>
                      <th>Assigned User</th>
                      <th>Date Created</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $params = "id IS NOT NULL AND expired = 1";

                    if ($current_user->role === "admin") {
                      $params .= " AND sender_id = '$current_user->id' AND expired = 1";
                    }

                    $sent_templates = $helpers->select_all_with_params("sent_templates", "$params GROUP BY signing_id ORDER BY id DESC");
                    if (count($sent_templates) > 0):
                      foreach ($sent_templates as $sent_template):
                        $template = $helpers->select_custom_fields_individual("templates", ["name"], "id = '$sent_template->template_id'");

                        $recipients = $helpers->select_custom_fields_with_params("sent_templates", ["name", "signed_finished_date"], "template_id = '$sent_template->template_id' AND signing_id = '$sent_template->signing_id' ORDER BY email_order ASC");

                        $recipientsText = $helpers->format_recipients_name($recipients);

                        $sender = $helpers->get_full_name($sent_template->sender_id);
                        $encrypted_signing_id = $helpers->encrypt($sent_template->signing_id);
                    ?>
                        <tr>
                          <td>
                            <?= $template->name ?>
                            <p class="text-muted mb-0 mt-2 text-capitalize">
                              <small><?= strtolower($recipientsText) ?></small>
                            </p>
                          </td>
                          <td><?= $sender ?></td>
                          <td><?= date("F j, Y g:i a", strtotime($sent_template->email_sent_date)) ?></td>
                          <td>
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


    function handleVoid(signingId) {
      swal.fire({
        title: "Are you sure?",
        text: "This action will void the signing process. This cannot be undone.",
        icon: "warning",
        buttons: true,
        dangerMode: true,
        showDenyButton: true,
        confirmButtonText: "Yes",
        denyButtonText: "No",
      }).then((d) => {
        if (d.isConfirmed) {
          $.ajax({
            url: "<?= SERVER_NAME . "/backend/nodes" ?>",
            type: "POST",
            data: {
              action: "void_sent_template",
              signing_id: signingId
            },
            dataType: "json",
            success: function(response) {
              swal.fire({
                title: response.success ? 'Success' : "Error",
                html: response.message,
                icon: response.success ? 'success' : "error",
              }).then(() => response.success ? location.reload() : null);
            },
            error: function(xhr, status, error) {
              swal.fire({
                title: "Error",
                html: "An error occurred while processing your request.<br><b>Status:</b> " + status + "<br><b>Error:</b> " + error,
                icon: "error"
              });
            }
          });
        }
      });
    }
  </script>
</body>
<?php include("../components/system/scripts.php") ?>

</html>