<script>
  var textBoxPlaceholder = "Enter custom text here...";

  function handleCustomZoom(action) {
    if (action === 'in') {
      containerScale += 0.1;
    } else if (action === 'out' && containerScale > 0.2) {
      containerScale -= 0.1;
    }
    $('#pdfContainer').css('transform', `scale(${containerScale})`);
    $('#pdfContainer').css('transform-origin', 'top left');
  }

  function handleRemoveSignBtn(el, type) {
    Swal.fire({
      title: 'Remove Button Signature',
      text: 'Are you sure you want to remove this signature button?',
      icon: 'warning',
      showCancelButton: true,
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        const elId = el.parent().attr('id');
        templateConfigArr = templateConfigArr.filter(d => {
          if (d.signId === elId) {
            if (d.includeDate && d.spanDateId) {
              $('#' + d.spanDateId).remove();
            }
          }
          return d.signId !== elId;
        });

        saveTemplateConfig(templateConfigArr, "signature");

        el.parent().remove();
      }
    });
  }

  function handleAddSign(action) {
    let hasFirstSignature = templateConfigArr.some((d) => d.signer === 'firstSigner');
    let hasSecondSignature = templateConfigArr.some((d) => d.signer === 'secondSigner');

    Swal.fire({
      title: 'Select Signer',
      html: `
        <div class="mt-3">
          <div class="d-flex justify-content-between px-4">
            <label class="form-check-inline"><input type="radio" name="signer" value="firstSigner"> 1st Signer</label>
            <label class="form-check-inline"><input type="radio" name="signer" value="secondSigner" ${!hasFirstSignature && "disabled"}> 2nd Signer</label>
            <label class="form-check-inline"><input type="radio" name="signer" value="thirdSigner" ${!hasSecondSignature && "disabled"}> 3rd Signer</label>
          </div>
        ${action === "primary"  ? 
        ` <div class="mt-4">
            <label><input type="checkbox" id="includeDate"> Include Date</label>
          </div>` : ""}
        </div>
      `,
      preConfirm: () => {
        const signer = $('input[name="signer"]:checked').val();
        const includeDate = $('#includeDate').is(':checked');
        if (!signer) {
          Swal.showValidationMessage('You need to choose a signer!');
        }
        return {
          signer,
          includeDate
        };
      },
      showCancelButton: true,
      confirmButtonText: 'Add Signature',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        if (action === "initial") {
          handlePopulateBtnToPage("initial", result.value.signer, result.value.includeDate);
        } else {
          handlePopulateBtnToPage("primary", result.value.signer, result.value.includeDate);
        }
      }
    });
  }

  function handlePopulateBtnToPage(type, selectedSigner, includeDate) {
    $('.page').each((i, el) => {
      if ($(el).isOnScreen()) {
        const pageNumber = i + 1;
        const spanDateId = generateUniqueId(`primary_${selectedSigner}_date_${pageNumber}`);
        const signId = generateUniqueId(`primary_${selectedSigner}_sign_${pageNumber}`);

        populateButton(type, selectedSigner, includeDate, pageNumber, signId, spanDateId, "add");

        saveTemplateConfig(templateConfigArr, "signature");
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

      $(`#${spanDateId}`).draggable({
        containment: "parent",
      });
    }

    if (action == "add") {
      templateConfigArr.push({
        pageNumber: pageNumber,
        signer: selectedSigner,
        type: type,
        signId: signId,
        signPlacement: {
          signTop: $(`#${signId}`).position().top,
          signLeft: $(`#${signId}`).position().left,
        },
        spanDateId: includeDate ? spanDateId : null,
        includeDate: includeDate,
        datePlacement: includeDate ? {
          dateTop: $(`#${spanDateId}`).position().top,
          dateLeft: $(`#${spanDateId}`).position().left
        } : null
      });
    }

    $(".btnSign").draggable({
      containment: "parent",
    });

    $(el).droppable({
      drop: function(event, ui) {
        handleDropEvent(event, ui);
      }
    });
  }

  function handleDropEvent(event, ui) {
    const currentPos = ui.helper.position();
    const dataSignerType = ui.helper.attr("data-signer-type");
    const dataSigner = ui.helper.attr("data-signer");

    if (dataSignerType && dataSigner) {
      templateConfigArr = templateConfigArr.map((d) => {
        if (ui.helper.hasClass('btnSign') && d.signId === ui.helper.attr('id')) {
          return {
            ...d,
            signPlacement: {
              signTop: parseInt(currentPos.top),
              signLeft: parseInt(currentPos.left)
            }
          };
        } else if (ui.helper.hasClass('spanDate') && d.spanDateId === ui.helper.attr('id')) {
          return {
            ...d,
            datePlacement: {
              dateTop: parseInt(currentPos.top),
              dateLeft: parseInt(currentPos.left)
            }
          };
        }
        return d;
      });
      saveTemplateConfig(templateConfigArr, "signature");
    } else {
      const textBoxId = ui.helper.attr("id");

      textBoxData = textBoxData.map((tb) => {
        if (tb.id === textBoxId) {
          return {
            ...tb,
            css: {
              ...tb.css,
              top: parseInt(currentPos.top),
              left: parseInt(currentPos.left)
            }
          };
        }
        return tb;
      });

      if (textBoxData.some(tb => tb.id === textBoxId)) {
        saveTemplateConfig(textBoxData, "text");
      }
    }
  }

  function generateUniqueId(prefix) {
    return `${prefix}_${Date.now()}`;
  }

  function formatSpanDate(divId, type, signer, signId) {
    const spanText = signer === "firstSigner" ? "1st Signer" : signer === "secondSigner" ? "2nd Signer" : "3rd Signer";

    return (`
      <div class="spanDate text-center pb-0 text-dark" id="${divId}" data-signer-type="${type}" data-signer="${signer}">
        <span class="text-decoration-underline position-absolute start-50 translate-middle-x" style="top: -15px;">
          ${spanText}
        </span>
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
      <div class="btnSign text-center p-1" id="${divId}" style="width: 150px" data-signer-type="initial" data-signer="${signer}">
        <button type="button" class="btn btn-link btn-sm px-2" onclick="handleSign('initial')">
          __<span class="text-decoration-underline">${signerLabel}</span>__
        </button>
        <i class="fa-solid fa-circle-xmark text-danger custom-close-icon" onclick="handleRemoveSignBtn($(this), 'initial')"></i>
      </div>`);
    }

    return (`
      <div class="btnSign text-center" id="${divId}" data-signer-type="primary" data-signer="${signer}">
        <button type="button" class="btn btn-primary btn-sm px-4" onclick="handleSign('primary')">
          <span>${signerLabel}</span>
        </button>
        <i class="fa-solid fa-circle-xmark text-danger custom-close-icon" onclick="handleRemoveSignBtn($(this), 'primary')"></i>
      </div>`);
  }

  function generateTextBoxId() {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    const charactersLength = characters.length;
    for (let i = 0; i < 6; i++) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }

    if (textBoxData.some(tb => tb.id === result)) {
      return generateTextBoxId();
    }

    return `text_${result}`;
  }

  function addTextToCanvas(type, textBoxId = null) {
    textBoxId = textBoxId || generateTextBoxId();

    if (type == "custom_text") {
      $('.page').each((i, el) => {
        if ($(el).isOnScreen()) {
          populateTextToCanvas(textBoxId, type, i + 1, "add");
        }
      });
    } else {
      let textType = "";

      if (type == "full_name") {
        textType = "Full Name";
      } else if (type == "email") {
        textType = "Email";
      }

      let hasFirstSignature = templateConfigArr.some((d) => d.signer === 'firstSigner');
      let hasSecondSignature = templateConfigArr.some((d) => d.signer === 'secondSigner');
      let hasThirdSignature = templateConfigArr.some((d) => d.signer === 'thirdSigner');

      Swal.fire({
        title: `${textType} for`,
        html: `
        <div class="mt-3">
          <div class="d-flex justify-content-between px-4">
            <label class="form-check-inline"><input type="radio" name="text_signer" value="firstSigner" ${!hasFirstSignature && "disabled"}> 1st Signer</label>
            <label class="form-check-inline"><input type="radio" name="text_signer" value="secondSigner" ${!hasSecondSignature && "disabled"}> 2nd Signer</label>
            <label class="form-check-inline"><input type="radio" name="text_signer" value="thirdSigner" ${!hasThirdSignature && "disabled"}> 3rd Signer</label>
          </div>
        </div>
      `,
        preConfirm: () => {
          const signer = $('input[name="text_signer"]:checked').val();
          if (!signer) {
            Swal.showValidationMessage('You need to choose a signer!');
          }
          return {
            signer
          };
        },
        showCancelButton: true,
        confirmButtonText: 'Add Text',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        $('.page').each((i, el) => {
          if ($(el).isOnScreen()) {
            populateTextToCanvas(textBoxId, type, i + 1, "add", result.value.signer);
          }
        });
      });
    }
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

    if (action == "add") {
      textBoxData.push({
        id: textBoxId,
        pageNumber: pageNumber,
        text: textBoxText === textBoxPlaceholder ? "" : textBoxText,
        type: type,
        css: defaultCss,
        signer: signer
      });
    }

    textBox.draggable({
      containment: "parent",
      drag: function(event, ui) {
        if (selectedTextBox) {
          updateToolBarPos();
        }
      }
    });

    $(el).droppable({
      drop: function(event, ui) {
        handleDropEvent(event, ui);
      }
    });

    textBox.on("click", function(e) {

      selectedTextBox = $(this);

      if (selectedTextBox.attr("data-type") === "custom_text") {
        e.stopPropagation();

        if (textBoxData.some(tb => tb.id === textBoxId)) {
          $("#customTextArea").val(textBoxData.find(tb => tb.id === textBoxId).text);
        } else {
          $("#customTextArea").val("");
        }
        $("#customTextArea").removeClass("d-none");

      } else {
        $("#customTextArea").val("");
        $("#customTextArea").addClass("d-none");
      }
      $(".text-toolbar #fontSize").val(selectedTextBox.css("font-size"));
      $(".text-toolbar").css({
        top: selectedTextBox.offset().top + selectedTextBox.outerHeight() + 10,
        left: selectedTextBox.offset().left,
        display: "block"
      });
    });
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

  function updateToolBarPos() {
    $(".text-toolbar").css({
      top: selectedTextBox.offset().top + selectedTextBox.outerHeight() + 10,
      left: selectedTextBox.offset().left,
      display: "block"
    });
  }

  $("#customTextArea").on("input", function() {
    if ($(this).val()) {
      selectedTextBox.html(convertBreak($(this).val()));
      textBoxData = textBoxData.map((tb) => {
        if (tb.id === selectedTextBox.attr("id")) {
          return {
            ...tb,
            text: $(this).val()
          };
        }
        return tb;
      });
    } else {
      selectedTextBox.text(textBoxPlaceholder);
    }

    saveTemplateConfig(textBoxData, "text");
    updateToolBarPos();
  });

  $("#boldBtn").on("click", function() {
    if (selectedTextBox) {
      let fontWeight = selectedTextBox.css("font-weight") == "700" ? "400" : "700";
      selectedTextBox.css("font-weight", fontWeight);
      textBoxData = textBoxData.map((tb) => {
        if (tb.id === selectedTextBox.attr("id")) {
          return {
            ...tb,
            css: {
              ...tb.css,
              fontWeight: fontWeight
            }
          };
        }
        return tb;
      });
      saveTemplateConfig(textBoxData, "text");
    }
  });

  $("#italicBtn").on("click", function() {
    if (selectedTextBox) {
      let fontStyle = selectedTextBox.css("font-style") == "italic" ? "normal" : "italic";
      selectedTextBox.css("font-style", fontStyle);
      textBoxData = textBoxData.map((tb) => {
        if (tb.id === selectedTextBox.attr("id")) {
          return {
            ...tb,
            css: {
              ...tb.css,
              fontStyle: fontStyle
            }
          };
        }
        return tb;
      });
      saveTemplateConfig(textBoxData, "text");
    }
  });

  $("#fontSize").on("change", function() {
    if (selectedTextBox) {
      selectedTextBox.css("font-size", $(this).val());
      textBoxData = textBoxData.map((tb) => {
        if (tb.id === selectedTextBox.attr("id")) {
          return {
            ...tb,
            css: {
              ...tb.css,
              fontSize: $(this).val()
            }
          };
        }
        return tb;
      });
      saveTemplateConfig(textBoxData, "text");
    }
  });

  $("#fontColor").on("change", function() {
    if (selectedTextBox) {
      selectedTextBox.css("color", $(this).val());
      textBoxData = textBoxData.map((tb) => {
        if (tb.id === selectedTextBox.attr("id")) {
          return {
            ...tb,
            css: {
              ...tb.css,
              color: $(this).val()
            }
          };
        }
        return tb;
      });
      saveTemplateConfig(textBoxData, "text");
    }
  });

  $("#deleteText").on("click", function() {
    if (selectedTextBox) {
      textBoxData = textBoxData.filter(tb => tb.id !== selectedTextBox.attr("id"));
      saveTemplateConfig(textBoxData, "text");

      $(".text-toolbar").hide();
      selectedTextBox.remove();
      selectedTextBox = null;
    }
  });

  $(".text-toolbar").on("click", function(e) {
    e.stopPropagation();
  });

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

  function showHeaderSaving() {
    if ($("#documentStatus").length) {
      $("#documentStatus").html(`<i class="bi bi-pencil me-1"></i> Draft`);
      $("#documentStatus").removeClass("bg-success").addClass("bg-secondary");

      $("#publishTemplateBtn").show();
      $("#useTemplateBtn").hide();
    }

    if (!$("#savingMessage").is(":visible")) {
      $("#savingMessage").show();
      setTimeout(function() {
        $("#savingMessage").hide();
      }, 1000);
    }
  }

  async function saveTemplateConfig(config, type = "signature") {
    const action = type === "signature" ? "save_sign_config" : "save_text_config";

    showHeaderSaving();
    return new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "<?= SERVER_NAME . '/backend/nodes' ?>", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            try {
              const newCsrfToken = xhr.getResponseHeader('X-CSRF-Token');
              if (newCsrfToken) {
                $('meta[name="csrf-token"]').attr('content', newCsrfToken);
              }
              resolve(JSON.parse(xhr.responseText));
            } catch (e) {
              resolve(xhr.responseText);
            }
          } else {
            resolve({
              success: false,
              message: `Error: ${xhr.responseText}`
            });
          }
        }
      };
      const params = `action=${action}&id=${encodeURIComponent($("#templateId").val())}&config=${encodeURIComponent(JSON.stringify(config))}`;
      xhr.send(params);
    });
  }
</script>