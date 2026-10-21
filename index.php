<?php
include("./backend/nodes.php");
$pageTitle = "Login";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php include(__DIR__ . "/components/system/head.php"); ?>
</head>

<body>
  <?php include(__DIR__ . "/components/loading-screen.php") ?>

  <div class="bg-body-tertiary min-vh-100 d-flex flex-row align-items-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-5">
          <div class="card ">
            <div class="card-body">
              <div class="row">
                <div class="col-md-12 d-flex justify-content-center align-items-center">
                  <img src="<?= SERVER_NAME . "/images/professional-roofers-logo-dark.png" ?>" style="max-width: 280px; height: auto;" alt="Logo">
                </div>
              </div>
              <h5 class="card-title text-center">Log in to your account</h5>
              <div class="text-body-secondary mb-3 text-center">
                Enter your email & password to log in
              </div>
              <form id="form-login" method="POST">
                <div class="form-group mb-1">
                  <label class="form-label">Email</label>
                  <input type="email" class="form-control" name="email" value="<?= $_SESSION["user_email"] ?? ""  ?>" required>
                </div>

                <div class="form-group mb-1">
                  <label for="password" class="form-label">Password</label>
                  <div class="password-wrapper">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <i class="toggle-password fa fa-eye-slash"></i>
                  </div>
                </div>

                <div class="mb-3 text-end">
                  <button class="btn btn-link px-0" type="button">Forgot password?</button>
                </div>

                <div class="d-grid gap-2">
                  <button class="btn btn-success btn-block px-4 text-white" type="submit">
                    Log in
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>
<?php include(__DIR__ . "/components/system/scripts.php") ?>

<script>
  $("#form-login").on("submit", function(e) {
    e.preventDefault();
    let fd = new FormData(this);
    fd.append("action", "login");

    $.ajax({
      type: "POST",
      url: "<?= SERVER_NAME . "/backend/nodes" ?>",
      processData: false,
      contentType: false,
      cache: false,
      data: fd,
      dataType: 'JSON',
      success: (response) => {
        if (response.success) {
          window.location.href = response.redirect;
        } else {
          alert({
            icon: 'error',
            title: 'Error',
            html: response.message,
            theme: $("html").attr("data-bs-theme"),
          });
        }
      },
      error: () => {
        alert({
          icon: 'error',
          title: 'Oops...',
          text: 'Something went wrong!',
          theme: $("html").attr("data-bs-theme"),
        });
      }
    });
  })
</script>

</html>