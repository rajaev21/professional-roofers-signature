<div class="d-none" id="signature-toolbar">
  <div class="row">
    <div class="col-3 d-flex justify-content-start align-items-center">
      <h5 style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;" data-bs-popper="tooltip" data-bs-placement="bottom" title="<?= $template->name ?>">
        <?= $template->name ?>
      </h5>
      <?php if ($template->document_status === 'published'): ?>
        <span class="ms-2 badge bg-success" id="documentStatus">
          <i class="bi bi-check-circle me-1"></i> Published
        </span>
      <?php elseif ($template->document_status === 'draft'): ?>
        <span class="ms-2 badge bg-secondary" id="documentStatus">
          <i class="bi bi-pencil me-1"></i> Draft
        </span>
      <?php endif; ?>
    </div>
    <div class="col-6 text-center"></div>

    <div class="col-3 text-end align-bottom">
      <span id="savingMessage" class="mx-4 text-center text-muted" style="display: none;">
        Saving as draft
        <div class="spinner-grow spinner-grow-sm" role="status" style="width: .5rem; height: .5rem;"></div>
        <div class="spinner-grow spinner-grow-sm" role="status" style="width: .5rem; height: .5rem;"></div>
        <div class="spinner-grow spinner-grow-sm" role="status" style="width: .5rem; height: .5rem;"></div>
      </span>
      <button type="button" class="btn btn-secondary text-white my-2" onclick="window.history.back()">
        <i class="bi bi-chevron-left me-1"></i>
        Go Back
      </button>
    </div>
  </div>
</div>

<div class="container-fluid" style="position: relative;">
  <div class="row">
    <div class="col-md-12">
      <div class="list-group my-auto" style="position: fixed; width: auto; height: auto; z-index: 1000;" id="signatureActionButtons">
        <button class="list-group-item list-group-item-action" onclick="handleAddSign('primary')">
          <i class="fa-solid fa-signature"></i>
          <span class="ms-2">Primary Sign</span>
        </button>
        <button type="button" class="list-group-item list-group-item-action" onclick="handleAddSign('initial')">
          <i class="fa-solid fa-i"></i>
          <span class="ms-2">Initial Signature</span>
        </button>
        <div class="dropdown dropend">
          <button class="list-group-item list-group-item-action dropdown-toggle border-top-0 border-bottom-0" type="button" id="addTextDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <svg class="icon">
              <use xlink:href="<?= SERVER_NAME . "/assets/vendors/@coreui/icons/sprites/free.svg#cil-text-shapes" ?>"></use>
            </svg>
            <span class="ms-2">Add Text</span>
          </button>
          <ul class="dropdown-menu" aria-labelledby="addTextDropdown">
            <li>
              <button type="button" class="dropdown-item" onclick="addTextToCanvas('full_name');">
                <i class="bi bi-person me-2"></i>
                Full Name
              </button>
            </li>
            <li>
              <button type="button" class="dropdown-item" onclick="addTextToCanvas('email');">
                <i class="bi bi-envelope me-2"></i>
                Email
              </button>
            </li>
            <li>
              <button type="button" class="dropdown-item" onclick="addTextToCanvas('custom_text');">
                <i class="bi bi-fonts me-2"></i>
                Custom Text
              </button>
            </li>
          </ul>
        </div>
        <a href="<?= SERVER_NAME . "/views/templates?page=edit&id=$id" ?>" class="list-group-item list-group-item-action">
          <i class="bi bi-pencil-square"></i>
          <span class="ms-2">Edit Files</span>
        </a>
        <button type="button" class="list-group-item list-group-item-action" onclick="handleCustomZoom('in')">
          <i class="bi bi-zoom-in"></i>
          <span class="ms-2">Zoom In</span>
        </button>
        <button type="button" class="list-group-item list-group-item-action" onclick="handleCustomZoom('out')">
          <i class="bi bi-zoom-out"></i>
          <span class="ms-2">Zoom Out</span>
        </button>
        <?php $isDraft = $template->document_status === 'draft'; ?>
        <button type="button" class="list-group-item list-group-item-action rounded-bottom" onclick="handlePublishTemplate()" style="display: <?= $isDraft ? 'block' : 'none' ?>;" id="publishTemplateBtn">
          <i class="bi bi-upload"></i>
          <span class="ms-2">Publish</span>
        </button>
        <a href="<?= SERVER_NAME . "/views/templates?page=use&id=$id" ?>" type="button" class="list-group-item list-group-item-action rounded-bottom" style="display:<?= !$isDraft ? 'block' : 'none' ?>;" id="useTemplateBtn">
          <i class="bi bi-file-earmark-image"></i>
          <span class="ms-2">Use Template</span>
        </a>
      </div>

      <div class="justify-content-center align-items-center d-none" id="pdfLoading">
        <div class="text-center" style="z-index:10;">
          <div class="spinner-border text-success" role="status" style="width:3rem; height:3rem;">
            <span class="visually-hidden">Loading</span>
          </div>
          <div class="mt-2 text-success">Loading PDF</div>
        </div>
      </div>
      <div id="pdfContainer" class="d-flex flex-column justify-content-center align-items-center d-none">

      </div>
    </div>
  </div>
</div>

<div class="text-toolbar p-2 shadow " style="display: none;">
  <div class="btn-group btn-group-sm" role="group">
    <button class="btn btn-outline-dark rounded" id="boldBtn">
      <i class="fa-solid fa-bold"></i>
    </button>
    <button class="btn btn-outline-dark rounded" id="italicBtn">
      <i class="fa-solid fa-italic"></i>
    </button>
    <select id="fontSize" class="form-select form-select-sm d-inline" style="width: auto;">
      <option value="12px">12</option>
      <option value="14px" selected="">14</option>
      <option value="16px">16</option>
      <option value="18px">18</option>
      <option value="20px">20</option>
      <option value="22px">22</option>
    </select>
    <input type="color" id="fontColor" class="form-control form-control-sm d-inline rounded mx-1" style="width: 40px;">
    <button class="btn btn-danger rounded mx-1" id="deleteText">
      <i class="fa-solid fa-trash-can text-white"></i>
    </button>
  </div>
  <textarea id="customTextArea" class="form-control form-control-sm mt-2 d-none" rows="3"></textarea>
</div>

<input type="hidden" id="templateId" value="<?= $template->id ?>">
<input type="hidden" id="docId" value="<?= $template->doc_id ?>">
<input type="hidden" id="is_void" value="0">

<script>
  const templateId = $("#templateId").val();
  const docId = $("#docId").val();

  let pdfDoc = null,
    scale = 1.5,
    containerScale = 1;
  let textBoxData = [];
  let selectedTextBox = null;

  var templateConfigArr = [];
  var templateConfig = null;
  var pdfFiles = [];

  $(document).ready(async function() {
    $("#pdfLoading").addClass("d-flex").removeClass("d-none");
    $("#pdfContainer").addClass("d-none");
    $("#signatureActionButtons").addClass("d-none");

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

    $("#signature-toolbar").removeClass("d-none");

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
        $("#signatureActionButtons").removeClass("d-none");
      });
  });

  $(document).on("click", function(e) {
    if (!$(e.target).closest('.text-toolbar, .text-box').length) {
      $(".text-toolbar").hide();
    }
  });

  function handlePublishTemplate() {
    Swal.fire({
      title: 'Publish Template',
      text: "Are you sure you want to publish this template?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes',
      cancelButtonText: 'No',
      preConfirm: () => {
        if (templateConfigArr.length > 0) {
          return $.ajax({
            type: "POST",
            url: "<?= SERVER_NAME . '/backend/nodes' ?>",
            data: {
              action: "publish_template",
              id: $("#templateId").val()
            },
            dataType: 'JSON',
            success: (response) => response,
            error: (xhr) => {
              return {
                success: false,
                message: `Error: ${xhr.responseText}`
              };
            }
          });
        } else {
          Swal.showValidationMessage('You need to add at least one signature!');
        }
        return templateConfigArr;
      },
    }).then((result) => {
      if (result.isConfirmed) {
        swal.fire({
          icon: result.value.success ? 'success' : 'error',
          title: result.value.success ? 'Success' : 'Error',
          html: result.value.message,
        }).then(() => result.value.success && window.location.reload());
      }
    });
  }
  // // Enable touch drag for mobile
  // $(".btnSign").each(function() {
  //   let $el = $(this);
  //   let startX, startY, origX, origY, dragging = false;

  //   $el.on('touchstart', function(e) {
  //     if (e.originalEvent.touches.length === 1) {
  //       dragging = true;
  //       startX = e.originalEvent.touches[0].clientX;
  //       startY = e.originalEvent.touches[0].clientY;
  //       origX = $el.position().left;
  //       origY = $el.position().top;
  //       $el.css("transition", "none");
  //     }
  //   });

  //   $el.on('touchmove', function(e) {
  //     if (dragging && e.originalEvent.touches.length === 1) {
  //       e.preventDefault();
  //       let dx = e.originalEvent.touches[0].clientX - startX;
  //       let dy = e.originalEvent.touches[0].clientY - startY;
  //       let newLeft = origX + dx;
  //       let newTop = origY + dy;

  //       // Containment logic
  //       let $parent = $el.parent();
  //       let maxLeft = $parent.width() - $el.outerWidth();
  //       let maxTop = $parent.height() - $el.outerHeight();
  //       newLeft = Math.max(0, Math.min(newLeft, maxLeft));
  //       newTop = Math.max(0, Math.min(newTop, maxTop));

  //       $el.css({
  //         left: newLeft + "px",
  //         top: newTop + "px"
  //       });
  //     }
  //   });

  //   $el.on('touchend', function(e) {
  //     if (dragging) {
  //       dragging = false;
  //       $el.css("transition", "");
  //       // Optionally trigger drop logic here if needed
  //     }
  //   });
  // });
</script>
<?php require(__DIR__ . "/../../../components/signatures/signature-scripts.php") ?>