<?php $templateConfig = $template->signature_config ? ($template->signature_config) : ""; ?>
<div class="container-fluid position-relative">
  <div class="card">
    <form method="POST" id="formUseTemplate" novalidate>
      <input type="hidden" name="id" value="<?= $id ?>">
      <input type="hidden" name="sender_id" value="<?= $current_user->id ?>">
      <input type="hidden" name="sender_name" value="<?= $current_user->full_name ?>">
      <input type="hidden" id="is_void" value="0">

      <div class="card-header">
        <h5 class="card-title">
          <?= $template->name ?>
        </h5>
      </div>
      <div class="card-body" id="formUseTemplateBody">
        <div class="row">
          <div class="col-md-6" id="signatureEmailConfig">
            <?php
            $hasFirstSigner = false;
            $hasSecondSigner = false;
            $hasThirdSigner = false;

            if (!empty($templateConfig)) {
              $signConfig = json_decode($templateConfig, true);
              if (count($signConfig) > 0) {
                foreach ($signConfig as $config) {
                  if ($hasFirstSigner && $hasSecondSigner && $hasThirdSigner) {
                    break;
                  } else if (!$hasFirstSigner && $config["signer"] == "firstSigner") {
                    $hasFirstSigner = true;
                  } elseif (!$hasSecondSigner && $config["signer"] == "secondSigner") {
                    $hasSecondSigner = true;
                  } elseif (!$hasThirdSigner && $config["signer"] == "thirdSigner") {
                    $hasThirdSigner = true;
                  }
                }
              }
            }

            $hasNoSigner = !$hasFirstSigner && !$hasSecondSigner && !$hasThirdSigner;
            ?>
            <div class="px-2">
              <?php if ($hasNoSigner): ?>
                <div class="alert alert-warning text-center" role="alert">
                  <strong>No signer is set for this template.</strong><br>
                  Please configure at least one signer in the template settings before using.
                </div>
              <?php endif; ?>
              <?php if ($hasFirstSigner): ?>
                <div class="row mb-3">
                  <h4>Signature/ Initials 1:</h4>
                  <div class="col-md-12 px-lg-5 mb-2">
                    <label for="firstSignerName" class="form-label">Name</label>
                    <input type="text" class="form-control" id="firstSignerName" name="first_signer_name" required>
                  </div>
                  <div class="col-md-12 px-lg-5 mb-2">
                    <label for="firstSignerEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="firstSignerEmail" name="first_signer_email" required>
                  </div>
                </div>
                <hr>
              <?php endif; ?>
              <?php if ($hasSecondSigner): ?>
                <div class="row mb-3">
                  <h4>Signature/ Initials 2:</h4>
                  <div class="col-md-12 px-lg-5 mb-2">
                    <label for="secondSignerName" class="form-label">Name</label>
                    <input type="text" class="form-control" id="secondSignerName" name="second_signer_name" required>
                  </div>
                  <div class="col-md-12 px-lg-5 mb-2">
                    <label for="secondSignerEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="secondSignerEmail" name="second_signer_email" required>
                  </div>
                </div>
                <hr>
              <?php endif; ?>
              <?php if ($hasThirdSigner): ?>
                <div class="row mb-3">
                  <h4>Signature/ Initials 3:</h4>
                  <div class="col-md-12 px-lg-5 mb-2">
                    <label for="thirdSignerName" class="form-label">Name</label>
                    <input type="text" class="form-control" id="thirdSignerName" name="third_signer_name" required>
                  </div>
                  <div class="col-md-12 px-lg-5 mb-2">
                    <label for="thirdSignerEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="thirdSignerEmail" name="third_signer_email" required>
                  </div>
                </div>
                <hr>
              <?php endif; ?>
              <div class="row mb-3">
                <h4>Add Message</h4>
                <div class="col-md-12 px-lg-5 mb-2">
                  <label for="emailSubject" class="form-label">Email Subject</label>
                  <input type="text" name="email_subject" id="emailSubject" class="form-control" value="<?= $template->email_subject ?>" maxlength="100" required>
                  <small class="text-muted">Remaining Characters: <span id="remainingChars"><?= 100 - strlen($template->email_subject) ?></span></small>
                </div>
                <div class="col-md-12 px-lg-5 mb-2">
                  <label for="emailMessage" class="form-label">Email Message</label>
                  <textarea name="email_message" id="emailMessage" class="form-control" rows="10" maxlength="1000"><?= htmlentities($template->email_message) ?></textarea>
                  <small class="text-muted">Remaining Characters: <span id="remainingCharsMessage"><?= 1000 - strlen($template->email_message) ?></span></small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 p-0 position-relative" id="pdfViewerContainer" style="min-height: 70vh;">
            <a href="<?= SERVER_NAME . "/views/templates?page=view&id=$id" ?>" class="btn btn-secondary position-absolute top-0 end-0 m-2 z-1" title="Fullscreen">
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

      </div>
      <div class="card-footer text-end">
        <button type="submit" id="btnSend" class="btn btn-primary" <?= $hasNoSigner ? 'disabled' : '' ?>>
          <i class="bi bi-send"></i>
          <span id="btnTxt">Send</span>
        </button>

        <a href="<?= SERVER_NAME . "/views/templates" ?>" class="btn btn-danger">
          <i class="bi bi-x-circle"></i>
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>
<input type="hidden" id="templateId" value="<?= $template->id ?>">
<input type="hidden" id="docId" value="<?= $template->doc_id ?>">

<script>
  $("#emailSubject").on("input", function() {
    const maxLength = parseInt($(this).attr("maxlength"));
    const currentLength = $(this).val().length;
    const remainingChars = maxLength - currentLength;
    $("#remainingChars").text(remainingChars);
  });

  $("#emailMessage").on("input", function() {
    const maxLength = parseInt($(this).attr("maxlength"));
    const currentLength = $(this).val().length;
    const remainingChars = maxLength - currentLength;
    $("#remainingCharsMessage").text(remainingChars);
  });

  var templateId = $("#templateId").val();
  var docId = $("#docId").val();

  var pdfDoc = null,
    scale = 1.5,
    containerScale = 1;
  var textBoxData = [];
  var selectedTextBox = null;

  var templateConfigArr = [];

  $.validator.addMethod("uniqueEmail", function(value, element) {
    const emails = [
      $("#firstSignerEmail").val(),
      $("#secondSignerEmail").val(),
      $("#thirdSignerEmail").val()
    ].filter(Boolean);

    return emails.filter(email => email === value).length === 1;
  }, "Email addresses must be unique.");

  $("#formUseTemplate").validate({
    submitHandler: function() {
      const formData = new FormData($("#formUseTemplate").get(0));
      formData.append("action", "send_use_template");

      showBtnLoading($("#btnSend"));
      $("#btnTxt").text("Sending...");
      $.ajax({
        url: "<?= SERVER_NAME . "/backend/nodes" ?>",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        enctype: "multipart/form-data",
        cache: false,
        dataType: "JSON",
        success: function(res) {
          if (res.success) {
            showBtnLoading($("#btnSend"), false);
            $("#btnTxt").text("Send");

            swal.fire({
              title: "Successfully Sent!",
              html: "Please take note emails arrive within 5 minutes depending on how large document is. Recipients may or may not need to check their Email's Junk/Spam folder.",
              icon: "success",
            }).then(() => {
              window.location.href = "<?= SERVER_NAME . "/views/templates" ?>";
            });

          } else {
            swal.fire({
              title: "Error",
              html: res.message,
              icon: "error",
            })
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          showBtnLoading($("#btnSend"), false);
          $("#btnTxt").text("Send");

          swal.fire({
            title: "Error",
            html: `Error: ${textStatus} <br> ${errorThrown}`,
            icon: "error",
          })
        }
      });
    },
    rules: {
      first_signer_email: {
        required: function() {
          return $("#firstSignerEmail").length > 0;
        },
        email: true,
        uniqueEmail: true
      },
      second_signer_email: {
        required: function() {
          return $("#secondSignerEmail").length > 0;
        },
        email: true,
        uniqueEmail: true
      },
      third_signer_email: {
        required: function() {
          return $("#thirdSignerEmail").length > 0;
        },
        email: true,
        uniqueEmail: true
      }
    },
    errorElement: "span",
    errorPlacement: function(error, element) {
      error.addClass("invalid-feedback").insertAfter(element)
    },
    highlight: function(element) {
      $(element).removeClass('is-valid').addClass('is-invalid');
    },
    unhighlight: function(element) {
      $(element).removeClass('is-invalid');
    }
  });

  var templateConfig = null;
  var pdfFiles = [];

  $(document).ready(async function() {
    $("#pdfViewerContainer").height($("#signatureEmailConfig").height());

    $("#pdfLoading").addClass("d-flex").removeClass("d-none");
    $("#pdfContainer").addClass("d-none");

    await $.post("<?= SERVER_NAME . "/backend/nodes" ?>", {
      action: "get_template_config",
      template_id: templateId,
    }, function(data) {
      if (data.success && data.config) {
        templateConfig = data.config.sort((a, b) => a.pageNumber - b.pageNumber);
        templateConfigArr = templateConfig ? templateConfig : templateConfigArr;
      }

      if (data.success && data.text_config) {
        textBoxData = data.text_config.sort((a, b) => a.pageNumber - b.pageNumber);
      }

      if (data.success && data.pdf_files) {
        pdfFiles = data.pdf_files;
      }
    }, "json");

    const toolbarContainer = $('#signature-toolbar-container');
    const toolbar = $('#signature-toolbar').clone();
    $('#signature-toolbar').remove();

    toolbarContainer.append(toolbar.removeClass('d-none'));

    let pdfDocs = [];
    let loadPromises = pdfFiles.map(path => pdfjsLib.getDocument(path).promise);

    Promise.all(loadPromises)
      .then(async (docs) => {
        pdfDocs = docs;
        pdfDoc = pdfDocs;
        await renderAllPages(pdfDocs);
      })
      .catch(err => {
        console.error("Error loading PDFs:", err);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to load one or more PDF files.',
        });
      })
      .finally(() => {
        $("#pdfLoading").addClass("d-none").removeClass("d-flex");
        $("#pdfContainer").removeClass("d-none");
      });
  });
</script>
<?php require(__DIR__ . "/../../../components/signatures/signature-view-scripts.php") ?>