<?php
$title = "";

if ($page == "action") {
  $title = "Action Required";
} else if ($page == "waiting") {
  $title = "Waiting for Others";
} else if ($page == "expiring") {
  $title = "Expiring Soon";
} else if ($page == "completed") {
  $title = "Completed";
}

?>
<div class="container">
  <div class="row mb-2">
    <div class="col-6 col-md-6">
      <h1 class="h3"><?= $title ?></h1>
    </div>
    <div class="col-6 col-md-6 text-end">
      <button type="button" class="btn btn-secondary text-white" onclick="window.history.back()">
        <i class="bi bi-chevron-left me-1"></i>
        Go Back
      </button>
    </div>
  </div>

  <div class="card mt-3">
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
            if (count($dashboardCounts[$page]) > 0):
              foreach ($dashboardCounts[$page] as $signingId):
                $recipients = $helpers->select_all_with_params("sent_templates", "signing_id = '$signingId' ORDER BY email_order ASC");

                $recipientsText = $helpers->format_recipients_name($recipients);

                $template = $helpers->select_custom_fields_individual("templates", ["name"], "id = '{$recipients[0]->template_id}'");

                $isSignedAll = count(array_filter($recipients, function ($recipient) {
                  return !empty($recipient->signed_finished_date);
                })) === count($recipients);

                $sender = $helpers->get_full_name($recipients[0]->sender_id);
                $encrypted_signing_id = $helpers->encrypt($signingId);

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
                  <td class="d-none d-md-table-cell"><?= date("F j, Y g:i a", strtotime($recipients[0]->email_sent_date)) ?></td>
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