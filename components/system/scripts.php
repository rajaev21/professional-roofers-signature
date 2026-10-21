<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (isset($_SESSION["user_id"])) : ?>
  <script src="<?= SERVER_NAME . "/assets/vendors/@coreui/coreui/dist/js/coreui.bundle.min.js" ?>"></script>
  <script src="<?= SERVER_NAME . "/assets/vendors/simplebar/dist/simplebar.min.js" ?>"></script>
<?php endif; ?>
<script src="<?= SERVER_NAME . "/assets/vendors/simplebar/dist/simplebar.min.js" ?>"></script>

<script>
  $(document).ajaxSend(function(event, xhr, settings) {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    if (csrfToken) {
      xhr.setRequestHeader('X-CSRF-Token', csrfToken);
    }
  });

  var isPageLoading = null;
  $(document).on("ajaxStart ajaxStop ajaxComplete", (event, xhr, settings) => {
    let isLoading = null;

    if (isPageLoading !== null) {
      isLoading = isPageLoading;
      isPageLoading = null;
      isLoading = null;
    } else {
      isLoading = event.type === "ajaxStart";
    }
    $("#loadingDiv").toggle(isLoading);
    $("button").prop("disabled", isLoading);

    if (event.type === "ajaxComplete") {
      const newCsrfToken = xhr.getResponseHeader('X-CSRF-Token');
      if (newCsrfToken) {
        $('meta[name="csrf-token"]').attr('content', newCsrfToken);
      }
    }
  });

  $(document).on("ajaxError", (event, jqxhr, settings, thrownError) => {
    console.error("AJAX request failed:", thrownError);
  });

  const header = document.querySelector('header.header');

  document.addEventListener('scroll', () => {
    if (header) {
      header.classList.toggle('shadow-sm', document.documentElement.scrollTop > 0);
    }
  });
</script>