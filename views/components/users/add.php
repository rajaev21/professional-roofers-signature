<div class="row mb-2">
  <div class="col-6 col-md-6">
    <h1 class="h3">Add User</h1>
  </div>
  <div class="col-6 col-md-6 text-end">
    <button type="button" class="btn btn-secondary text-white" onclick="window.location.replace(`<?= SERVER_NAME . '/views/users' ?>`)">
      <i class="bi bi-chevron-left me-1"></i>
      Go Back
    </button>
  </div>
</div>

<div class="card">
  <form id="addUserForm" method="POST" novalidate>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-12">
          <div class="d-flex align-items-start align-items-sm-center gap-4">
            <img src="<?= $helpers->default_avatar ?>" alt="user-avatar" class="d-block rounded" height="100" width="100" style="object-fit: cover" id="uploadedUser">

            <div class="button-wrapper">
              <label for="img_upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                <span class="d-none d-sm-block">Upload new photo</span>
                <i class="fa-solid fa-upload d-block d-sm-none"></i>
                <input type="file" class="account-file-input" id="img_upload" accept="image/png, image/jpeg" name="img_upload" onchange="imageOnChange(this, 'uploadedUser')" hidden="">
              </label>

              <button type="button" class="btn btn-outline-danger account-image-reset mb-4 " id="btnReset" onclick="resetImage('img_upload', 'uploadedUser', '<?= $helpers->default_avatar ?>')">
                <i class="fa-solid fa-arrow-rotate-left d-block d-sm-none"></i>
                <span class="d-none d-sm-block">Reset</span>
              </button>
              <p class="text-muted mb-0">Allowed JPG or PNG</p>
            </div>
          </div>
        </div>
        <hr>

        <div class="col-md-6">
          <label class="form-label">First name</label>
          <input type="text" class="form-control" aria-label="First name" name="fname" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Last Name</label>
          <input type="text" class="form-control" aria-label="Last name" name="lname" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Contact</label>
          <input type="text" class="form-control" aria-label="First name" name="contact" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" aria-label="Email" name="email" required>
        </div>
        <div class="col-md-12">
          <label class="form-label">Address</label>
          <input type="text" class="form-control" aria-label="Address" name="address" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            <option value="" selected disabled>-- select role --</option>
            <?php if ($current_user->role == "super_admin"): ?>
              <option value="super_admin">Super Admin</option>
            <?php endif; ?>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label me-2">Status:</label>
          <div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" id="activeUser" type="radio" name="status" value="1" required>
              <label class="form-check-label" for="activeUser">Active</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" id="inactiveUser" type="radio" name="status" value="0">
              <label class="form-check-label" for="inactiveUser">Inactive</label>
            </div>
          </div>
        </div>
        <div class="col-md-6" id="companyField">
          <label class="form-label">Company</label>
          <select name="company" class="form-select" id="companySelect">
            <option value="">-- select company --</option>
            <?php
            $companies = $helpers->select_custom_fields("companies", ["id", "name"]);
            if (count($companies) > 0) :
              foreach ($companies as $company) :
            ?>
                <option value="<?= $company->id ?>" data-image="<?= $helpers->get_company_img_link($company->id) ?>">
                  <?= $company->name ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>

          </select>

          <!-- <input type="text" class="form-control" aria-label="Company" name="company"> -->
        </div>
        <div class="col-md-6">
          <label class="form-label">Position</label>
          <input type="text" class="form-control" aria-label="Position" name="position">
        </div>

        <div class="d-flex align-items-center mt-4">
          <hr class="flex-grow-1 me-3">
          <span class="text-muted text-nowrap">Password</span>
          <hr class="flex-grow-1 ms-3">
        </div>

        <div class="col-md-6">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" aria-label="Password" name="password" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Confirm Password</label>
          <input type="password" class="form-control" aria-label="Confirm Password" name="confirm_password" required>
        </div>

        <div class="col-12">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="showPassword">
            <label class="form-check-label" for="showPassword">Show password</label>
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer text-end">
      <button type="submit" class="btn btn-success text-white">
        <i class="fa-solid fa-check me-1"></i>
        Save
      </button>

      <button type="button" class="btn btn-danger" onclick="window.location.replace(`<?= SERVER_NAME . '/views/users' ?>`)">
        <i class="fa-regular fa-circle-xmark"></i>
        Cancel
      </button>
    </div>
  </form>
</div>

<script>
  $('#showPassword').on('change', function() {
    const type = $(this).is(':checked') ? 'text' : 'password';
    $('input[name="password"], input[name="confirm_password"]').attr('type', type);
  });

  $('#companySelect').select2({
    templateResult: formatOptionWithImage,
    theme: "bootstrap-5",
    placeholder: "-- select company --",
    allowClear: true,
    escapeMarkup: function(markup) {
      return markup;
    }
  }).on('select2:unselecting', function(e) {
    $(this).data('state', 'unselected');
  }).on('select2:open', function(e) {
    if ($(this).data('state') === 'unselected') {
      $(this).removeData('state');
      $(this).select2('close');
    }
  });

  function formatOptionWithImage(option) {
    if (!option.id) {
      return option.text;
    }
    var image = $(option.element).data('image');
    if (image) {
      return `<span>
                <img src="${image}" class="rounded" style="width: 45px; height: 45px; object-fit: cover; margin-right: 20px;">
                ${option.text}
              </span>`;
    }
    return option.text;
  }

  $("#addUserForm").validate({
    submitHandler: function() {
      let fd = new FormData($("#addUserForm").get(0));
      fd.append("action", "add_user");

      $.ajax({
        type: "POST",
        url: "<?= SERVER_NAME . "/backend/nodes" ?>",
        processData: false,
        contentType: false,
        cache: false,
        data: fd,
        enctype: "multipart/form-data",
        cache: false,
        dataType: "JSON",
        success: (res) => {
          if (res.success) {
            window.location.href = "<?= SERVER_NAME . "/views/users" ?>";
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
    ignore: "",
    rules: {
      content: "required",
      password: {
        required: true,
        minlength: 8
      },
      confirm_password: {
        required: true,
        equalTo: 'input[name="password"]'
      }
    },
    messages: {
      password: {
        required: "Please provide a password",
        minlength: "Password must be at least 8 characters long"
      },
      confirm_password: {
        required: "Please confirm your password",
        equalTo: "Passwords do not match"
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
</script>