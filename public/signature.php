<?php
include(__DIR__ . "/../backend/nodes.php");
$pageTitle = "Create Signature";

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

if ($sentTemplate->expired == 1 || $sentTemplate->is_void == 1) {
  http_response_code(403);
  header("Location: " . SERVER_NAME . "/error/403");
  exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php include("../components/system/head.php") ?>
  <link href="https://fonts.cdnfonts.com/css/writing-signature" rel="stylesheet">
  <style>
    .signature-pad--body {
      position: relative;
      -webkit-box-flex: 1;
      -ms-flex: 1;
      flex: 1;
    }

    .signature-pad {
      font-size: 10px;
      width: 100%;
      height: 100%;
      max-height: 460px;
      border: 1px solid let(--bg-border);
      background-color: #fff;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.27), 0 0 40px rgba(0, 0, 0, 0.08) inset;
      border-radius: 4px;
      padding: 10px;
    }

    .signature-pad--body canvas {
      width: 100%;
      height: 100%;
      border-radius: 4px;
      box-shadow: 0 0 5px rgb(0 0 0 / 22%) inset;
      border: 1px solid #a7a3a3b8;
    }
  </style>
</head>

<body>
  <div class="wrapper d-flex flex-column min-vh-100">
    <div class="body flex-grow-1">
      <div class="container-fluid py-5 position-relative">
        <div class="row justify-content-center align-content-center" style="height: 90vh;">
          <div class="col-md-8 col-lg-6">
            <div class="card w-100">
              <div class="card-header">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                  <h5 class="card-title mb-0">
                    <?= (isset($_GET["edit_signature"]) && $_GET["edit_signature"] == "edit") ? "Update" : "Create" ?> Signature
                  </h5>
                  <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" onclick="toggleSignatureMode('type')" id="btnType">
                      <i class="bi bi-pencil me-1"></i>
                      Type
                    </button>
                    <button type="button" class="btn btn-primary d-none" onclick="toggleSignatureMode('draw')" id="btnDraw">
                      <i class="bi bi-brush me-1"></i>
                      Draw
                    </button>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div id="confirmSignature" class="d-none alert alert-info py-2 px-3 mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2" role="alert" style="font-size: 0.95rem; color: #0c5460;">
                  <span class="d-flex align-items-center flex-grow-1" style="min-width:0;">
                    <i class="bi bi-question-circle-fill me-1"></i>
                    <span class="text-truncate" style="white-space:nowrap;">Are you sure you want to use this signature?</span>
                  </span>
                  <span class="d-flex flex-shrink-0 gap-2">
                    <button type="button" class="btn btn-success btn-sm" onclick="handleYesSignature()">
                      <i class="bi bi-check-circle me-1"></i> Yes
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="handleEditSignature()">
                      <i class="bi bi-pencil-square me-1"></i> Edit
                    </button>
                  </span>
                </div>
                <div id="signPadContainer">
                  <div class="row">
                    <div class="col-md-8 mb-3">
                      <div class="signature-pad">
                        <div class="title">
                          <h6>Primary</h6>
                        </div>
                        <div class="signature-pad--body">
                          <canvas id="signPadPrimary" style="touch-action: none; width: 100%; height: 180px; min-height: 120px;"></canvas>
                        </div>
                        <div class="mt-2">
                          <button type="button" class="btn btn-primary m-1" onclick="handleClear('signPadPrimary')">
                            <i class="bi bi-eraser me-1"></i>
                            Clear
                          </button>
                          <button type="button" class="btn btn-primary m-1" onclick="handleUndo('signPadPrimary')">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Undo
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 mb-3">
                      <div class="signature-pad">
                        <div class="title">
                          <h6>Initial</h6>
                        </div>
                        <div class="signature-pad--body">
                          <canvas id="signPadInitial" style="touch-action: none; width: 100%; height: 180px; min-height: 120px;"></canvas>
                        </div>
                        <div class="mt-2">
                          <button type="button" class="btn btn-primary m-1" onclick="handleClear('signPadInitial')">
                            <i class="bi bi-eraser me-1"></i>
                            Clear
                          </button>
                          <button type="button" class="btn btn-primary m-1" onclick="handleUndo('signPadInitial')">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Undo
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="typePadContainer" class="d-none">
                  <div class="row">
                    <div class="col-md-8 mb-3">
                      <div class="signature-pad">
                        <div class="title">
                          <h6>Primary</h6>
                        </div>
                        <div class="signature-pad--body" style="height: 180px;">
                          <canvas id="typePadPrimary" style="width:100%; height:180px;"></canvas>
                        </div>
                        <input type="text" class="form-control mt-3" id="signatureText" placeholder="Type primary signature here...">
                      </div>
                    </div>
                    <div class="col-md-4 mb-3">
                      <div class="signature-pad">
                        <div class="title">
                          <h6>Initial</h6>
                        </div>
                        <div class="signature-pad--body" style="height: 180px;">
                          <canvas id="typePadInitial" style="width:100%; height:180px;"></canvas>
                        </div>
                        <input type="text" class="form-control mt-3" id="signatureTextInitial" placeholder="Type initial signature here...">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer text-end">
                <button type="button" class="btn btn-success" id="saveSignature" onclick="submitSignature()">
                  <i class="bi bi-check2 me-1"></i>
                  Save Signature
                </button>
                <?php if (isset($_GET["edit_signature"])) : ?>
                  <button type="button" class="btn btn-danger" onclick="window.location.replace(`<?= SERVER_NAME . '/public/signing?' . http_build_query($_GET) ?>`)">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancel
                  </button>
                <?php else: ?>
                  <button type="button" class="btn btn-secondary" id="btnSkip" onclick='window.location.replace(`<?= SERVER_NAME . "/public/viewing?" . http_build_query($_GET) . "&skip" ?>`)'>
                    <i class="bi bi-arrow-right-circle me-1"></i>
                    Skip
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- <input type="hidden" id="templateId" value="<?= $template->id ?>"> -->
  <?php include(__DIR__ . "/../components/loading-screen.php") ?>
</body>

<?php include("../components/system/scripts.php") ?>
<script src="<?= SERVER_NAME . "/assets/js/signature_pad.js" ?>"></script>
<script>
  let signPadPrimary = new SignaturePad($("#signPadPrimary").get(0), {
    backgroundColor: 'transparent'
  });

  let signPadInitial = new SignaturePad($("#signPadInitial").get(0), {
    backgroundColor: 'transparent'
  });

  $(document).ready(function() {
    resizeCanvases()
  });

  $(window).on("resize", function() {
    resizeCanvases()
  });

  function handleYesSignature() {
    handleSubmitSignature();
  }

  function handleEditSignature() {
    $("#confirmSignature").addClass("d-none");

    if (!$("#typePadContainer").hasClass("d-none")) {
      $("#signatureText").attr('readonly', false);
      $("#signatureTextInitial").attr('readonly', false);
    } else {
      signPadPrimary.on();
      signPadInitial.on();
    }

    $(".signature-pad button").prop("disabled", false);
    $("#saveSignature").prop("disabled", false);
  }

  function submitSignature() {
    const isTypeMode = !$("#typePadContainer").hasClass("d-none");

    const signatureText = $("#signatureText")
    const signatureTextInitial = $("#signatureTextInitial")

    if (
      (!isTypeMode && (signPadPrimary.isEmpty() || signPadInitial.isEmpty())) ||
      (isTypeMode && (!signatureText.val().trim() || !signatureTextInitial.val().trim()))
    ) {
      swal.fire({
        icon: "error",
        title: "Error",
        text: `Please ${isTypeMode ? "type" : "draw"} your signature (primary and initial).`
      });
      return;

    } else {
      if (isTypeMode) {
        $("#signatureText").attr('readonly', true);
        $("#signatureTextInitial").attr('readonly', true);
      } else {
        signPadPrimary.off();
        signPadInitial.off();

        $(".signature-pad button").prop("disabled", true);
      }


      $("#saveSignature").prop("disabled", true);
      $("#confirmSignature").removeClass("d-none");
    }
  }

  function handleSubmitSignature() {
    showBtnLoading($("#saveSignature"));

    const searchParams = new URLSearchParams(window.location.search);
    const templateId = searchParams.get("t");
    const signingId = searchParams.get("s");
    const customerTemplateId = searchParams.get("c");
    const method = searchParams.get("page");

    let formData = new FormData();
    formData.append("action", "save_signature");
    formData.append("template_id", templateId);
    formData.append("signing_id", signingId);
    formData.append("customer_template_id", customerTemplateId);
    formData.append("page", method);

    const isTypeMode = !$("#typePadContainer").hasClass("d-none");
    if (isTypeMode) {
      formData.append("primary_signature", dataURLToBlob($("#typePadPrimary").get(0).toDataURL("image/png")));
      formData.append("initial_signature", dataURLToBlob($("#typePadInitial").get(0).toDataURL("image/png")));
    } else {
      formData.append("primary_signature", dataURLToBlob(signPadPrimary.toDataURL()));
      formData.append("initial_signature", dataURLToBlob(signPadInitial.toDataURL()));
    }

    $.ajax({
      url: "<?= SERVER_NAME . "/backend/nodes" ?>",
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      cache: false,
      enctype: 'multipart/form-data',
      dataType: 'JSON',
      success: function(response) {
        showBtnLoading($("#saveSignature"), false);
        if (response.success) {
          swal.fire({
            icon: 'success',
            title: 'Success',
            text: response.message
          }).then(() =>
            window.location.replace(`<?= SERVER_NAME ?>/public/signing?s=${signingId}&t=${templateId}&c=${customerTemplateId}`)
          );
        } else {
          showBtnLoading($("#saveSignature"), false);
          swal.fire({
            icon: 'error',
            title: 'Error',
            text: response.message
          });
        }
      },
      error: function(xhr, status, error) {
        showBtnLoading($("#saveSignature"), false);
        swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'An error occurred while processing your request.',
        });
      }
    });
  }

  function toggleSignatureMode(mode) {
    let signPadContainer = $("#signPadContainer");
    let typePadContainer = $("#typePadContainer");
    let btnType = $("#btnType");
    let btnDraw = $("#btnDraw");
    let confirmSignature = $("#confirmSignature");

    if (mode === "type") {
      $("#signatureText").attr('readonly', false);
      $("#signatureTextInitial").attr('readonly', false);

      btnType.addClass("d-none");
      btnDraw.removeClass("d-none");
      signPadContainer.addClass("d-none");
      typePadContainer.removeClass("d-none");
      confirmSignature.addClass("d-none");

      $(".signature-pad button").prop("disabled", false);
      $("#saveSignature").prop("disabled", false);

      resizeCanvas("typePadPrimary");
      resizeCanvas("typePadInitial");

      handleClear("signPadPrimary");
      handleClear("signPadInitial");
    } else if (mode === "draw") {
      signPadPrimary.on();
      signPadInitial.on();

      btnType.removeClass("d-none");
      btnDraw.addClass("d-none");
      signPadContainer.removeClass("d-none");
      typePadContainer.addClass("d-none");
      confirmSignature.addClass("d-none");

      $(".signature-pad button").prop("disabled", false);
      $("#saveSignature").prop("disabled", false);

      resizeCanvas("signPadPrimary");
      resizeCanvas("signPadInitial");

      $("#signatureText").val('');
      $("#signatureTextInitial").val('');
    }
  }

  function drawTypedSignature(canvasId, text) {
    const canvas = $(`#${canvasId}`).get(0);
    const ctx = canvas.getContext('2d');
    const parent = $(canvas).parent();

    const width = parent.width();
    const height = 180;
    const ratio = window.devicePixelRatio || 1;

    canvas.width = width * ratio;
    canvas.height = height * ratio;
    canvas.style.width = width + "px";
    canvas.style.height = height + "px";
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.scale(ratio, ratio);

    ctx.clearRect(0, 0, width, height);

    let fontSize = Math.max(height * 0.45, 48);
    let lines = [];
    let lineHeight = fontSize * 1.2;

    do {
      ctx.font = `${fontSize}px 'Writing Signature', cursive`;
      lines = getWrappedLines(ctx, text, width * 0.9);
      lineHeight = fontSize * 1.2;
      if (lines.length * lineHeight > height * 0.9) {
        fontSize -= 2;
      } else {
        break;
      }
    } while (fontSize > 16);

    ctx.font = `${fontSize}px 'Writing Signature', cursive`;
    ctx.textAlign = "center";
    ctx.textBaseline = "alphabetic";
    ctx.fillStyle = "#000";
    ctx.backgroundColor = "transparent";

    const totalHeight = lines.length * lineHeight;
    let startY = (height - totalHeight) / 2 + lineHeight * 0.8;
    for (let i = 0; i < lines.length; i++) {
      ctx.fillText(lines[i], width / 2, startY + i * lineHeight);
    }
  }

  function getWrappedLines(context, text, maxWidth) {
    const words = text.split(' ');
    let line = '';
    let lines = [];
    for (let n = 0; n < words.length; n++) {
      let testLine = line + words[n] + ' ';
      let metrics = context.measureText(testLine);
      let testWidth = metrics.width;
      if (testWidth > maxWidth && n > 0) {
        lines.push(line.trim());
        line = words[n] + ' ';
      } else {
        line = testLine;
      }
    }
    lines.push(line.trim());
    return lines;
  }

  $("#signatureText, #signatureTextInitial").on("input", function() {
    if (this.id === "signatureText") {
      drawTypedSignature("typePadPrimary", this.value);
    } else if (this.id === "signatureTextInitial") {
      drawTypedSignature("typePadInitial", this.value);
    }
  });

  function resizeCanvases() {
    if ($("#signPadPrimary").is(":visible")) {
      resizeCanvas("signPadPrimary");
      resizeCanvas("signPadInitial");
    } else if ($("#typePadContainer").is(":visible")) {
      resizeCanvas("typePadPrimary");
      resizeCanvas("typePadInitial");
    }
  }

  function resizeCanvas(canvasId) {
    const $canvas = $(`#${canvasId}`);
    const $parent = $canvas.parent();

    const ratio = window.devicePixelRatio || 1;
    const displayWidth = $parent.width();
    const displayHeight = 180;

    $canvas.prop("width", displayWidth * ratio);
    $canvas.prop("height", displayHeight * ratio);
    $canvas.css("width", `#${displayWidth}px`);
    $canvas.css("height", `#${displayHeight}px`);

    const ctx = $canvas.get(0).getContext('2d');
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.scale(ratio, ratio);

    if (canvasId === "typePadPrimary") {
      drawTypedSignature(canvasId, $("#signatureText").val());
    } else if (canvasId === "typePadInitial") {
      drawTypedSignature(canvasId, $("#signatureTextInitial").val());
    }
  }

  function dataURLToBlob(dataURL) {
    let byteString;
    if (dataURL.split(',')[0].indexOf('base64') >= 0)
      byteString = atob(dataURL.split(',')[1]);
    else
      byteString = unescape(dataURL.split(',')[1]);

    let mimeString = dataURL.split(',')[0].split(':')[1].split(';')[0];

    let ia = new Uint8Array(byteString.length);
    for (let i = 0; i < byteString.length; i++) {
      ia[i] = byteString.charCodeAt(i);
    }

    return new Blob([ia], {
      type: mimeString
    });

  }

  function handleClear(canvasId) {
    if (canvasId === "signPadPrimary") {
      signPadPrimary.clear();
    } else if (canvasId === "signPadInitial") {
      signPadInitial.clear();
    }
  }

  function handleUndo(canvasId) {
    let pad = null;

    if (canvasId === "signPadPrimary") {
      pad = signPadPrimary;
    } else if (canvasId === "signPadInitial") {
      pad = signPadInitial
    }

    if (pad) {
      let data = pad.toData();

      if (data) {
        data.pop();
        pad.fromData(data);
      }
    }
  }
</script>

</html>