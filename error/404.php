<?php
include(__DIR__ . "/../backend/nodes.php");
$pageTitle = "404 Not Found";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php include(__DIR__ . "/../components/system/head.php"); ?>
</head>

<body>
  <div class="bg-body-tertiary min-vh-100 d-flex flex-row align-items-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="clearfix">
            <h1 class="float-start display-3 me-4">404</h1>
            <h4 class="pt-3">Oops! You're lost.</h4>
            <p class="text-body-secondary">The page you are looking for was not found.</p>
            <button type="button" class="btn btn-link" onclick="window.history.back()">
              <i class="bi bi-chevron-left mx-2"></i>
              Go Back
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

<?php include(__DIR__ . "/../components/system/scripts.php") ?>

</html>