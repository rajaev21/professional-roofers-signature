<div class="container-fluid">
  <div class="row mb-2">
    <div class="col-6 col-md-6">
      <h1 class="h3">Progress</h1>
    </div>
    <div class="col-6 col-md-6 text-end">
      <button type="button" class="btn btn-secondary text-white" onclick="window.history.back()">
        <i class="bi bi-chevron-left me-1"></i>
        Go Back
      </button>
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
              <th>Email</th>
              <th class="d-none d-md-table-cell">Status</th>
              <th class="d-none d-md-table-cell">Resent Counter</th>
              <th class="d-none d-md-table-cell">Date sent</th>
              <th class="d-none d-md-table-cell">Signed Date</th>
              <th class="d-none d-md-table-cell">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $signing_id = $_GET['s'];
            $decrypted_signing_id = $helpers->decrypt($signing_id);

            $sent_templates = $helpers->select_all_with_params("sent_templates", "signing_id = '$decrypted_signing_id' ORDER BY email_order ASC");
            if (count($sent_templates) > 0):
              foreach ($sent_templates as $sent_template):
                $resentBy = $helpers->encrypt($current_user->id);

                $template = $helpers->select_all_individual("templates", "id = $sent_template->template_id");
                $encrypted_id = $helpers->encrypt($sent_template->template_id);

                $resendLabel = "Resend Email";

                $status = "Pending";
                $statusColor = "warning";

                if ($sent_template->signed_finished_date) {
                  $status = "Signed";
                  $statusColor = "success";
                } else if (($sent_template->primary_signature || $sent_template->initial_signature) || $sent_template->signed_data_id) {
                  $status = "Signature Created ";
                  if ($sent_template->signed_data_id) {
                    $status = "Signing Document";
                  }
                  $statusColor = "primary";
                } else if ($sent_template->terms_agree) {
                  $status = "Terms Agreed";
                  $statusColor = "info";
                } else if ($sent_template->is_email_sent == "0" && $sent_template->email_error) {
                  $status = "Not Sent";
                  $statusColor = "danger";
                } else if ($sent_template->is_email_sent == "1") {
                  $status = "Email Sent";
                  $statusColor = "secondary";
                }

                $resentData = $helpers->select_all_with_params("resent_emails", "sent_template_id = '$sent_template->id' ORDER BY date_created DESC");
                if (count($resentData) > 0) {
                  $resentData = array_map(function ($item) use ($helpers, $sent_template) {
                    return [
                      "signatory" => $sent_template->name,
                      "date_resent" => date("F j, Y g:i a", strtotime($item->date_created)),
                      "resent_by" => $helpers->get_full_name($item->resent_by),
                      "type" => ucfirst($item->type),
                    ];
                  }, $resentData);
                }
            ?>
                <tr>
                  <td class="d-table-cell d-md-none"><button class="btn btn-transparent" onclick="viewMore(this)"> <i class="bi bi-three-dots"></i></button></td>
                  <td>
                    <?= $template->name ?>
                    <p class="text-muted mb-0 mt-2 text-capitalize">
                      <small>To: <?= strtolower($sent_template->name) ?></small>
                    </p>
                  </td>
                  <td><?= $sent_template->email ?></td>
                  <td class="d-none d-md-table-cell" >
                    <span class="badge bg-<?= $statusColor ?> ms-2"><?= $status ?></span>
                  </td>
                  <td class="d-none d-md-table-cell text-center">
                    <button type="button" class="btn btn-info btn-sm text-light" onclick='handleShowHistory(`<?= json_encode($resentData) ?>`)'>
                      <?= count($resentData) ?>
                    </button>
                  </td>
                  <td class="d-none d-md-table-cell">
                    <?php if ($sent_template->email_sent_date): ?>
                      <?= date("F j, Y g:i a", strtotime($sent_template->email_sent_date)) ?>
                    <?php endif; ?>
                  </td>
                  <td class="d-none d-md-table-cell">
                    <?php if ($sent_template->signed_finished_date): ?>
                      <?= date("F j, Y g:i a", strtotime($sent_template->signed_finished_date)) ?>
                    <?php endif; ?>
                  </td>
                  <td class="d-none d-md-table-cell">
                    <button type="button" onclick="handlePreviewDocument('<?= $sent_template->signing_link ?>')" class="btn btn-primary my-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Preview Document">
                      <i class="bi bi-file-earmark-text text-light"></i>
                    </button>
                    <?php
                    if ($sent_template->signed_finished_date):
                      $resendLabel = "Resend Completed Document";
                    ?>
                      <a href="<?= SERVER_NAME . "/views/reports?page=view&id=$encrypted_id&s=$signing_id" ?>" type="button" class="btn btn-primary my-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Preview">
                        <i class="bi bi-eye"></i>
                      </a>
                    <?php endif; ?>
                    <?php if ($sent_template->is_email_sent == "1" && !$sent_template->is_void && !$sent_template->expired): ?>
                      <button type="button" onclick="resendEmail('<?= $sent_template->id ?>', '<?= $resentBy ?>')" class="btn btn-success my-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= $resendLabel ?>">
                        <i class="bi bi-arrow-repeat text-light"></i>
                      </button>
                    <?php endif; ?>
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

  <!-- Resend History Modal -->
  <div class="modal fade" id="resentHistoryModal" tabindex="-1" aria-labelledby="resentHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="resentHistoryModalLabel"><span id="resentTemplateName"></span> Resend History</h5>
          <button type="button" class="btn-close" onclick="closeHistoryModal()" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Date Resent</th>
                <th>Resent By</th>
                <th>Document Type</th>
              </tr>
            </thead>
            <tbody id="resentHistoryTableBody"></tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeHistoryModal()">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function handlePreviewDocument(signingLink) {
    const url = new URL(signingLink);
    window.open(`<?= SERVER_NAME . "/public/viewing" ?>${url.search}`, '_blank');
  }

  function closeHistoryModal() {
    $('#resentHistoryModal').modal('hide');
    $('#resentHistoryTableBody').empty();
    $('#resentTemplateName').empty();
  }

  function handleShowHistory(resentDataJson) {
    let resentData;
    try {
      resentData = JSON.parse(resentDataJson);
    } catch (e) {
      resentData = [];
    }
    const tbody = $('#resentHistoryTableBody');
    tbody.empty();
    if (resentData.length === 0) {
      tbody.append('<tr><td colspan="4" class="text-center text-muted">No history found.</td></tr>');
    } else {
      $('#resentTemplateName').text(resentData[0].signatory);
      $.each(resentData, function(idx, item) {
        tbody.append(`
        <tr>
          <td>${item.date_resent}</td>
          <td>${item.resent_by || ''}</td>
          <td>${item.type || ''}</td>
        </tr>
      `);
      });
    }
    $('#resentHistoryModal').modal('show');
  }

  function resendEmail(sentTemplateId, resentBy) {
    swal.fire({
        title: "Are you sure?",
        text: "Do you want to resend the email?",
        icon: "warning",
        confirmButtonText: "Yes",
        showDenyButton: true,
        denyButtonText: "No"
      })
      .then((d) => {
        if (d.isConfirmed) {
          let fd = new FormData();
          fd.append("action", "resend_email");
          fd.append("sent_template_id", sentTemplateId);
          fd.append("resent_by", resentBy);

          $.ajax({
            url: '<?= SERVER_NAME . "/backend/nodes" ?>',
            type: 'POST',
            contentType: false,
            processData: false,
            data: fd,
            dataType: 'JSON',
            success: function(data) {
              swal.fire({
                title: data.success ? "Success" : "Error",
                text: data.success ? "Email resent successfully." : (data.message || "Failed to resend email."),
                icon: data.success ? "success" : "error"
              }).then(() => data.success && location.reload());
            },
            error: function() {
              swal.fire("Error", "An error occurred while resending email.", "error");
            }
          });
        }
      });
  }
</script>