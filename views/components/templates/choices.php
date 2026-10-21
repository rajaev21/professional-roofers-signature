<div class="container">
  <div class="row mb-2">
    <div class="col-6 col-md-6">
      <h1 class="h3">Create Template</h1>
    </div>
    <div class="col-6 col-md-6 text-end">
      <button type="button" class="btn btn-secondary text-white my-2" onclick="window.location.replace(`<?= SERVER_NAME . '/views/templates' ?>`)">
        <i class="bi bi-chevron-left me-1"></i>
        Go Back
      </button>
    </div>
  </div>

  <div class="card">
    <form id="formTemplate" method="POST" enctype="multipart/form-data">
      <div class="card-body">
        <div class="mb-3">
          <label id="templateName" class="form-label ">
            Template Name
          </label>
          <input type="text" id="templateName" class="form-control" name="name" required>
        </div>
        <div class="mb-3">
          <label for="formFile" class="form-label ">
            Upload Files
          </label>
          <input class="form-control" type="file" id="formFile" name="upload_template[]" accept=".pdf" onchange="previewPDF(event)" multiple required>

          <div class="row mt-3 w-100" id="pdfContainer"></div>
        </div>
        <hr>
        <div class="row mb-3">
          <h4>Add Message</h4>
          <div class="col-md-12 px-lg-5 mb-2">
            <label for="emailSubject" class="form-label">Email Subject</label>
            <input type="text" name="email_subject" id="emailSubject" class="form-control" maxlength="100" required>
            <small class="text-muted">Remaining Characters: <span id="remainingChars">100</span></small>
          </div>
          <div class="col-md-12 px-lg-5 mb-2">
            <label for="emailMessage" class="form-label">Email Message</label>
            <textarea name="email_message" id="emailMessage" class="form-control" rows="10" maxlength="1000"></textarea>
            <small class="text-muted">Remaining Characters: <span id="remainingCharsMessage">1000</span></small>
          </div>
        </div>
      </div>
      <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary text-white">
          Next
          <i class="bi bi-chevron-right ms-1"></i>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  const maxFileLength = 8;
  var fileOrder = [];

  $("#pdfContainer").sortable({
    items: ".card-preview",
    forcePlaceholderSize: true,
    tolerance: "pointer",
    scroll: true,
    appendTo: document.body,
    containment: "window",
    start: function(e, ui) {
      ui.item.addClass('dragging');
      console.log(fileOrder)
    },
    stop: function(e, ui) {
      ui.item.removeClass('dragging');
      fileOrder = [];
      $(".card-preview").each(function(index) {
        const fileName = $(this).data("file-name");
        fileOrder.push({
          index: index,
          name: fileName
        });
      });
      console.log(fileOrder);
    }
  }).disableSelection();

  $("#emailSubject").on("input", function() {
    const maxLength = 100;
    const currentLength = $(this).val().length;
    const remainingChars = maxLength - currentLength;
    $("#remainingChars").text(remainingChars);
  });

  $("#emailMessage").on("input", function() {
    const maxLength = 1000;
    const currentLength = $(this).val().length;
    const remainingChars = maxLength - currentLength;
    $("#remainingCharsMessage").text(remainingChars);
  });

  $("#formTemplate").validate({
    submitHandler: function() {
      let files = $("#formFile")[0].files;

      if (files.length > maxFileLength) {
        Swal.fire({
          icon: 'error',
          title: 'Too Many Files',
          text: `You can only upload up to ${maxFileLength} PDF files.`,
        });
        return false;
      }

      let formData = new FormData($("#formTemplate").get(0));
      formData.append("action", "create_template");
      formData.append("created_by", <?= $current_user->id ?>);
      formData.append("file_order", JSON.stringify(fileOrder));

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
          Swal.fire({
            icon: response.success ? 'success' : 'error',
            title: response.success ? 'Success' : 'Error',
            html: response.message,
          }).then(() => window.location.replace(response.redirect));
        },
        error: function(xhr, status, error) {
          console.error(error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while processing your request.',
          });
          console.log(xhr.responseText);
        }
      });
    },
    rules: {
      'upload_template': {
        required: true,
        extension: "pdf",
        filesize: 10485760,
        maxfiles: maxFileLength
      }
    },
    errorElement: "span",
    errorPlacement: function(error, element) {
      if (element.is(":radio")) {
        error.addClass("invalid-feedback").insertAfter(element.closest(".form-check").parent().last());
      } else {
        error.addClass("invalid-feedback").insertAfter(element);
      }
    },
    highlight: function(element) {
      if ($(element).is(":radio")) {
        $(element).closest(".col-md-6").find(".form-check-input").addClass('is-invalid');
      } else {
        $(element).addClass('is-invalid');
      }
    },
    unhighlight: function(element) {
      if ($(element).is(":radio")) {
        $(element).closest(".col-md-6").find(".form-check-input").removeClass('is-invalid');
      } else {
        $(element).removeClass('is-invalid');
      }
    },
  });

  if (!$.validator.methods.extension) {
    $.validator.addMethod("extension", function(value, element, param) {
      param = typeof param === "string" ? param.replace(/,/g, '|') : "pdf";
      return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
    }, $.validator.format("Please enter a file with a valid extension."));
  }

  if (!$.validator.methods.filesize) {
    $.validator.addMethod("filesize", function(value, element, param) {
      if (element.files.length === 0) return true;
      for (let i = 0; i < element.files.length; i++) {
        if (element.files[i].size > param) return false;
      }
      return true;
    }, $.validator.format("File size must not exceed {0} bytes each."));
  }

  $.validator.addMethod("maxfiles", function(value, element, param) {
    return element.files.length <= param;
  }, "You can only upload up to {0} files.");

  function previewPDF(event) {
    if (event.target.files.length > maxFileLength) {
      Swal.fire({
        icon: 'error',
        title: 'Too Many Files',
        text: `You can only upload up to ${maxFileLength} PDF files.`,
      });

      event.target.value = '';
      $("#pdfContainer").empty();
      fileOrder = [];
      return;
    }

    $("#pdfContainer").empty();
    fileOrder = [];

    for (let i = 0; i < event.target.files.length; i++) {
      const file = event.target.files[i];

      if (file && file.type === "application/pdf") {
        const loadingCard = $(`
        <div class="col-md-3" id="loadingCard_${i}">
          <div class="card">
            <div class="card-body d-flex justify-content-center align-items-center" style="height:260px;">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <div class="card-footer text-center">
              <span class="text-muted">${file.name}</span>
            </div>
          </div>
        </div>
      `);
        $("#pdfContainer").append(loadingCard);

        const reader = new FileReader();

        reader.onload = function(e) {
          pdfjsLib.getDocument({
            data: new Uint8Array(e.target.result)
          }).promise.then(function(pdf) {
            pdf.getPage(1).then(function(page) {

              const viewport = page.getViewport({
                scale: 1.0
              });

              const canvas = $('<canvas style="width: 100%; height: auto;" class="rounded-top"></canvas>')[0];
              canvas.width = viewport.width;
              canvas.height = viewport.height;
              const context = canvas.getContext('2d');

              const renderContext = {
                canvasContext: context,
                viewport: viewport
              };

              page.render(renderContext).promise.then(function() {
                const pageNumber = pdf.numPages;
                const pagePreview = URL.createObjectURL(file);

                const cardPreview = $(`
                <div class="col-md-3 mb-3 card-preview" id="card_${i}" data-file-index="${i}" data-file-name="${file.name}">
                  <div class="card">
                    <div class="card-body p-0 pdf-canvas-container"></div>
                      <div class="card-footer d-flex justify-content-between align-items-center">
                        <div>
                          <h6 style="width:220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                          ${file.name}
                          </h6>
                          <span class="text-muted">${pageNumber} ${pageNumber === 1 ? 'Page' : 'Pages'}</span>
                        </div>
                        <div class="dropdown">
                          <button class="btn btn-outline-secondary" type="button" id="moreDropdownPage_${i}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                          </button>
                          <div class="dropdown-menu" aria-labelledby="moreDropdownPage_${i}">
                            <a href="${pagePreview}" class="dropdown-item" target="_blank" rel="noopener noreferrer">
                              View
                            </a>
                          </div>
                        </div>
                    </div>
                  </div>
                </div>`);
                cardPreview.find('.pdf-canvas-container').append(canvas);
                cardPreview.find('.pdf-canvas-container').css({
                  'overflow': 'hidden',
                  'position': 'relative',
                  'width': '100%',
                  'height': '260px'
                });

                $(`#loadingCard_${i}`).replaceWith(cardPreview);
                fileOrder.push({
                  index: i,
                  name: file.name
                });
              });
            });
          }).catch(function(error) {
            $(`#loadingCard_${i}`).remove();
            Swal.fire({
              icon: 'error',
              title: 'PDF Error',
              text: 'Failed to load PDF: ' + error.message,
            });
          });
        };
        reader.readAsArrayBuffer(file);
      } else {
        $("#pdfPreview").hide();
        break;
        Swal.fire({
          icon: 'error',
          title: 'Invalid File',
          text: 'Please upload a valid PDF file.',
        });
      }
    }
  }
</script>