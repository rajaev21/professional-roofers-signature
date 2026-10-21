<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="apple-mobile-web-app-title" content="Professional Roofers" />
<meta name="application-name" content="Professional Roofers" />
<meta name="csrf-token" content="<?= htmlspecialchars($helpers->generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>" />

<link rel="manifest" href="<?= SERVER_NAME . "/images/favicon/site.webmanifest" ?>" />
<link rel="icon" type="image/png" href="<?= SERVER_NAME . "/images/favicon/favicon-96x96.png" ?>" sizes="96x96" />
<link rel="icon" type="image/svg+xml" href="<?= SERVER_NAME . "/images/favicon/favicon.svg" ?>" />
<link rel="shortcut icon" href="<?= SERVER_NAME . "/images/favicon/favicon.ico" ?>" />
<link rel="apple-touch-icon" sizes="180x180" href="<?= SERVER_NAME . "/images/favicon/apple-touch-icon.png" ?>" />

<title>Professional Roofers <?= isset($pageTitle) ? "- $pageTitle" : "" ?></title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.js"></script>


<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.2/mammoth.browser.min.js"></script> -->

<!-- PDF.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>

<!-- jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>

<!-- html2canvas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
  pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
</script>
<!-- moment.js -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

<?php if (isset($_SESSION["user_id"])): ?>
  <link rel="stylesheet" href="<?= SERVER_NAME . "/assets/vendors/@coreui/coreui/dist/css/coreui.min.css" ?>" />
  <link rel="stylesheet" href="<?= SERVER_NAME . "/assets/vendors/simplebar/dist/simplebar.min.css" ?>" />

  <!-- Include Select2 CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

  <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">

  <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>

  <!-- Include Select2 JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
  <script src="<?= SERVER_NAME . "/assets/js/config.js" ?>"></script>
  <script src="<?= SERVER_NAME . "/assets/js/color-modes.js" ?>"></script>

  <script src="<?= SERVER_NAME . "/assets/vendors/tinymce/tinymce.min.js" ?>"></script>
<?php endif; ?>

<link rel="stylesheet" href="<?= SERVER_NAME . "/assets/css/vendors/simplebar.css" ?>" />
<link rel="stylesheet" href="<?= SERVER_NAME . "/assets/css/style.css" ?>" />
<link rel="stylesheet" href="<?= SERVER_NAME . "/custom-assets/css/custom.css" ?>" />

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Toastr CSS & JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script src="<?= SERVER_NAME . "/custom-assets/js/custom.js" ?>"></script>
<script src="<?= SERVER_NAME . "/custom-assets/js/components.js" ?>"></script>

<!-- data tables -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.min.css" />
<script src="https://cdn.datatables.net/2.3.5/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.5/js/dataTables.bootstrap5.js"></script>