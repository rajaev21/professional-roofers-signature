  <div class="row mb-2">
    <div class="col-6 col-md-6">
      <h1 class="h3">Add Company</h1>
    </div>
    <div class="col-6 col-md-6 text-end">
      <button type="button" class="btn btn-secondary text-white" onclick="window.location.replace(`<?= SERVER_NAME . '/views/companies' ?>`)">
        <i class="bi bi-chevron-left me-1"></i>
        Go Back
      </button>
    </div>
  </div>

  <div class="card">
    <form id="addCompanyForm" method="POST" novalidate>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-12">
            <div class="d-flex align-items-start align-items-sm-center gap-4">
              <img src="<?= $helpers->get_company_img_link(null) ?>" alt="user-avatar" class="d-block rounded" height="100" width="100" style="object-fit: cover" id="uploadedCompany">

              <div class="button-wrapper">
                <label for="img_upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                  <span class="d-none d-sm-block">Upload new photo</span>
                  <i class="fa-solid fa-upload d-block d-sm-none"></i>
                  <input type="file" class="account-file-input" id="img_upload" accept="image/png, image/jpeg" name="img_upload" onchange="imageOnChange(this, 'uploadedCompany')" hidden="">
                </label>

                <button type="button" class="btn btn-outline-danger account-image-reset mb-4 " id="btnReset" onclick="resetImage('img_upload', 'uploadedCompany', '<?= $helpers->get_company_img_link(null) ?>')">
                  <i class="fa-solid fa-arrow-rotate-left d-block d-sm-none"></i>
                  <span class="d-none d-sm-block">Reset</span>
                </button>
                <p class="text-muted mb-0">Allowed JPG or PNG</p>
              </div>
            </div>
          </div>
          <hr>

          <div class="col-md-6">
            <label class="form-label">Company name</label>
            <input type="text" class="form-control" aria-label="Company name" name="name" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">License</label>
            <input type="text" class="form-control" aria-label="License" name="license">
          </div>

          <div class="col-md-12">
            <label class="form-label">Address</label>
            <input type="text" class="form-control" aria-label="Address" name="address" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Contact</label>
            <input type="text" class="form-control" aria-label="Contact" name="contact" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" aria-label="Email" name="email" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Number of Employee</label>
            <input type="number" class="form-control" aria-label="Number of Employee" name="number_of_employee">
          </div>

          <div class="col-md-6">
            <label class="form-label">Website</label>
            <input type="text" class="form-control" aria-label="Website" name="website">
          </div>
        </div>
      </div>

      <div class="card-footer text-end">
        <button type="submit" class="btn btn-success text-white">
          <i class="fa-solid fa-check me-1"></i>
          Save
        </button>

        <button type="button" class="btn btn-danger" onclick="window.location.replace(`<?= SERVER_NAME . '/views/companies' ?>`)">
          <i class="fa-regular fa-circle-xmark"></i>
          Cancel
        </button>
      </div>
    </form>
  </div>

  <script>
    $("#addCompanyForm").validate({
      submitHandler: function() {
        const formData = new FormData($("#addCompanyForm").get(0));
        formData.append("action", "add_company");

        $.ajax({
          url: "<?= SERVER_NAME . "/backend/nodes" ?>",
          type: "POST",
          data: formData,
          contentType: false,
          processData: false,
          enctype: "multipart/form-data",
          cache: false,
          dataType: "JSON",
          success: function(res) {
            if (res.status === "success") {
              window.location.href = "<?= SERVER_NAME . "/views/companies" ?>";
            } else {
              alert({
                title: "Error",
                html: res.message,
                icon: "error",
              })
            }
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert({
              title: "Error",
              html: `Error: ${textStatus} <br> ${errorThrown}`,
              icon: "error",
            })
          }
        });
      },
      errorElement: "span",
      errorPlacement: function(error, element) {
        error.addClass("invalid-feedback").insertAfter(element)
      },
      highlight: function(element) {
        $(element).removeClass('is-valid').addClass('is-invalid');
      },
      unhighlight: function(element) {
        $(element).removeClass('is-invalid');
      },
      rules: {
        name: {
          required: true,
          minlength: 2
        },
        address: {
          required: true,
          minlength: 5
        },
        contact: {
          required: true,
        },
        email: {
          required: true,
          email: true
        }
      },
      messages: {
        name: {
          required: "Please enter company name",
          minlength: "Company name must be at least 2 characters long"
        },
        address: {
          required: "Please enter address",
          minlength: "Address must be at least 5 characters long"
        },
        contact: {
          required: "Please enter contact number",
        },
        email: {
          required: "Please enter email address",
          email: "Please enter a valid email address"
        }
      }
    });
  </script>