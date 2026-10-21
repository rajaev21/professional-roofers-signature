<script>
  var textBoxPlaceholder = "Enter custom text here...";

  function handlePopulateBtnToPage(type, selectedSigner, includeDate) {
    $('.page').each((i, el) => {
      if ($(el).isOnScreen()) {
        const pageNumber = i + 1;
        const signId = `sign_${selectedSigner}_${pageNumber}_${templateConfigArr.filter(d => d.pageNumber === pageNumber).length + 1}`;
        const spanDateId = `date_${selectedSigner}_${pageNumber}_${templateConfigArr.filter(d => d.pageNumber === pageNumber).length + 1}`;

        populateButton(type, selectedSigner, includeDate, pageNumber, signId, spanDateId, "add");
      }
    })
  }

  function populateButton(type, selectedSigner, includeDate, pageNumber, signId, spanDateId, action) {
    const el = $(`#pageNumber_${pageNumber}`);

    const btnSign = formatSignButton(selectedSigner, signId, type);

    const signConfig =
      templateConfigArr.some((d) => d.pageNumber === pageNumber && d.signId === signId) ?
      templateConfigArr.filter((d) => d.pageNumber === pageNumber && d.signId === signId)[0] : null;

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

  function formatSpanDate(divId, type, signer, signId) {
    const spanText = signer === "firstSigner" ? "1st Signer" : signer === "secondSigner" ? "2nd Signer" : "3rd Signer";

    return (`
      <div class="spanDate text-center pb-0 viewing-btn text-dark" id="${divId}" data-signer-type="${type}" data-signer="${signer}">
        <span class="fw-bold text-info" style="position: relative; z-index: 1;">
          <u>_____DATE_____</u>
        </span>
      </div>`);
  }

  function formatSignButton(signer, divId, type) {
    let signerLabel = "1st Signer";
    if (signer == "secondSigner") {
      signerLabel = "2nd Signer"
    } else if (signer == "thirdSigner") {
      signerLabel = "3rd Signer"
    }

    if (type === "initial") {
      return (`
      <div class="btnSign text-center p-1 viewing-btn" id="${divId}" style="width: 150px" data-signer-type="initial" data-signer="${signer}">
        <button type="button" class="btn btn-link btn-sm px-2">
          __<span class="text-decoration-underline">${signerLabel}</span>__
        </button>
      </div>`);
    }

    return (`
      <div class="btnSign text-center viewing-btn" id="${divId}" data-signer-type="primary" data-signer="${signer}">
        <button type="button" class="btn btn-primary btn-sm px-4">
          <span>${signerLabel}</span>
        </button>
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

    } else if (type === "custom_text") {
      textBoxText = textBoxPlaceholder;
    } else if (type === "full_name") {
      textBoxText = `{{{${signer ? `${signer}_` : ""}full_name}}}`;
    } else if (type === "email") {
      textBoxText = `{{{${signer ? `${signer}_` : ""}email}}}`;
    }

    let textBox = formatTextBox(textBoxId, type, textBoxText);

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

  function formatTextBox(textBoxId, type, defaultText) {
    return $(
      `<div 
        id="${textBoxId}" 
        class="text-box px-2" 
        data-type="${type}"
        style="
          background: rgb(0 255 255 / 27%); 
          border: 2px dashed #008b8b; 
          padding: 6px 12px; 
          border-radius: 4px; 
          box-shadow: 0 2px 8px rgba(0,0,0,0.08); 
          ${type === "custom_text" ? "min-width: 10px;" : ""}
          min-height: 37px;
          color: #222; 
          position: absolute; 
          z-index: 999; 
          cursor: move;
        ">${convertBreak(defaultText)}</div>
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

          const pageCanvas = $('<canvas></canvas>')[0];
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
              $(`<div class="page position-relative d-flex justify-content-center mb-3" id="pageNumber_${globalPageNumber}" style="width:${renderDisplayWidth}px">
                  <span class='position-absolute bg-white' style='top: .2rem; left: 1rem; font-size: 12px'>
                      ID: ${docId}
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
              templateConfigArr.forEach((signData) => {
                if (signData.pageNumber === globalPageNumber) {
                  populateButton(signData.type, signData.signer, signData.includeDate, globalPageNumber, signData.signId, signData.spanDateId, "populate");
                }
              });
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
</script>