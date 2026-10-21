<?php
$id = $_GET['id'];
$signing_id = $_GET['s'];

$decrypted_id = $helpers->decrypt($id);
$decrypted_signing_id = $helpers->decrypt($signing_id);

$template = $helpers->select_all_individual("templates", "id = '$decrypted_id'");

$sentTemplate = $helpers->select_all_individual("sent_templates", "signing_id = '$decrypted_signing_id' AND template_id = '$decrypted_id' AND email_order = 1");

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

$signerNames = $helpers->select_all_with_params("sent_templates", "signing_id = '$signing_id'");

if ($signerNames) {
  foreach ($signerNames as $signerName) {
    if ($signerName->email_order == 1) {
      $signerLabel["firstSigner"]["full_name"] = $signerName->name;
      $signerLabel["firstSigner"]["email"] = $signerName->email;
    } else if ($signerName->email_order == 2) {
      $signerLabel["secondSigner"]["full_name"] = $signerName->name;
      $signerLabel["secondSigner"]["email"] = $signerName->email;
    } else if ($signerName->email_order == 3) {
      $signerLabel["thirdSigner"]["full_name"] = $signerName->name;
      $signerLabel["thirdSigner"]["email"] = $signerName->email;
    }
  }
}
?>
<div class="d-none" id="signature-toolbar">
  <div class="row">
    <div class="col-3 d-flex justify-content-start align-items-center">
      <h5 style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;" data-bs-popper="tooltip" data-bs-placement="bottom" title="<?= $template->name ?>">
        <?= $template->name ?>
      </h5>
    </div>
    <div class="col-6 d-flex justify-content-center">
      <div class="btn-group" role="group" aria-label="Basic example">
        <button type="button" class="btn btn-outline-success" onclick="handleCustomZoom('in')" data-bs-placement="bottom" data-bs-original-title="Zoom in">
          <i class="bi bi-zoom-in"></i>
          Zoom In
        </button>
        <button type="button" class="btn btn-outline-success" onclick="handleCustomZoom('out')" data-bs-placement="bottom" data-bs-original-title="Zoom out">
          <i class="bi bi-zoom-out"></i>
          Zoom Out
        </button>
      </div>
    </div>

    <div class="col-3 text-end align-bottom">
      <button type="button" class="btn btn-secondary text-white" onclick="window.history.back()">
        <i class="bi bi-chevron-left me-1"></i>
        Go Back
      </button>
    </div>
  </div>
</div>

<div class="container-fluid position-relative">
  <div class="row">
    <div class="col-md-12">
      <div class="justify-content-center align-items-center d-none mt-5" id="pdfLoading">
        <div class="text-center" style="z-index:10;">
          <div class="spinner-border text-success" role="status" style="width:3rem; height:3rem;">
            <span class="visually-hidden">Loading</span>
          </div>
          <div class="mt-2 text-success">Loading PDF</div>
        </div>
      </div>
      <div id="pdfContainer" class="d-flex flex-column justify-content-center align-items-center d-none"></div>
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

  <input type="hidden" id="is_void" value="<?= $sentTemplate->is_void ?? "0" ?>">
</div>

<?php require(__DIR__ . "/../../../components/signatures/signature-signed-view-scripts.php") ?>