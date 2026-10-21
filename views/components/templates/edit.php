<div class="container">
  <div class="row mb-2">
    <div class="col-6 col-md-6">
      <h1 class="h3">Edit Template</h1>
    </div>
    <div class="col-6 col-md-6 text-end">
      <button type="button" class="btn btn-secondary text-white my-2" onclick="window.history.back()">
        <i class="bi bi-chevron-left me-1"></i>
        Go Back
      </button>
    </div>
  </div>

  <div class="card">
    <form id="formEditTemplate" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="template_id" id="templateId" value="<?= $template->id ?>">
      <div class="card-body">
        <div class="mb-3">
          <label id="templateName" class="form-label ">
            Template Name
          </label>
          <input type="text" id="templateName" class="form-control" name="name" value="<?= $template->name ?>" required>
        </div>
        <div class="mb-3">
          <label for="formFile" class="form-label ">
            Template Files
            <button type="button" class="btn btn-primary btn-sm ms-2" onclick="triggerFileInput()">
              <i class="bi bi-plus-lg"></i> Add Files
            </button>
            <input class="form-control d-none" type="file" id="formFile" name="upload_template[]" accept="application/pdf" multiple>
          </label>
          <div class="row mt-3 w-100" id="pdfContainer"></div>
        </div>
        <hr>
        <div class="row mb-3">
          <h4>Add Message</h4>
          <div class="col-md-12 px-lg-5 mb-2">
            <label for="emailSubject" class="form-label">Email Subject</label>
            <input type="text" name="email_subject" id="emailSubject" class="form-control" value="<?= $template->email_subject ?>" maxlength="100" required>
            <small class="text-muted">Remaining Characters: <span id="remainingChars"><?= 100 - strlen($template->email_subject) ?></span></small>
          </div>
          <div class="col-md-12 px-lg-5 mb-2">
            <label for="emailMessage" class="form-label">Email Message</label>
            <textarea name="email_message" id="emailMessage" class="form-control" rows="10" maxlength="1000"><?= $template->email_message ?></textarea>
            <small class="text-muted">Remaining Characters: <span id="remainingCharsMessage"><?= 1000 - strlen($template->email_message) ?></span></small>
          </div>
        </div>
      </div>
      <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary text-white">
          Submit
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  const maxFileLength = 8;
  let fileOrder = [];
  let pdfFiles = [];

  if (!$.validator.methods.extension) {
    $.validator.addMethod("extension", function(value, element, param) {
      param = typeof param === "string" ? param.replace(/,/g, '|') : "pdf";
      return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
    }, $.validator.format("Please enter a file with a valid extension."));
  }
  if (!$.validator.methods.filesize) {
    $.validator.addMethod("filesize", function(value, element, param) {
      if (element.files.length === 0) return true;
      for (let i = 0; i < element.files.length; i++)
        if (element.files[i].size > param) return false;
      return true;
    }, $.validator.format("File size must not exceed {0} bytes each."));
  }
  $.validator.addMethod("maxfiles", function(value, element, param) {
    return element.files.length <= param;
  }, "You can only upload up to {0} files.");

  $("#formEditTemplate").validate({
    submitHandler: function() {
      let files = $("#formFile")[0].files;
      if (files.length > maxFileLength) {
        Swal.fire({
          icon: 'error',
          title: 'Too Many Files',
          text: `Max ${maxFileLength} PDF files.`
        });
        return false;
      }
      let formData = new FormData($("#formEditTemplate")[0]);
      formData.append("action", "edit_template");
      formData.append("file_order", JSON.stringify(fileOrder));

      $.ajax({
        url: "<?= SERVER_NAME . "/backend/nodes" ?>",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'JSON',
        success: r => Swal.fire({
          icon: r.success ? 'success' : 'error',
          title: r.success ? 'Success' : 'Error',
          html: r.message
        }).then(() => r.success ? window.location.reload() : null),
        error: () => Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'An error occurred.'
        })
      });
    },
    rules: {
      'upload_template': {
        required: true,
        extension: "pdf",
        filesize: 10485760,
        maxfiles: maxFileLength
      }
    }
  });

  function handleRemoveFile($icon, id = null) {
    Swal.fire({
      title: 'Remove file?',
      text: "You can't undo this after successful deletion.",
      icon: 'warning',
      showDenyButton: true,
      confirmButtonText: 'Yes',
      denyButtonText: 'Cancel'
    }).then(function(result) {
      if (!result.isConfirmed) return;
      var $card = $icon.closest('.card-preview');
      var fileName = $card.data('file-name');

      if (id) {
        $.ajax({
          url: "<?= SERVER_NAME . "/backend/nodes" ?>",
          type: "POST",
          data: {
            action: "remove_pdf_file",
            file_id: id,
            template_id: $("#templateId").val()
          },
          dataType: "JSON",
          success: function(data) {
            if (data.success) {
              fileOrder = fileOrder.filter(f => f.id !== id);
              fileOrder = fileOrder.map((f, idx) => ({
                ...f,
                index: idx
              }));
              $card.remove();
            } else {
              Swal.fire({
                title: 'Error removing file',
                text: data.message || 'An error occurred while removing the file.',
                icon: 'error'
              });
            }
          },
          error: function() {
            Swal.fire({
              title: 'Error removing file',
              text: 'An error occurred while removing the file.',
              icon: 'error'
            });
          }
        });
      } else {
        var $input = $('#formFile');
        var dt = new DataTransfer();
        $.each($input[0].files, function(i, f) {
          if (f.name !== fileName) dt.items.add(f);
        });
        $input[0].files = dt.files;

        fileOrder = $.grep(fileOrder, function(f) {
          return f.name !== fileName;
        });

        $card.remove();
      }
    });
  }

  $(async function() {
    $.post("<?= SERVER_NAME . "/backend/nodes" ?>", {
      action: "get_pdf_files",
      template_id: $("#templateId").val(),
    }, function(data) {
      if (data.success && data.file_data) renderExistingPDFs(data.file_data);
    }, "json");
  });

  function renderExistingPDFs(files) {
    $("#pdfContainer").empty();
    fileOrder = [];
    files.forEach((file, i) => {
      let fileName = file.file_name.replace(/^[^_]+_/, '');
      let loadingCard = $(`
      <div class="col-md-3" id="loadingCard_existing_${i}">
        <div class="card">
          <div class="card-body d-flex justify-content-center align-items-center" style="height:260px;">
            <div class="spinner-border text-primary"></div>
          </div>
          <div class="card-footer text-center">
            <span class="text-muted">${fileName}</span>
          </div>
        </div>
      </div>
    `);
      $("#pdfContainer").append(loadingCard);
      pdfjsLib.getDocument(file.url).promise.then(pdf => {
        pdf.getPage(1).then(page => {
          let canvas = $('<canvas style="width:100%;height:auto;" class="rounded-top"></canvas>')[0];
          let v = page.getViewport({
            scale: 1.0
          });
          canvas.width = v.width;
          canvas.height = v.height;
          page.render({
            canvasContext: canvas.getContext('2d'),
            viewport: v
          }).promise.then(() => {
            let pageNumber = pdf.numPages;
            let cardPreview = $(`
            <div class="col-md-3 mb-3 card-preview" id="card_existing_${i}" data-file-index="${i}" data-file-name="${fileName}" data-file-id="${file.id}" data-file-url="${file.url}">
              <div class="card">
                <i class="z-1 fa-solid fa-circle-xmark text-danger custom-close-icon" onclick="handleRemoveFile($(this),'${file.id}')" style="font-size:1.4rem;top:-10px;right:-10px;"></i>
                <div class="card-body p-0 pdf-canvas-container"></div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                  <div>
                    <h6 style="width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${fileName}</h6>
                    <span class="text-muted">${pageNumber} ${pageNumber === 1 ? 'Page' : 'Pages'}</span>
                  </div>
                  <div class="dropdown">
                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                      <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <div class="dropdown-menu">
                      <a href="${file.url}" class="dropdown-item" target="_blank">View</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          `);
            cardPreview.find('.pdf-canvas-container').append(canvas);
            cardPreview.find('.pdf-canvas-container').css({
              overflow: 'hidden',
              position: 'relative',
              width: '100%',
              height: '260px'
            });
            $(`#loadingCard_existing_${i}`).replaceWith(cardPreview);
            fileOrder.push({
              id: file.id,
              url: file.url,
              index: i,
              name: fileName
            });
          });
        });
      }).catch(() => $(`#loadingCard_existing_${i}`).remove());
    });
  }

  $("#pdfContainer").sortable({
    items: ".card-preview",
    stop: function() {
      fileOrder = [];
      $(".card-preview").each(function(index) {
        fileOrder.push({
          index,
          id: $(this).data("file-id") || null,
          url: $(this).data("file-url") || null,
          name: $(this).data("file-name")
        });
      });
    }
  }).disableSelection();

  $("#emailSubject, #emailMessage").on("input", function() {
    let maxLength = parseInt($(this).attr("maxlength"));
    let remaining = maxLength - $(this).val().length;
    $(this).is("#emailSubject") ? $("#remainingChars").text(remaining) : $("#remainingCharsMessage").text(remaining);
  });

  function triggerFileInput() {
    $("#formFile").click();
  }

  $("#formFile").on("change", function(e) {
    let files = e.target.files;
    let currentCount = $(".card-preview").length;
    if (currentCount + files.length > maxFileLength) {
      Swal.fire({
        icon: 'error',
        title: 'Too Many Files',
        text: `Max ${maxFileLength} PDF files in total.`
      });
      e.target.value = '';
      return;
    }
    for (let i = 0; i < files.length; i++) {
      let file = files[i];
      if (file.type !== "application/pdf") {
        Swal.fire({
          icon: 'error',
          title: 'Invalid File',
          text: 'Please upload a valid PDF file.'
        });
        break;
      }
      let previewIndex = currentCount + i;
      let loadingCard = $(`
      <div class="col-md-3" id="loadingCard_${previewIndex}">
        <div class="card">
          <div class="card-body d-flex justify-content-center align-items-center" style="height:260px;">
            <div class="spinner-border text-primary"></div>
          </div>
          <div class="card-footer text-center">
            <span class="text-muted">${file.name}</span>
          </div>
        </div>
      </div>
    `);

      $("#pdfContainer").append(loadingCard);
      let reader = new FileReader();
      reader.onload = function(ev) {
        pdfjsLib.getDocument({
          data: new Uint8Array(ev.target.result)
        }).promise.then(pdf => {
          pdf.getPage(1).then(page => {
            let canvas = $('<canvas style="width:100%;height:auto;" class="rounded-top"></canvas>')[0];
            let v = page.getViewport({
              scale: 1.0
            });
            canvas.width = v.width;
            canvas.height = v.height;
            page.render({
              canvasContext: canvas.getContext('2d'),
              viewport: v
            }).promise.then(() => {
              let pageNumber = pdf.numPages;
              let cardPreview = $(`
              <div class="col-md-3 mb-3 card-preview" id="card_${previewIndex}" data-file-index="${previewIndex}" data-file-name="${file.name}">
                <div class="card">
                  <i class="z-1 fa-solid fa-circle-xmark text-danger custom-close-icon" onclick="handleRemoveFile($(this))" style="font-size:1.4rem;top:-10px;right:-10px;"></i>
                  <div class="card-body p-0 pdf-canvas-container"></div>
                  <div class="card-footer d-flex justify-content-between align-items-center">
                    <div>
                      <h6 style="width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${file.name}</h6>
                      <span class="text-muted">${pageNumber} ${pageNumber === 1 ? 'Page' : 'Pages'}</span>
                    </div>
                    <div class="dropdown">
                      <button class="btn btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                      </button>
                      <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" target="_blank">View</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            `);
              cardPreview.find('.pdf-canvas-container').append(canvas);
              cardPreview.find('.pdf-canvas-container').css({
                overflow: 'hidden',
                position: 'relative',
                width: '100%',
                height: '260px'
              });
              $(`#loadingCard_${previewIndex}`).replaceWith(cardPreview);
              fileOrder.push({
                index: previewIndex,
                name: file.name
              });
            });
          });
        }).catch(() => {
          $(`#loadingCard_${previewIndex}`).remove();
        });
      };
      reader.readAsArrayBuffer(file);
    }
  });
</script>