<?php
include(__DIR__ . "/../backend/nodes.php");
$pageTitle = "Signatures";

if (!isset($_GET["s"]) || !isset($_GET["t"])) {
  header("Location: " . SERVER_NAME . "/error/404");
  exit();
}

$signing_id = $helpers->decrypt($_GET["s"] ?? "");
$template_id = $helpers->decrypt($_GET["t"] ?? "");

$template = $helpers->select_all_individual("templates", "id = '$template_id'");

$sentTemplate = $helpers->select_all_individual("sent_templates", "signing_id = '$signing_id' AND template_id = '$template_id' AND email_order = 1");

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

if (!$template) {
  http_response_code(404);
  header("Location: " . SERVER_NAME . "/error/404");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php include("../components/system/head.php") ?>
  <link rel="stylesheet" href="<?= SERVER_NAME . "/assets/css/print-document-style.css" ?>">
</head>

<body>
  <div class="wrapper d-flex flex-column">
    <?php include("./components/header.php") ?>

    <input type="hidden" id="templateId" value="<?= $template_id ?>">
    <input type="hidden" id="signing_id" value="<?= $signing_id ?>">
    <input type="hidden" id="docId" value="<?= $template->doc_id ?>">

    <input type="hidden" id="firstSignerFullName" value='<?= $signerLabel["firstSigner"]["full_name"] ?? "" ?>'>
    <input type="hidden" id="firstSignerEmail" value='<?= $signerLabel["firstSigner"]["email"] ?? "" ?>'>

    <input type="hidden" id="secondSignerFullName" value='<?= $signerLabel["secondSigner"]["full_name"] ?? "" ?>'>
    <input type="hidden" id="secondSignerEmail" value='<?= $signerLabel["secondSigner"]["email"] ?? "" ?>'>

    <input type="hidden" id="thirdSignerFullName" value='<?= $signerLabel["thirdSigner"]["full_name"] ?? "" ?>'>
    <input type="hidden" id="thirdSignerEmail" value='<?= $signerLabel["thirdSigner"]["email"] ?? "" ?>'>

    <input type="hidden" id="is_void" value="<?= $sentTemplate->is_void ?? "0" ?>">
    <input type="hidden" id="expired" value="<?= $sentTemplate->expired ?? "0" ?>">

    <div class="body flex-grow-1">
      <div class="container-fluid position-relative" style="overflow: auto;">
        <div class="justify-content-center align-items-center d-none mt-5" id="pdfLoading">
          <div class="text-center" style="z-index:10;">
            <div class="spinner-border text-success" role="status" style="width:3rem; height:3rem;">
              <span class="visually-hidden">Loading</span>
            </div>
            <div class="mt-2 text-success">Loading PDF</div>
          </div>
        </div>
        <div id="pdfContainer" class="d-none overflow-auto px-4"></div>
      </div>
    </div>
  </div>

</body>

<?php include("../components/system/scripts.php") ?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

<?php require(__DIR__ . "/../components/signatures/signature-signed-view-scripts.php") ?>

</html>