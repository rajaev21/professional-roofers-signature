$(document).ready(function () {
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // new DataTable(".datatable", {
  //   responsive: true,
  //   ordering: false,
  // });

  $(".toggle-password").on("click", function () {
    $(this)
      .siblings("input")
      .attr("type", function (index, attr) {
        return attr === "password" ? "text" : "password";
      });
    $(this).toggleClass("fa-eye fa-eye-slash");
  });

  $("td").each(function () {
    if (!$(this).text()) {
      $(this).html("<em class='text-muted'>--</em>");
    }
  });

  $("[required]").each(function () {
    const asterisk = ` <span class="text-danger">*</span>`;
    if ($(this).prop("type") === "radio") {
      $(this).parent().parent().siblings(".form-label").append(asterisk);
    } else {
      $(this).parent().find(".form-label").append(asterisk);
    }
  });
});
