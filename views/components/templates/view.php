<div class="d-none" id="signature-toolbar">
  <div class="row">
    <div class="col-3 d-flex justify-content-start align-items-center">
      <h5 style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;" data-bs-popper="tooltip" data-bs-placement="bottom" title="<?= $template->name ?>">
        <?= $template->name ?>
      </h5>
      <?php if ($template->document_status === 'published'): ?>
        <span class="ms-2 badge bg-success">
          <i class="bi bi-check-circle me-1"></i> Published
        </span>
      <?php elseif ($template->document_status === 'draft'): ?>
        <span class="ms-2 badge bg-secondary">
          <i class="bi bi-pencil me-1"></i> Draft
        </span>
      <?php endif; ?>
    </div>
    <div class="col-6 d-flex justify-content-center"></div>

    <div class="col-3 text-end align-bottom">
      <button type="button" class="btn btn-secondary text-white my-2" onclick="window.history.back()">
        <i class="bi bi-chevron-left me-1"></i>
        Go Back
      </button>
    </div>
  </div>
</div>

<div class="container-fluid position-relative">
  <div class="row">
    <div class="col-md-12">
      <div class="list-group my-auto" style="position: fixed; width: auto; height: auto; z-index: 1000;" id="signatureActionButtons">
        <a href="<?= SERVER_NAME . "/views/templates?page=signature&id=$id" ?>" class="list-group-item list-group-item-action">
          <i class="bi bi-pencil-square"></i>
          Edit Template
        </a>
        <button type="button" class="list-group-item list-group-item-action" onclick="handleCustomZoom('in')" data-bs-placement="bottom" data-bs-original-title="Zoom in">
          <i class="bi bi-zoom-in"></i>
          Zoom In
        </button>
        <button type="button" class="list-group-item list-group-item-action" onclick="handleCustomZoom('out')" data-bs-placement="bottom" data-bs-original-title="Zoom out">
          <i class="bi bi-zoom-out"></i>
          Zoom Out
        </button>
      </div>

      <div class="justify-content-center align-items-center d-none" id="pdfLoading">
        <div class="text-center" style="z-index:10;">
          <div class="spinner-border text-success" role="status" style="width:3rem; height:3rem;">
            <span class="visually-hidden">Loading</span>
          </div>
          <div class="mt-2 text-success">Loading PDF</div>
        </div>
      </div>
      <div id="pdfContainer" class="d-flex flex-column justify-content-center align-items-center d-none"></div>
    </div>
  </div>
</div>
<input type="hidden" id="templateId" value="<?= $template->id ?>">
<input type="hidden" id="docId" value="<?= $template->doc_id ?>">
<input type="hidden" id="is_void" value="0">

<script>
  const templateId = $("#templateId").val();
  const docId = $("#docId").val();

  var pdfDoc = null,
    scale = 1.5,
    containerScale = 1;
  var textBoxData = [];
  var selectedTextBox = null;

  var templateConfigArr = [];

  var templateConfig = null;
  var pdfFiles = [];

  $(document).ready(async function() {
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