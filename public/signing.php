<?php
include(__DIR__ . "/../backend/nodes.php");
$pageTitle = "Signatures";

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

if ($sentTemplate && $sentTemplate->signed_finished_date) {
  header("Location: " . SERVER_NAME . "/public/?s=$_GET[s]&t=$_GET[t]&c=$_GET[c]");
  exit();
}

$signer = "";
if ($sentTemplate->email_order == 1) {
  $signer = "firstSigner";
} else if ($sentTemplate->email_order == 2) {
  $signer = "secondSigner";
} else if ($sentTemplate->email_order == 3) {
  $signer = "thirdSigner";
}

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
<!DOCTYPE html>
<html lang="en">

<head>
  <?php include("../components/system/head.php") ?>
</head>

<body>
  <div class="wrapper d-flex flex-column">
    <?php include("./components/header.php") ?>

    <input type="hidden" id="templateId" value="<?= $template_id ?>">
    <input type="hidden" id="sent_template_id" value="<?= $customer ?>">
    <input type="hidden" id="signing_id" value="<?= $signing_id ?>">
    <input type="hidden" id="docId" value="<?= $template->doc_id ?>">
    <input type="hidden" id="signer" value="<?= $signer ?>">

    <input type="hidden" id="firstSignerFullName" value='<?= $signerLabel["firstSigner"]["full_name"] ?? "" ?>'>
    <input type="hidden" id="firstSignerEmail" value='<?= $signerLabel["firstSigner"]["email"] ?? "" ?>'>

    <input type="hidden" id="secondSignerFullName" value='<?= $signerLabel["secondSigner"]["full_name"] ?? "" ?>'>
    <input type="hidden" id="secondSignerEmail" value='<?= $signerLabel["secondSigner"]["email"] ?? "" ?>'>

    <input type="hidden" id="thirdSignerFullName" value='<?= $signerLabel["thirdSigner"]["full_name"] ?? "" ?>'>
    <input type="hidden" id="thirdSignerEmail" value='<?= $signerLabel["thirdSigner"]["email"] ?? "" ?>'>

    <input type="hidden" id="is_void" value="<?= $sentTemplate->is_void ?? "0" ?>">

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

<script>
  var textBoxPlaceholder = "";
  const date = moment();
  const currentDate = date.format('MM/DD/YYYY HH:mm:ss');

  var templateId = $("#templateId").val();
  var sent_template_id = $("#sent_template_id").val();
  var signing_id = $("#signing_id").val();
  var docId = $("#docId").val();
  var signer = $("#signer").val();

  let pdfDoc = null,
    scale = 1.5,
    containerScale = 1;
  let textBoxData = [];
  let selectedTextBox = null;

  var templateDataArr = [];
  var templateConfig = null;
  var pdfFiles = [];

  var signedData = [];
  var signatures = [];

  function displaySignature(divId, type, selectedSigner) {
    let el = $(`#${divId}`);
    let selectedSign = signedData.find((d) => d.signId === divId && d.type === type && d.signer === selectedSigner);
    let signatureSign = signatures.find((s) => s.signer === selectedSign.signer);

    if (selectedSign.type === "initial") {
      el.html(`
      <div class="position-relative w-100 h-100 d-flex justify-content-center align-items-center">
        <img class="position-absolute" src="<?= SERVER_NAME ?>/uploads/signatures/${signatureSign.initial_signature}" style="width: 100px; top: -15px; left: 10px; cursor: not-allowed;" />
      </div>`);
    } else {
      el.html(`
      <div class="position-relative w-100 h-100 d-flex justify-content-center align-items-center">
        <img src="<?= SERVER_NAME ?>/uploads/signatures/${signatureSign.primary_signature}" style="width: 250px; margin-top: -15px; cursor: not-allowed;"/>
        <span class="text-danger position-absolute" style="bottom: 0; left: 0; font-size: 10px">SignID: ${signatureSign.signature_token}</span>
      </div>`);
    }

    if (selectedSign.includeDate) {
      const date = selectedSign.dateSigned ? selectedSign.dateSigned : currentDate;
      const formattedDate = moment(new Date(date)).format('MM/DD/YYYY');
      $(`#${selectedSign.spanDateId}`).html(`<span class="fw-bold" style="font-size: 14px">${formattedDate}</span>`);
    }
  }

  async function handleToggleSign(divId, type, selectedSigner) {
    if (signedData.some((d) => d.signId === divId && d.type === type && d.signer === selectedSigner)) {
      let signatureSign = signatures.find((s) => s.signer === selectedSigner);

      if (!signatureSign.primary_signature || !signatureSign.initial_signature) {
        toastr.error('Primary or initial signature is not set.<br>Please set your signature first.');
        return;
      } else {
        displaySignature(divId, type, selectedSigner);

        signedData = signedData.map((d) => {
          if (d.signId === divId && d.type === type && d.signer === selectedSigner) {
            return {
              ...d,
              isSigned: true,
              dateSigned: currentDate
            };
          }
          return d;
        });

        const saveSignature = await handleSaveSignature(signedData);
        if (saveSignature.success) {
          if (signedData.filter((d) => d.isSigned && d.signer === signer).length < signedData.filter((d) => d.signer === signer).length) {
            scrollToNext(divId);
          } else {
            swal.fire({
                title: "Signing Complete!",
                icon: "success",
                confirmButtonText: '<i class="fa fa-paper-plane"></i> Submit',
                confirmButtonColor: "#0d6efd",
                cancelButtonText: '<i class="fa fa-times"></i> Cancel',
                showCancelButton: true,
                showLoaderOnConfirm: true,
                preConfirm: async (value) => {
                  try {
                    isPageLoading = false;
                    const res = await handleSubmitSignature();

                    return res;
                  } catch (error) {
                    return {
                      success: false,
                      message: "An error occurred while saving the signature."
                    };
                  }
                },
                allowOutsideClick: () => !Swal.isLoading(),
              })
              .then((d) => {
                if (d.isConfirmed) {
                  swal.fire({
                    title: d.value.success ? "Success" : "Error",
                    html: d.value.message,
                    icon: d.value.success ? "success" : "error",
                  });
                } else {
                  $("#btnFinish").removeClass("d-none");
                }
              });
          }
        } else {
          toastr.error(`Error saving signature: ${saveSignature.message}`);
        }

      }
    }
  }

  $(document).ready(async function() {
    $("#pdfLoading").addClass("d-flex").removeClass("d-none");
    $("#pdfContainer").addClass("d-none");

    await $.post("<?= SERVER_NAME . "/backend/nodes" ?>", {
      action: "get_template_config",
      template_id: templateId,
    }, function(data) {
      if (data.success && data.config) {
        templateConfig = data.config.sort((a, b) => a.pageNumber - b.pageNumber);
        templateDataArr = templateConfig ? templateConfig : templateDataArr;
      }

      if (data.success && data.text_config) {
        textBoxData = data.text_config.sort((a, b) => a.pageNumber - b.pageNumber);
      }

      if (data.success && data.pdf_files) {
        pdfFiles = data.pdf_files;
      }
    }, "json");

    await $.post("<?= SERVER_NAME . "/backend/nodes" ?>", {
      action: "get_signed_data",
      template_id: templateId,
      signing_id: signing_id
    }, function(response) {
      if (response.signatures) {
        signatures = response.signatures;
      }

      if (response.data && response.data.length > 0) {
        const resData = response.data;

        signedData = resData.map(item => ({
          ...item,
          isSigned: item.isSigned,
          dateSigned: item.dateSigned,
          signerId: !item.signerId && item.signer === signer ? sent_template_id : item.signerId,
        }));
      } else {
        signedData = templateDataArr.map(item => ({
          ...item,
          isSigned: false,
          dateSigned: null,
          signerId: item.signer === signer ? sent_template_id : null,
        }));
      }
    }, "json");

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

        if (signedData.filter((d) => d.isSigned && d.signer === signer).length === signedData.filter((d) => d.signer === signer).length) {
          $("#btnFinish").removeClass("d-none");
        } else {
          $("#btnFinish").addClass("d-none");
        }
      });
  });

  function showHeaderSaving() {
    $("#savingMessage").removeClass("d-none")
    setTimeout(function() {
      $("#savingMessage").addClass("d-none")
    }, 1000);
  }

  function populateButton(type, selectedSigner, includeDate, pageNumber, signId, spanDateId) {
    const el = $(`#pageNumber_${pageNumber}`);

    const btnSign = formatSignButton(selectedSigner, signId, type);

    const signConfig =
      templateDataArr.some((d) => d.pageNumber === pageNumber && d.signId === signId) ?
      templateDataArr.filter((d) => d.pageNumber === pageNumber && d.signId === signId)[0] : null;

    $(el).prepend(btnSign).find(`#${signId}`).css({
      top: signConfig && signConfig.signPlacement ?
        signConfig.signPlacement.signTop : parseInt(($(el).height() - $(`#${signId}`).outerHeight()) / 2),
      left: signConfig && signConfig.signPlacement ?
        signConfig.signPlacement.signLeft : parseInt(($(el).width() - $(`#${signId}`).outerWidth()) / 2)
    });

    if (includeDate) {
      const spanDate = formatSpanDate(spanDateId, type, selectedSigner);

      $(el).prepend(spanDate).find(`#${spanDateId}`).css({
        top: signConfig && signConfig.datePlacement ?
          signConfig.datePlacement.dateTop : parseInt(($(el).height() - $(`#${spanDateId}`).outerHeight()) / 2),
        left: signConfig && signConfig.datePlacement ?
          signConfig.datePlacement.dateLeft : parseInt(($(el).width() - $(`#${spanDateId}`).outerWidth()) / 2) + 220
      });
    }
  }

  function formatSignButton(selectedSigner, divId, type) {
    const noButton = signedData.some((d) => d.signId === divId && d.type === type && d.signer === signer);

    if (type === "initial") {
      return (`
      <div class="btnSign text-center p-1 viewing-btn" id="${divId}" style="width: 150px" data-signer-type="initial" data-signer="${signer}">
      ${noButton ? 
        `<button class="btn btn-link btn-sm px-2" onclick="handleToggleSign('${divId}', '${type}', '${signer}')">
          ____<span class="text-decoration-underline">Sign</span>____
        </button>` : ""}
      </div>`);
    }

    return (`
      <div class="btnSign text-center viewing-btn" id="${divId}" data-signer-type="primary" data-signer="${signer}">
      ${noButton ? 
        `<button class="btn btn-primary btn-sm px-4" onclick="handleToggleSign('${divId}', '${type}', '${signer}')">
          <span>Sign</span>
        </button>` : ""}
      </div>`);
  }

  function formatSpanDate(divId, type, signer, signId) {
    return (`<div class="spanDate text-center pb-0 viewing-btn" id="${divId}" data-signer-type="${type}" data-signer="${signer}"></div>`);
  }

  function isCanvasEmpty(canvas) {
    const context = canvas.getContext('2d');
    const pixelData = context.getImageData(0, 0, canvas.width, canvas.height).data;

    for (let i = 0; i < pixelData.length; i += 4) {
      if (pixelData[i] !== 255 || pixelData[i + 1] !== 255 || pixelData[i + 2] !== 255 || pixelData[i + 3] !== 255) {
        return false;
      }
    }
    return true;
  }

  function scrollToNext(currentDivSigner, count = 1) {
    let notSignedData = signedData.filter(d => !d.isSigned && d.signer === signer);
    let next = notSignedData[0];

    if (next) {
      const nextEl = $(`#${next.signId}`);
      $("html, body").animate({
          scrollTop: nextEl.offset().top - 500,
        },
        200
      );
    } else {
      toastr.info("No more signatures to scroll.");
    }
  }

  function populateTextToCanvas(textBoxId, type, pageNumber, action, signer = null) {
    const el = $(`#pageNumber_${pageNumber}`);

    let textBoxText = "";

    if (textBoxData.some(tb => tb.id === textBoxId)) {
      const existingTextBox = textBoxData.find(tb => tb.id === textBoxId);
      textBoxText = existingTextBox.text ? existingTextBox.text : textBoxPlaceholder;
    }

    let textBox = formatTextBox(textBoxId, type, textBoxText, signer);

    const defaultCss = {
      top: parseInt(Math.max(0, ($(el).height() - textBox.outerHeight()) / 2)),
      left: parseInt(Math.max(0, ($(el).width() - textBox.outerWidth()) / 2)),
      fontSize: "14px",
      color: "#000",
      fontStyle: "normal",
      fontWeight: "normal",
      position: "absolute",
    }

    $(el).prepend(textBox).find(`#${textBoxId}`).css(
      textBoxData.some(tb => tb.id === textBoxId) ?
      textBoxData.find(tb => tb.id === textBoxId).css :
      defaultCss
    );
  }

  function formatTextBox(textBoxId, type, defaultText, signer = null) {

    const fullName = $(`#${signer}FullName`).val();
    const email = $(`#${signer}Email`).val();

    let text = "";
    if (type === "custom_text") {
      text = convertBreak(defaultText);
    } else if (type === "full_name") {
      text = fullName;
    } else if (type === "email") {
      text = email;
    }
    return $(
      `<div 
        id="${textBoxId}" 
        class="text-box px-2" 
        data-type="${type}"
        >${text}</div>
        `
    );
  }

  function convertBreak(str) {
    if (!str) {
      return '';
    }
    if (typeof str !== 'string') {
      return str;
    } else {
      return str.replace(/\n/g, "<br />")
    }
  }

  async function renderAllPages(pdfDocs) {
    const container = $('#pdfContainer');
    container.empty();

    if (pdfDocs && Array.isArray(pdfDocs)) {
      let globalPageNumber = 1;
      for (const doc of pdfDocs) {
        for (let pageNum = 1; pageNum <= doc.numPages; pageNum++) {
          const page = await doc.getPage(pageNum);
          const viewport = page.getViewport({
            scale
          });

          const pageCanvas = $('<canvas class="rounded"></canvas>')[0];
          const pageCtx = pageCanvas.getContext('2d');
          pageCanvas.height = viewport.height;
          pageCanvas.width = viewport.width;
          pageCanvas.style.border = '1px solid #ccc';

          let renderDisplayWidth = viewport.width;
          const MAX_DISPLAY_WIDTH = 1200;
          let renderDisplayFactor = 1;

          if (viewport.width > MAX_DISPLAY_WIDTH) {
            renderDisplayFactor = MAX_DISPLAY_WIDTH / viewport.width;
          } else if (viewport.width < 600) {
            renderDisplayFactor = 600 / viewport.width;
          }

          renderDisplayWidth = Math.round(viewport.width * renderDisplayFactor);

          renderDisplayWidth = Math.min(renderDisplayWidth, MAX_DISPLAY_WIDTH);
          renderDisplayWidth = Math.max(renderDisplayWidth, 300);

          pageCanvas.style.width = `${renderDisplayWidth}px`;

          await page.render({
            canvasContext: pageCtx,
            viewport: viewport
          }).promise;

          if (!isCanvasEmpty(pageCanvas)) {
            const pageWrapper =
              $(`<div class="page position-relative m-auto mb-3" id="pageNumber_${globalPageNumber}" style="width:${viewport.width}px">
                  <span class='position-absolute bg-white' style='top: .2rem; left: 1rem; font-size: 12px'>
                    PrimeSeal Doc ID: ${docId}
                  </span>
                </div>`);
            pageWrapper.append(pageCanvas);
            if ($("#is_void").val() === "1") {
              pageWrapper.append(`
                <div class='void-box'>
                  <span>Void</span>
                </div>
              `);
            }
            container.append(pageWrapper);

            if (templateConfig && $("#is_void").val() === "0") {
              if (signedData.length) {
                signedData.forEach((signData) => {
                  if (signData.pageNumber === globalPageNumber) {
                    populateButton(signData.type, signData.signer, signData.includeDate, globalPageNumber, signData.signId, signData.spanDateId);
                    if (signData.isSigned) {
                      displaySignature(signData.signId, signData.type, signData.signer);
                    }
                  }
                });
              }
            }
          }
          globalPageNumber++;
        }
      }

      if (textBoxData.length > 0) {
        textBoxData.forEach((textData) => {
          populateTextToCanvas(textData.id, textData.type, textData.pageNumber, "populate", textData.signer);
        });
      }
    }
  }

  async function handleSaveSignature(signConfig) {
    showHeaderSaving();
    return new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "<?= SERVER_NAME . "/backend/nodes" ?>", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
          try {
            const newCsrfToken = xhr.getResponseHeader('X-CSRF-Token');
            if (newCsrfToken) {
              $('meta[name="csrf-token"]').attr('content', newCsrfToken);
            }

            const response = JSON.parse(xhr.responseText);
            resolve({
              success: response.success,
              message: response.message
            });

          } catch (e) {
            resolve({
              success: false,
              message: "An error occurred while saving the signature."
            });
          }
        }
      };
      const strSignedData = encodeURIComponent(JSON.stringify(signedData));
      const strTextBoxData = encodeURIComponent(JSON.stringify(textBoxData));
      const params = `action=save_signed_data&signing_id=${signing_id}&sent_template_id=${sent_template_id}&signed_data=${strSignedData}&template_id=${templateId}&text_box_data=${strTextBoxData}`;
      xhr.onerror = function() {
        reject({
          success: false,
          message: "An error occurred while saving the signature."
        });
      };
      xhr.send(params);
    });
  }
</script>

</html>