<div class="row mb-2">
  <div class="col-6 col-md-6">
    <h1 class="h3">Edit User</h1>
  </div>
  <div class="col-6 col-md-6 text-end">
    <button type="button" class="btn btn-secondary text-white" onclick="window.location.replace(`<?= SERVER_NAME . '/views/users' ?>`)">
      <i class="bi bi-chevron-left me-1"></i>
      Go Back
    </button>
  </div>
</div>

<div class="card">
  <form id="editUserForm" method="POST" novalidate>
    <input type="hidden" name="id" value="<?= $user->id ?>" readonly>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-12">
          <div class="d-flex align-items-start align-items-sm-center gap-4">
            <img src="<?= $helpers->get_avatar_link($user->id) ?>" alt="user-avatar" class="d-block rounded" height="100" width="100" style="object-fit: cover" id="uploadedUser">

            <div class="button-wrapper">
              <label for="img_upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                <span class="d-none d-sm-block">Upload new photo</span>
                <i class="fa-solid fa-upload d-block d-sm-none"></i>
                <input type="file" class="account-file-input" id="img_upload" accept="image/png, image/jpeg" name="img_upload" onchange="inputOnChange(this, 'uploadedUser')" hidden="">
              </label>

              <button type="button" class="btn btn-outline-danger account-image-reset mb-4 " id="btnReset" onclick="resetInputImg('img_upload', 'uploadedUser', '<?= $helpers->default_avatar ?>')">
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
          <input type="text" class="form-control" aria-label="First name" name="fname" value="<?= $user->fname ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Last Name</label>
          <input type="text" class="form-control" aria-label="Last name" name="lname" value="<?= $user->lname ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Contact</label>
          <input type="text" class="form-control" aria-label="First name" name="contact" value="<?= $user->contact ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" aria-label="Email" name="email" value="<?= $user->email ?>" required>
        </div>
        <div class="col-md-12">
          <label class="form-label">Address</label>
          <input type="text" class="form-control" aria-label="Address" name="address" value="<?= $user->address ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            <option value="" selected disabled>-- select role --</option>
            <?php
            $roles = ["super_admin" => "Supper Admin", "admin" => "Admin"];
            foreach ($roles as $value => $name) : ?>
              <option value="<?= $value ?>" <?= $user->role == $value ? "selected" : "" ?>><?= $name ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label me-2">Status:</label>
          <div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" id="activeUser" type="radio" name="status" value="1" <?= $user->status == "1" ? "checked" : "" ?> required>
              <label class="form-check-label" for="activeUser">Active</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" id="inactiveUser" type="radio" name="status" value="0" <?= $user->status == "0" ? "checked" : "" ?> >
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
                <option value="<?= $company->id ?>" data-image="<?= $helpers->get_company_img_link($company->id) ?>" <?= $user->company_id == $company->id ? "selected" : "" ?>>
                  <?= $company->name ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>

          </select>

          <!-- <input type="text" class="form-control" aria-label="Company" name="company"> -->
        </div>
        <div class="col-md-6">
          <label class="form-label">Position</label>
          <input type="text" class="form-control" aria-label="Position" name="position" value="<?= $user->position ?>">
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
  let isImageReset = false;

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

  $("#editUserForm").validate({
    submitHandler: function() {
      let fd = new FormData($("#editUserForm").get(0));
      fd.append("action", "edit_user");
      fd.append("isImageReset", isImageReset);

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
            window.location.reload();
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

  function inputOnChange(el, imgID) {
    imageOnChange(el, imgID);
    isImageReset = false;
  }

  function resetInputImg(inputID, imgID, imgSrc) {
    resetImage(inputID, imgID, imgSrc);
    isImageReset = true;
  }
</script>