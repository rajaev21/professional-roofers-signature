$.fn.isOnScreen = function () {
  var win = $(window);

  var viewport = {
    top: win.scrollTop(),
    left: win.scrollLeft(),
  };
  viewport.right = viewport.left + win.width();
  viewport.bottom = viewport.top + win.height();

  var bounds = this.offset();
  bounds.right = bounds.left + this.outerWidth();
  bounds.bottom = bounds.top + this.outerHeight();

  var visibleWidth =
    Math.min(bounds.right, viewport.right) -
    Math.max(bounds.left, viewport.left);
  var visibleHeight =
    Math.min(bounds.bottom, viewport.bottom) -
    Math.max(bounds.top, viewport.top);
  var visibleArea = visibleWidth * visibleHeight;

  return (
    visibleArea > 0 &&
    visibleArea ===
      Math.max.apply(
        null,
        $(".page")
          .map(function () {
            var elBounds = $(this).offset();
            elBounds.right = elBounds.left + $(this).outerWidth();
            elBounds.bottom = elBounds.top + $(this).outerHeight();

            var elVisibleWidth =
              Math.min(elBounds.right, viewport.right) -
              Math.max(elBounds.left, viewport.left);
            var elVisibleHeight =
              Math.min(elBounds.bottom, viewport.bottom) -
              Math.max(elBounds.top, viewport.top);
            return elVisibleWidth * elVisibleHeight;
          })
          .get()
      )
  );
};

window.handleDelete = function (
  backendUrl,
  config = {
    title: "Are you sure you want to remove this item?",
    text: "You can't undo this action after successful deletion.",
    buttonText: "Delete",
    buttonColor: "#dc3545",
  },
  postData = {
    table: "",
    column: "",
    val: "",
  }
) {
  swal
    .fire({
      title: config.title,
      text: config.text,
      icon: "warning",
      confirmButtonText: config.buttonText,
      confirmButtonColor: config.buttonColor,
      showCancelButton: true,
      showLoaderOnConfirm: true,
      backdrop: true,
      preConfirm: async (value) => {
        const res = await $.ajax({
          url: backendUrl,
          data: postData,
          type: "POST",
          dataType: "JSON",
          success: function (resp) {
            if (!resp.success) {
              return Swal.showValidationMessage(`Error: ${resp.message}`);
            }
          },
        });

        return res;
      },
      allowOutsideClick: () => !Swal.isLoading(),
    })
    .then((d) => {
      if (d.isConfirmed) {
        swal
          .fire({
            title: !d.value.success ? "Error!" : "Success",
            html: !d.value.success
              ? d.value.message
              : "Item successfully deleted",
            icon: !d.value.success ? "error" : "success",
          })
          .then(() => (d.value.success ? window.location.reload() : undefined));
      }
    });
};

window.handleDeleteNoConfirm = function (
  backendUrl,
  postData = {
    table: "",
    column: "",
    val: "",
  }
) {
  $.post(backendUrl, postData, (data, status) => {
    const resp = JSON.parse(data);
    if (!resp.success) {
      swal.fire({
        title: "Error!",
        html: resp.message,
        icon: "error",
      });
    } else {
      window.location.reload();
    }
  }).fail(function (e) {
    swal.fire({
      title: "Error!",
      html: e.statusText,
      icon: "error",
    });
  });
};

window.handleOpenModalImg = (el, modalId, modalImgId, src = null) => {
  if (!src) {
    $(`#${modalImgId}`).attr("src", el[0].src);
  } else {
    $(`#${modalImgId}`).attr("src", src);
  }

  $("html").css({
    overflow: "hidden",
  });
  $(`#${modalId}`).show();
};

window.handleCloseModalImg = (modalId, modalImgId) => {
  $(`#${modalId}`).fadeOut("slow", function () {
    $(`#${modalImgId}`).attr("src", "");
    $("html").css({
      overflow: "visible",
    });
  });
};

window.previewImage = (input, imgID) => {
  const file = input.files[0];
  const reader = new FileReader();

  reader.onload = function (e) {
    $(`#${imgID}`).attr("src", e.target.result);
  };

  if (file) {
    reader.readAsDataURL(file);
  }
};

window.imageOnChange = (el, imgID) => {
  previewImage(el, imgID);
};

window.resetImage = (inputID, imgID, imgSrc) => {
  $(`#${inputID}`).val(null);
  $(`#${imgID}`).attr("src", imgSrc);
};

window.showBtnLoading = (buttonEl, enableLoading = true) => {
  const loadingEl = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>`;

  if (enableLoading) {
    buttonEl.addClass("disabled");
    buttonEl.prepend(loadingEl);
    $(buttonEl.children()[1]).addClass("d-none");
  } else {
    buttonEl.removeClass("disabled");
    $(buttonEl.children()[1]).removeClass("d-none");
    $(buttonEl.children()[0]).remove();
  }
};

window.alert = async ({ title, html, icon, theme, ...props }) => {
  await swal.fire({ title, html, icon, theme, ...props });
};

window.showPlacement = (el) => {
  el.html(`<div class="loading">
              <p class="card-text placeholder-glow">
                <span class="placeholder col-7 placeholder-xs"></span>
                <span class="placeholder col-4 placeholder-xs"></span>
                <span class="placeholder col-4 placeholder-xs"></span>
                <span class="placeholder col-2 placeholder-xs"></span>
              </p>
            </div>`);
};

window.handleZoom = (action) => {
  let zoomLevel = parseFloat(document.body.style.zoom) || 1;

  if (action == "in") {
    zoomLevel += 0.1;
  } else {
    zoomLevel = Math.max(zoomLevel - 0.1, 0.1);
  }
  document.body.style.zoom = zoomLevel;
  resetToolTip();
};

window.resetToolTip = () => {
  tooltipList.forEach(function (tooltip) {
    if (tooltip._element) {
      tooltip.dispose();
    }
  });
};
