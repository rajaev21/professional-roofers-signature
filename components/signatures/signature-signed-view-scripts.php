<script>
  var textBoxPlaceholder = "";
  var date = moment();
  var currentDate = date.format('MM/DD/YYYY HH:mm:ss');

  var templateId = $("#templateId").val();
  var signing_id = $("#signing_id").val();
  var docId = $("#docId").val();

  var pdfDoc = null,
    scale = 1.5,
    containerScale = 1;
  var textBoxData = [];
  var selectedTextBox = null;

  var templateDataArr = [];
  var templateConfig = null;
  var pdfFiles = [];

  var signedData = [];
  var signatures = [];

  $(document).ready(async function() {
    if ($("#pdfViewerContainer").length) {
      $("#pdfViewerContainer").height($("#signatureEmailConfig").height());
    }

    $("#pdfLoading").addClass("d-flex").removeClass("d-none");
    $("#pdfContainer").addClass("d-none");

    const searchParams = new URLSearchParams(window.location.search);
    const page = searchParams.get('page');

    await $.post("<?= SERVER_NAME . "/backend/nodes" ?>", {
      action: "get_template_config",
      template_id: $("#templateId").val(),
    }, function(data) {
      if (data.success && data.config) {
        templateConfig = data.config.sort((a, b) => a.pageNumber - b.pageNumber);
        templateDataArr = templateConfig ? templateConfig : templateDataArr;
      }

      // if (data.success && data.text_config) {
      //   textBoxData = data.text_config.sort((a, b) => a.pageNumber - b.pageNumber);
      // }

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

      if (response.text_config && response.text_config.length > 0) {
        textBoxData = response.text_config.sort((a, b) => a.pageNumber - b.pageNumber);
      }

      if (response.data && response.data.length > 0) {
        signedData = response.data;
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
      });

    if (page && page === "fullscreen") {
      const toolbarContainer = $('#signature-toolbar-container');
      const toolbar = $('#signature-toolbar').clone();
      $('#signature-toolbar').remove();

      toolbarContainer.append(toolbar.removeClass('d-none'));
    }
  });

  function populateImageSignature(type, selectedSigner, includeDate, pageNumber, signId, spanDateId) {
    const el = $(`#pageNumber_${pageNumber}`);

    const imgSign = formatImageSignature(selectedSigner, signId, type);

    const signConfig = signedData.find((d) => d.signId === signId && d.type === type && d.signer === selectedSigner);

    if (signConfig.isSigned) {
      $(el).prepend(imgSign).find(`#${signId}`).css({
        top: signConfig.signPlacement.signTop,
        left: signConfig.signPlacement.signLeft
      });

      if (includeDate) {
        const spanDate = formatSpanDate(spanDateId, signId);

        $(el).prepend(spanDate).find(`#${spanDateId}`).css({
          top: signConfig.datePlacement.dateTop,
          left: signConfig.datePlacement.dateLeft
        });
      }
    }
  }

  function formatImageSignature(selectedSigner, divId, type) {
    let selectedSign = signedData.find((d) => d.signId === divId && d.type === type && d.signer === selectedSigner);
    let signatureSign = signatures.find((s) => s.signer === selectedSign.signer);

    if (type === "initial") {
      return (`
      <div class="btnSign text-center p-1 viewing-btn" id="${divId}" style="width: 150px" data-signer-type="initial">
      ${signatureSign.initial_signature ? 
        `<div class="position-relative w-100 h-100 d-flex justify-content-center align-items-center">
            <img class="position-absolute" src="<?= SERVER_NAME ?>/uploads/signatures/${signatureSign.initial_signature}" style="width: 100px; top: -15px; left: 10px; cursor: text;" />
          </div>` : ""}
      </div>`);
    }

    return (`
      <div class="btnSign text-center viewing-btn" id="${divId}" data-signer-type="primary">
      ${signatureSign.primary_signature ? 
        `<div class="position-relative w-100 h-100 d-flex justify-content-center align-items-center">
            <img src="<?= SERVER_NAME ?>/uploads/signatures/${signatureSign.primary_signature}" style="width: 250px; margin-top: -15px; cursor: text;"/>
            <span class="text-danger position-absolute" style="bottom: 0; left: 0; font-size: 10px">SignID: ${signatureSign.signature_token}</span>
          </div>` : ""}
      </div>`);
  }

  function formatSpanDate(divId, signId) {
    const selectedSign = signedData.find((d) => d.signId === signId && d.spanDateId === divId);
    const date = selectedSign && selectedSign.includeDate && selectedSign.dateSigned ? moment(new Date(selectedSign.dateSigned)).format('MM/DD/YYYY') : "";

    return (`
    <div class="spanDate text-center pb-0 viewing-btn text-dark" id="${divId}">
      <span class="fw-bold" style="font-size: 14px">${date}</span>
    </div>`);
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
              $(`<div class="page position-relative m-auto mb-3" id="pageNumber_${globalPageNumber}" style="width:${renderDisplayWidth}px">
                  <span class='position-absolute bg-white' style='top: .2rem; left: 1rem; font-size: 12px'>
                    Professional Roofers Signatures Doc ID: ${docId}
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
            if ($("#expired").val() === "1") {
              pageWrapper.append(`
                <div class='expired-box'>
                  <span>Expired</span>
                </div>
              `);
            }
            container.append(pageWrapper);

            if (templateConfig) {
              if (signedData.length) {
                signedData.forEach((signData) => {
                  if (signData.pageNumber === globalPageNumber) {
                    populateImageSignature(signData.type, signData.signer, signData.includeDate, globalPageNumber, signData.signId, signData.spanDateId);
                  }
                });
              }
            }
          }
          globalPageNumber++;
        }
      }
      if (textBoxData.length > 0) {
        console.log("Populating text boxes...", textBoxData);
        console.log(signedData)
        textBoxData.forEach((textData) => {
          populateTextToCanvas(textData.id, textData.type, textData.pageNumber, "populate", textData.signer);
        });
      }
    }
  }
</script>