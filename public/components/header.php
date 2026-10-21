<header class="header header-sticky p-0 mb-4">
  <div class="container-fluid px-2 px-md-4">
    <div id="signature-toolbar-container" class="w-100">
      <div class="row g-2">
        <div class="col-12 col-md-3 d-flex justify-content-center justify-content-md-start align-items-center mb-2 mb-md-0">
          <?php if (isset($customer)) : ?>
            <small class="text-center text-md-start">Please review the documents below</small>
          <?php endif; ?>
        </div>
        <div class="col-12 col-md-6 text-center mb-2 mb-md-0">
          <div class="btn-group flex-wrap" role="group">
            <?php if (isset($customer)) : ?>
              <a href="<?= SERVER_NAME . "/public/signature?s=$_GET[s]&t=$_GET[t]&c=$_GET[c]&edit_signature" ?>" class="btn btn-outline-success btn-sm" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Edit Signature">
                <i class="fa-solid fa-signature"></i>
                <span class="d-none d-lg-inline">Edit Signature</span>
              </a>
            <?php endif; ?>
            <button type="button" class="btn btn-outline-success btn-sm" onclick="handleCustomZoom('in')" data-bs-placement="bottom" data-bs-original-title="Zoom in">
              <i class="bi bi-zoom-in"></i>
              <span class="d-none d-lg-inline">Zoom In</span>
            </button>
            <button type="button" class="btn btn-outline-success btn-sm" onclick="handleCustomZoom('out')" data-bs-placement="bottom" data-bs-original-title="Zoom out">
              <i class="bi bi-zoom-out"></i>
              <span class="d-none d-lg-inline">Zoom Out</span>
            </button>

            <?php if (isset($customer)) : ?>
              <button type="button" id="btnUp" class="btn btn-outline-success btn-sm" data-bs-placement="bottom" data-bs-original-title="Previously Signed">
                <i class="bi bi-arrow-up"></i>
                <span class="d-none d-lg-inline">Previous</span>
              </button>

              <button type="button" id="btnDown" class="btn btn-outline-success btn-sm" data-bs-placement="bottom" data-bs-original-title="Next Sign">
                <i class="bi bi-arrow-down"></i>
                <span class="d-none d-lg-inline">Next</span>
              </button>
            <?php else : ?>
              <button type="button" class="btn btn-outline-success btn-sm" onclick="printPDF()" data-bs-placement="bottom" data-bs-original-title="Print PDF">
                <i class="bi bi-printer"></i>
                <span class="d-none d-lg-inline">Print</span>
              </button>
              <button type="button" class="btn btn-outline-success btn-sm" onclick="downloadPDF()" data-bs-placement="bottom" data-bs-original-title="Download PDF">
                <i class="bi bi-file-earmark-pdf"></i>
                <span class="d-none d-lg-inline">Download</span>
              </button>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-12 col-md-3 text-center text-md-end">
          <span id="savingMessage" class="mx-2 text-center text-muted d-inline-block d-none">
            Saving
            <div class="spinner-grow spinner-grow-sm" role="status" style="width: .5rem; height: .5rem;"></div>
            <div class="spinner-grow spinner-grow-sm" role="status" style="width: .5rem; height: .5rem;"></div>
            <div class="spinner-grow spinner-grow-sm" role="status" style="width: .5rem; height: .5rem;"></div>
          </span>

          <button type="button" class="btn btn-primary btn-sm d-none" id="btnFinish">
            <i class="fa-solid fa-paper-plane me-1"></i>
            <span class="d-none d-sm-inline">Finish</span>
          </button>

          <?php if (isset($_GET["skip"])): ?>
            <button type="button" class="btn btn-secondary btn-sm ms-2" onclick='window.location.replace(`<?= SERVER_NAME . "/public/signature?" . http_build_query(array_diff_key($_GET, ["skip" => ""])) ?>`)'>
              <i class="fa-solid fa-arrow-left me-1"></i>
              <span class="d-none d-sm-inline">Back</span>
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</header>

<?php require(__DIR__ . "/../../components/loading-screen.php") ?>

<script>
  async function printPDF() {
    swal.showLoading();
    const $contractBody = $('#pdfContainer');
    const $pages = $contractBody.find('.page');

    if (!$pages.length) {
      swal.fire({
        title: "Error",
        text: "No pages found to print.",
        icon: "error",
      });
      return;
    }

    try {
      const canvases = await Promise.all(
        $pages.map(function(i, page) {
          const $page = $(page);
          $page.css({
            boxShadow: 'none',
            margin: 0,
            border: 'none',
            padding: 0
          });
          return html2canvas(page, {
            backgroundColor: "#fff",
            scale: 2,
            allowTaint: true,
            logging: false
          });
        }).get()
      );

      const printWindow = window.open('');
      canvases.forEach(function(canvas, i) {
        const imgData = canvas.toDataURL('image/png');
        printWindow.document.write(`<img src="${imgData}" style="width:100%; height:auto; margin:0; border:none; page-break-after:always;" />`);
      });

      printWindow.document.close();
      printWindow.onload = function() {
        printWindow.print();
        setTimeout(() => printWindow.close(), 500);
        swal.close();
      };

      printWindow.onafterprint = function() {
        printWindow.close();
      };
    } catch (error) {
      console.error("PDF print error:", error);
      swal.fire({
        title: "Error",
        text: "Failed to print PDF. Please try again.",
        icon: "error",
      });
    }
  }

  async function downloadPDF() {
    const {
      jsPDF
    } = window.jspdf;

    swal.showLoading();
    const $contractBody = $('#pdfContainer');
    const $pages = $contractBody.find('.page');

    if (!$pages.length) {
      swal.fire({
        title: "Error",
        text: "No pages found to export.",
        icon: "error",
      });
      return;
    }

    const $firstPage = $pages.first();
    const pageWidth = $firstPage.outerWidth();
    const pageHeight = $firstPage.outerHeight();

    const pdf = new jsPDF({
      orientation: 'portrait',
      unit: 'px',
      format: [pageWidth, pageHeight]
    });

    try {
      const canvases = await Promise.all(
        $pages.map(function(i, page) {
          const $page = $(page);
          $page.css({
            boxShadow: 'none',
            margin: 0,
            border: 'none'
          });
          return html2canvas(page, {
            backgroundColor: "#fff",
            scale: 1,
            allowTaint: true,
            logging: false
          });
        }).get()
      );

      canvases.forEach(function(canvas, i) {
        const imgData = canvas.toDataURL('image/png', 0.75);
        pdf.addImage(imgData, 'PNG', 0, 0, pageWidth, pageHeight);
        if (i < canvases.length - 1) {
          pdf.addPage([pageWidth, pageHeight], 'portrait');
        }
      });
      pdf.save("document-" + Date.now() + ".pdf");
      swal.close();
    } catch (error) {
      console.error("PDF generation error:", error);
      swal.fire({
        title: "Error",
        text: "Failed to generate PDF. Please try again.",
        icon: "error",
      });
    }
  }

  $("#btnFinish").on("click", async function() {
    try {
      const submitSignature = await handleSubmitSignature();
      swal.fire({
        title: submitSignature.success ? "Success" : "Error",
        html: submitSignature.message,
        icon: submitSignature.success ? "success" : "error",
      }).then(() => submitSignature.success ? $(this).addClass("d-none") : null);
    } catch (error) {
      swal.fire({
        title: "Error",
        text: "An error occurred while submitting the signature.",
        icon: "error",
      });
    }
  });

  async function handleSubmitSignature() {
    let formData = new FormData();
    formData.append("action", "submit_document");
    formData.append("sent_template_id", $("#sent_template_id").val());
    formData.append("template_id", $("#templateId").val());

    const response = await $.ajax({
      url: "<?= SERVER_NAME . "/backend/nodes" ?>",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "JSON",
      success: (res) => res,
      error: (xhr, status, error) => {
        return {
          success: false,
          message: "An error occurred while processing your request."
        };
      }
    });

    return response;
  }

  function handleCustomZoom(action) {
    const minScale = 0.3;
    const maxScale = 3;
    containerScale = typeof containerScale === 'number' ? containerScale : 1;

    if (action === 'in') {
      containerScale = Math.min(maxScale, +(containerScale + 0.1).toFixed(2));
    } else if (action === 'out') {
      containerScale = Math.max(minScale, +(containerScale - 0.1).toFixed(2));
    }

    const $pdf = $('#pdfContainer');
    $pdf.css({
      transform: `scale(${containerScale})`,
      transformOrigin: 'top left'
    });

    const isMobile = window.innerWidth <= 768 || 'ontouchstart' in window;
    if (isMobile) {
      $pdf.css('width', (100 / containerScale) + '%');
      $pdf.parent().css({
        overflow: 'auto',
        '-webkit-overflow-scrolling': 'touch'
      });
    } else {
      $pdf.css('width', '');
      $pdf.parent().css({
        overflow: '',
        '-webkit-overflow-scrolling': ''
      });
    }
  }

  $("#btnUp").on("click", function() {
    const signed = signedData.filter(d => d.signer === signer).filter((d) => d.isSigned);
    const lastPreviouslySigned = signed[signed.length - 1];

    if (lastPreviouslySigned) {
      const el = $(`#${lastPreviouslySigned.signId}`);
      $("html, body").animate({
          scrollTop: el.offset().top - 500,
        },
        200
      );
    } else {
      toastr.info("No previously signed to scroll.");
    }
  });

  $("#btnDown").on("click", function() {
    const unSigned = signedData.filter(d => d.signer === signer).filter((d) => !d.isSigned);

    if (unSigned.length) {
      const el = $(`#${unSigned[0].signId}`);
      $("html, body").animate({
          scrollTop: el.offset().top - 500,
        },
        200
      );
    } else {
      toastr.info("No more signatures to scroll.");
    }
  });
</script>