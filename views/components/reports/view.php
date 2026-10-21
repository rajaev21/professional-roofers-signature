<?php
$id = $_GET['id'];
$signing_id = $_GET['s'];

$decrypted_id = $helpers->decrypt($id);
$decrypted_signing_id = $helpers->decrypt($signing_id);

$template = $helpers->select_all_individual("templates", "id = '$decrypted_id'");

$signerLabel = [
  "firstSigner" => array(
    "full_name" => "",
    "email" => ""
  ),
  "secondSigner" => array(
    "full_name" => "",
    "email" => ""
  ),
  "thirdSigner" => array(
    "full_name" => "",
    "email" => ""
  )
];

$fullName = "";
$email = "";
?>
<div class="container-fluid position-relative">
  <div class="card">
    <div class="card-header">
      <div class="row mb-2">
        <div class="col-6 col-md-6">
          <h5 class="card-title">
            <?= $template->name ?>
          </h5>
        </div>
        <div class="col-6 col-md-6 text-end">
          <button type="button" class="btn btn-secondary text-white" onclick='window.location.replace(`<?= SERVER_NAME . "/views/reports?page=progress&id=$id&s=$signing_id" ?>`)'>
            <i class="bi bi-chevron-left me-1"></i>
            Go Back
          </button>
        </div>
      </div>

    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6" id="signatureEmailConfig">
          <div class="px-2">
            <?php
            $sent_templates = $helpers->select_all_with_params("sent_templates", "template_id = '$decrypted_id' AND signing_id = '$decrypted_signing_id' ORDER BY email_order ASC");
            if (count($sent_templates) > 0):
              foreach ($sent_templates as $sent_template):

                if ($sent_template->email_order == 1) {
                  $signerLabel["firstSigner"]["full_name"] = $sent_template->name;
                  $signerLabel["firstSigner"]["email"] = $sent_template->email;
                } else if ($sent_template->email_order == 2) {
                  $signerLabel["secondSigner"]["full_name"] = $sent_template->name;
                  $signerLabel["secondSigner"]["email"] = $sent_template->email;
                } else if ($sent_template->email_order == 3) {
                  $signerLabel["thirdSigner"]["full_name"] = $sent_template->name;
                  $signerLabel["thirdSigner"]["email"] = $sent_template->email;
                }

                $status = "Pending";
                $statusColor = "warning";

                if ($sent_template->signed_finished_date) {
                  $status = "Signed";
                  $statusColor = "success";
                } else if (($sent_template->primary_signature || $sent_template->initial_signature) || $sent_template->signed_data_id) {
                  if ($sent_template->signed_data_id) {
                    $status = "Signing Document";
                  }
                  $status = "Signature Created ";
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
            ?>
                <div class="row mb-3">
                  <div class="col-md-12">
                    <h4 class="d-inline">Signature/ Initials <?= $sent_template->email_order ?>:</h4>
                    <span class="badge bg-<?= $statusColor ?> ms-2"><?= $status ?></span>
                  </div>

                  <div class="col-md-12 px-lg-5 mb-2">
                    <label for="firstSignerName" class="form-label">Name</label>
                    <input type="text" class="form-control" value="<?= $sent_template->name ?>" required>
                  </div>
                  <div class="col-md-12 px-lg-5 mb-2">
                    <label for="firstSignerEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?= $sent_template->email ?>" required>
                  </div>
                </div>
                <hr>
              <?php endforeach; ?>
              <div class="row mb-3">
                <h4>Add Message</h4>
                <div class="col-md-12 px-lg-5 mb-2">
                  <label for="emailSubject" class="form-label">Email Subject</label>
                  <input type="text" name="email_subject" id="emailSubject" class="form-control" value="<?= $sent_template->email_subject ?>" maxlength="100" required>
                </div>
                <div class="col-md-12 px-lg-5 mb-2">
                  <label for="emailMessage" class="form-label">Email Message</label>
                  <textarea name="email_message" id="emailMessage" class="form-control" rows="10" maxlength="1000"><?= htmlentities($sent_template->email_message) ?></textarea>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-md-6 overflow-y-auto p-0 position-relative" id="pdfViewerContainer" style="height: 70vh;">
          <a href="<?= SERVER_NAME . "/views/reports?page=fullscreen&id=$id&s=$signing_id" ?>" class="btn btn-secondary position-absolute top-0 end-0 m-2 z-1" title="Fullscreen">
            <i class="bi bi-arrows-fullscreen"></i>
          </a>

          <div class="border border-5 border-black rounded h-100 bg-white shadow-lg position-relative" style="overflow: auto;">
            <div class="justify-content-center align-items-center d-none mt-5" id="pdfLoading">
              <div class="text-center" style="z-index:10;">
                <div class="spinner-border text-success" role="status" style="width:3rem; height:3rem;">
                  <span class="visually-hidden">Loading</span>
                </div>
                <div class="mt-2 text-success">Loading PDF</div>
              </div>
            </div>
            <div id="pdfContainer" class="p-3 d-none"></div>
          </div>
        </div>
      </div>
      <input type="hidden" id="templateId" value="<?= $template->id ?>">
      <input type="hidden" id="signing_id" value="<?= $decrypted_signing_id ?>">
      <input type="hidden" id="docId" value="<?= $template->doc_id ?>">

      <input type="hidden" id="firstSignerFullName" value='<?= $signerLabel["firstSigner"]["full_name"] ?? "" ?>'>
      <input type="hidden" id="firstSignerEmail" value='<?= $signerLabel["firstSigner"]["email"] ?? "" ?>'>

      <input type="hidden" id="secondSignerFullName" value='<?= $signerLabel["secondSigner"]["full_name"] ?? "" ?>'>
      <input type="hidden" id="secondSignerEmail" value='<?= $signerLabel["secondSigner"]["email"] ?? "" ?>'>

      <input type="hidden" id="thirdSignerFullName" value='<?= $signerLabel["thirdSigner"]["full_name"] ?? "" ?>'>
      <input type="hidden" id="thirdSignerEmail" value='<?= $signerLabel["thirdSigner"]["email"] ?? "" ?>'>

      <input type="hidden" id="is_void" value="<?= $sent_template->is_void ?? "0" ?>">
      <input type="hidden" id="expired" value="<?= $sent_template->expired ?? "0" ?>">
    </div>
  </div>
</div>

<?php require(__DIR__ . "/../../../components/signatures/signature-signed-view-scripts.php") ?>