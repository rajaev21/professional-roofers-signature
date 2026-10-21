<?php
include(__DIR__ . "/../backend/nodes.php");
$pageTitle = "Terms Agreement";

if (!isset($_GET["s"]) || !isset($_GET["t"]) || !isset($_GET["c"])) {
  header("Location: " . SERVER_NAME . "/error/404");
  exit();
}

$signing_id = $helpers->decrypt($_GET["s"]);
$template_id = $helpers->decrypt($_GET["t"]);
$customer = $helpers->decrypt($_GET["c"]);

$template = $helpers->select_all_individual("templates", "id = '$template_id'");

$sentTemplate = $helpers->select_all_individual("sent_templates", "id = '$customer'");

if (!$template || !$sentTemplate) {
  http_response_code(404);
  header("Location: " . SERVER_NAME . "/error/404");
  exit();
}

if (!$sentTemplate->signed_finished_date && $sentTemplate->terms_agree == 1) {
  $location = "";
  if (!$sentTemplate->signed_finished_date && ($sentTemplate->primary_signature || $sentTemplate->initial_signature)) {
    $location = (SERVER_NAME . "/public/signing?s=" . $helpers->encrypt($signing_id) . "&t=" . $helpers->encrypt($template_id) . "&c=" . $helpers->encrypt($customer));
  } else {
    $location = (SERVER_NAME . "/public/signature?s=" . $helpers->encrypt($signing_id) . "&t=" . $helpers->encrypt($template_id) . "&c=" . $helpers->encrypt($customer));
  }
  echo "<script>window.location.replace('" . $location . "');</script>";
  exit();
}

$template_title = $template->name ?? "Document";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php include("../components/system/head.php") ?>
</head>

<body>
  <div class="wrapper d-flex flex-column min-vh-100">

    <div class="body flex-grow-1">
      <div class="container-fluid position-relative">
        <div class="row">
          <div class="col-md-12">
            <?php if (($sentTemplate->signed_finished_date && $sentTemplate->terms_agree == 1) || $sentTemplate->expired == 1 || $sentTemplate->is_void == 1) : ?>
              <div class="container d-block py-2">
                <div class="row justify-content-center align-content-center" style="height: 90vh;">
                  <div class="col-md-8 col-lg-6">
                    <div class="card shadow p-4 text-center w-100">
                      <?php if ($sentTemplate->is_void == 1 && $sentTemplate->expired == 0): ?>
                        <div class="voided-icon mb-3" style="font-size: 3rem; color: #dc3545;">
                          <i class="bi bi-file-earmark-x-fill"></i>
                        </div>
                        <h2 class="mb-4 text-danger">Document Voided</h2>
                        <p class="mb-4 text-secondary">
                          This document has been voided and is no longer available for signing.<br>
                          If you believe this is an error, please contact support for assistance.
                        </p>
                        <div class="alert alert-info text-start py-2 px-3 mb-3" role="alert" style="font-size: 0.95rem;">
                          <i class="bi bi-exclamation-circle-fill me-1"></i>
                          <strong>Note:</strong>
                          Voided documents cannot be signed or completed. Please check your email for further instructions or contact the administrator.
                        </div>
                      <?php else: ?>
                        <div class="expired-icon mb-3" style=" font-size: 3rem; color: #dc3545;">
                          <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <h2 class="mb-4 text-danger">Link Expired</h2>
                        <p class="mb-4 text-secondary">
                          Sorry, this link has expired and is no longer valid.<br>
                          Please contact support if you need assistance.
                        </p>
                        <div class="alert alert-info text-start py-2 px-3 mb-3" role="alert" style="font-size: 0.95rem;">
                          <i class="bi bi-info-circle-fill me-1"></i>
                          <strong>Note:</strong>
                          This document may have already been signed and completed. <br> If you already signed, please wait for the completion email.
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php elseif ($sentTemplate->terms_agree == 0 && !$sentTemplate->signed_finished_date): ?>
              <div class="container d-block py-2" id="container-terms">
                <div class="row justify-content-center align-content-center" style="height: 90vh;">
                  <div class="col-md-8 col-lg-6">
                    <div class="card">
                      <form id="form-terms" class="w-100" method="POST">
                        <input type="hidden" name="customer_template_id" value="<?= $_GET["c"] ?>">
                        <input type="hidden" name="template_id" value="<?= $_GET["t"] ?>">
                        <input type="hidden" name="signing_id" value="<?= $_GET["s"] ?>">
                        <input type="hidden" name="action" value="agree_terms">

                        <div class="card-body">
                          <div class="alert alert-info" role="alert">
                            <small>
                              <p class="mb-2">
                                <strong>Welcome to Professional Roofers!</strong>
                              </p>
                              <ul class="mb-2 ps-4">
                                <li>
                                  To view your document, please accept the terms and conditions below.
                                </li>
                                <li>
                                  After accepting, you’ll be guided to sign and initial using our signature pad.
                                </li>
                                <li>
                                  <span class="text-danger fw-bold">Note:</span> Accepting the terms <strong>does not</strong> sign the document immediately. You must click on the designated signature and initial fields to complete signing.
                                </li>
                                <li>
                                  You can close the document at any time to save your progress.
                                </li>
                              </ul>
                              <p class="mb-0">
                                If you need help, please contact our support team.
                              </p>
                            </small>
                          </div>
                          <hr>
                          Please read the <a class="btn btn-link p-0 align-top" onclick="toggleModal('open')">Electronic Record and Signature Disclosure</a>.
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="iAgree" name="terms_agree" value="TRUE">
                            <label class="form-check-label" for="iAgree">
                              I agree to use electronic record and signatures.
                            </label>
                          </div>
                        </div>
                        <div class="card-footer text-end">
                          <button class="btn btn-primary" type="submit" id="btnTerms" disabled>
                            Continue
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>

              <div class="modal fade" id="modalTerms" tabindex="-1" aria-labelledby="modalTerms" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">
                        Terms and Conditions for Electronic Signature Use
                      </h5>
                      <button type="button" class="btn-close" onclick="toggleModal()" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="container">
                        <p style="text-indent: 50px;">
                          By accessing and using our services, you agree to these Terms and Conditions for Electronic Signature Use ("Terms"). These Terms govern the use of electronic signatures for transactions and communications between you ("User") and [Professional Roofers Signatures]. Please read these Terms carefully.
                        </p>
                        <p style="text-indent: 50px;">
                          By using electronic signatures in your interactions with [Professional Roofers Signatures], you acknowledge and agree to the terms outlined below.
                        </p>

                        <ol>
                          <li class="fw-bold py-2">Legal Acceptance of Electronic Signatures</li>
                          <p>
                            Under the <strong>Electronic Signatures in Global and National Commerce Act (ESIGN Act, 15 U.S.C. § 7001 et seq.)</strong> and the <strong>Uniform Electronic Transactions Act (UETA),</strong> electronic signatures and records have the same legal force and effect as handwritten signatures and paper documents in the United States. By agreeing to these Terms, you acknowledge that:
                          </p>
                          <ul>
                            <li>An electronic signature is legally binding;</li>
                            <li>Electronically signed agreements or contracts are enforceable and carry the same legal weight as physical documents;</li>
                            <li>You consent to the use of electronic signatures and records for all interactions with [Professional Roofers Signatures].</li>
                          </ul>
                          <li class="fw-bold py-2">Consent to Electronic Transactions</li>
                          <p>
                            By using our services, you agree to conduct business electronically and receive all relevant disclosures, notices, contracts, and other communications in electronic format. You understand and agree that:
                          </p>
                          <ul>
                            <li>You can retain electronic records by printing or saving them electronically for your records;</li>
                            <li>You have the right to request paper copies of electronic documents by contacting [Professional Roofers Signatures], though additional fees may apply;</li>
                            <li>You may need specific hardware and software to access and retain electronic records (as specified in Section 5).</li>
                          </ul>
                          <li class="fw-bold py-2">Right to Withdraw Consent</li>
                          <p>
                            You have the right to withdraw your consent to use electronic signatures or receive electronic communications. To do so, you must provide written notice to [Professional Roofers Signatures] at [Company Contact Information]. Withdrawal of consent may impact your ability to use certain services, and [Professional Roofers Signatures] reserves the right to terminate or limit services upon withdrawal.
                          </p>
                          <li class="fw-bold py-2">Technology Requirements</li>
                          <p>
                            To access, sign, and retain electronic documents, you must have:
                          </p>
                          <ul>
                            <li>A computer, mobile device, or similar device capable of accessing the internet;</li>
                            <li>Software to view PDF files or other commonly used formats (e.g., Adobe Reader);</li>
                            <li>A valid email account for receiving notifications and communications.</li>
                          </ul>
                          <p>
                            You are responsible for ensuring that you have the necessary equipment and software to access and retain electronic records.
                          </p>
                          <li class="fw-bold py-2">Verification of Identity</li>
                          <p>
                            By using electronic signatures, you affirm that you are authorized to sign and that the information provided is accurate and truthful. [Professional Roofers Signatures] reserves the right to require additional verification of identity for any electronically signed document.
                          </p>
                          <li class="fw-bold py-2">Governing Law</li>
                          <p>
                            These Terms are governed by all applicable laws in the United States, including the <strong>ESIGN Act, UETA,</strong> and relevant state laws. Any disputes arising from or related to the use of electronic signatures will be governed by the laws of [State Name], unless otherwise specified.
                          </p>
                          <li class="fw-bold py-2">Modification of Terms</li>
                          <p>
                            [Professional Roofers Signatures] reserves the right to modify or update these Terms at any time without prior notice. Continued use of electronic signatures or services provided by [Professional Roofers Signatures] after such modifications constitutes acceptance of the revised Terms.
                          </p>
                          <li class="fw-bold py-2">Severability</li>
                          <p>
                            If any provision of these Terms is deemed invalid or unenforceable under applicable law, the remaining provisions will remain valid and enforceable to the fullest extent permitted by law.
                          </p>
                          <li class="fw-bold py-2">Acceptance of Terms</li>
                          <p>
                            By clicking "I Agree," submitting an electronic form, or otherwise indicating your acceptance electronically, you confirm that:
                          </p>
                          <ul>
                            <li>You have read and understood these Terms and Conditions;</li>
                            <li>You consent to the use of electronic signatures for transactions with [Professional Roofers Signatures];</li>
                            <li>You agree that electronic signatures are legally binding and enforceable.</li>
                          </ul>
                        </ol>

                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" onclick="toggleModal()">Close</button>
                      <button type="button" class="btn btn-primary" id="btnAgree" disabled>
                        <i class="bi bi-check-circle-fill me-2"></i>
                        I Agree
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

  </div>
  <input type="hidden" id="templateId" value="<?= $template->id ?>">
  <?php include(__DIR__ . "/../components/loading-screen.php") ?>
</body>

<?php include("../components/system/scripts.php") ?>

<script>
  $("#form-terms").on("submit", function(e) {
    const searchParams = new URLSearchParams(window.location.search);
    const customerTemplateId = searchParams.get("c");
    const templateId = searchParams.get("t");
    const signingId = searchParams.get("s");

    e.preventDefault();
    if ($("#iAgree").is(":checked")) {
      $.ajax({
        url: "<?= SERVER_NAME ?>/backend/nodes",
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(response) {
          if (response.success) {
            window.location.replace(`<?= SERVER_NAME ?>/public/signature?s=${signingId}&t=${templateId}&c=${customerTemplateId}`);
          } else {
            swal.fire({
              icon: "error",
              title: "Error",
              text: response.message || "An error occurred while processing your request."
            });
          }
        },
        error: function() {
          swal.fire({
            icon: "error",
            title: "Error",
            text: "An error occurred while processing your request."
          });
        }
      });
    } else {
      swal.fire({
        icon: "warning",
        title: "Warning",
        text: "You must agree to the terms to continue."
      });
    }
  });

  $("#iAgree").on("change", function() {
    if ($(this).is(":checked")) {
      $("#btnTerms").removeAttr("disabled");
    } else {
      $("#btnTerms").attr("disabled", true);
    }
  });

  function toggleModal(action = "hide") {
    if (action === 'open') {
      $("#modalTerms").modal("show");
    } else {
      $("#modalTerms").modal("hide");
    }
  }

  let hasScrolledToBottom = false;

  $("#modalTerms .modal-body").on("scroll", function() {
    const $this = $(this);
    if ($this[0].scrollHeight - $this.scrollTop() <= $this.outerHeight() + 10) {
      hasScrolledToBottom = true;
      $("#btnAgree").removeAttr("disabled");
    }
  });

  $("#modalTerms").on("shown.bs.modal", function() {
    hasScrolledToBottom = false;
    $("#btnAgree").attr("disabled", true);
    $("#modalTerms .modal-body").scrollTop(0);
  });

  $("#btnAgree").on("click", function() {
    toggleModal('hide');
    $("#iAgree").prop("checked", true);
    $("#btnTerms").removeAttr("disabled");
  });
</script>

</html>