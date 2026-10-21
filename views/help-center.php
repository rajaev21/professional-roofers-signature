<?php
include("../backend/nodes.php");
include("../components/system/config.php");

$pageTitle = "Help Center";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php include("../components/system/head.php") ?>
</head>

<body>
  <?php include("./components/side-bar.php") ?>
  <div class="wrapper d-flex flex-column min-vh-100">
    <?php include("./components/header.php") ?>

    <div class="body flex-grow-1">
      <?php

      if (isset($_GET["page"])):
        $page = $_GET["page"];
        if ($page == "add" || $page == "edit") {
          $pageTitle = $page == "add" ? "Add Item" : "Edit Item";
          include("./components/help-center/add.php");
        } else if ($page == "view") {
          $item_id = $helpers->decrypt($_GET["id"]);
          $help_center = $helpers->select_all_with_params("help_center", "id = " . $item_id);
          $category_id = $helpers->decrypt($_GET["category"]);
          $help_center_categories = $helpers->select_all_individual("help_center_categories", "id = '$category_id'");
          $pageTitle = "View " . $help_center_categories->formatted_category;
          include("./components/help-center/view.php");
        } else {
          http_response_code(404);
          header("Location:" . SERVER_NAME . "/error/404");
          exit();
        }

      elseif ($current_user->role == "admin"):
        include("./components/help-center/admin-view.php");
      ?>

      <?php else: ?>
        <div class="container-lg px-4 mb-3">
          <div class="row mb-2">
            <div class="col-6 col-md-6">
              <h1 class="h3"><?= $pageTitle ?></h1>
            </div>
            <div class="col-6 col-md-6 text-end <?= $current_user->role == "super_admin" ? "" : "d-none" ?>">
              <button class="btn btn-success text-white btn-sm" id="addCategory">
                <i class="bi bi-folder-plus"></i> Add Category
              </button>
            </div>
          </div>

          <div class="row">
            <div class="col-12 ">
              <div class="card">
                <div class="card-header d-flex justify-content-end align-items-center flex-wrap gap-2 mb-3">
                  <ul class="nav nav-tabs card-header-tabs me-auto" role="tablist">
                    <li class="nav-item" role="presentation" style="position: relative; overflow:visible;">
                      <button class="nav-link active" id="tab-recently-added" data-bs-toggle="tab" data-bs-target="#recently-added" type="button" role="tab">
                        Recently Added
                      </button>
                    </li>
                    <?php
                    $categories = $helpers->select_all_with_params("help_center_categories", "is_archived = 0 order by is_pinned desc");
                    if (count($categories) > 0):
                      foreach ($categories as $category):
                    ?>
                        <li class="nav-item" role="presentation" style="position: relative; overflow:visible;">
                          <button class="btn p-0 <?= $current_user->role == "super_admin" ? "" : "d-none" ?>" style="position: absolute; right: -8px;top: -12px; z-index: 50;" onclick="handleArchiveCategory('<?= $category->id ?>')">
                            <i class="bi bi-x-circle-fill text-danger"></i>
                          </button>
                          <button class="nav-link" id="tab-<?= $category->category ?>" data-bs-toggle="tab" data-bs-target="#<?= $category->category ?>" type="button" role="tab">
                            <?= $category->formatted_category ?>
                          </button>
                        </li>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </ul>
                </div>
                <div class="card-body">
                  <div class="tab-content">
                    <div class="tab-pane fade show active" role="tabpanel" aria-labelledby="tab-recently-added" id="recently-added">
                      <div class="table-responsive">
                        <table id="tableRecentlyAdded" class="table table-striped">
                          <thead>
                            <tr>
                              <th>Category</th>
                              <th>Title</th>
                              <th>Description</th>
                              <th>Created Date</th>
                              <th>Last Updated</th>
                              <th>Type</th>
                              <th>Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                            $recently_added = $helpers->custom_query_multiple("select hc.id as id, hc.title, hc.description, hc.date_created, hc.last_updated, hc.content, hc.pdf_file, hc.video_file, hc.external_link, hc.category_id, hcc.formatted_category as formatted_category from help_center hc right join help_center_categories hcc on hc.category_id = hcc.id where hc.is_archived = 0 and hcc.is_archived = 0 order by hc.id desc limit 10");
                            if (count($recently_added) > 0):
                              foreach ($recently_added as $recent):
                                $encrypted_category_id = $helpers->encrypt($recent->category_id);
                                $encrypted_recent_id = $helpers->encrypt($recent->id);
                                $recent_date_created = date("F j, Y H:i A", strtotime($recent->date_created));
                                $recent_last_updated = date("F j, Y H:i A", strtotime($recent->last_updated));
                            ?>
                                <tr>
                                  <td> <?= $recent->formatted_category ?> </td>
                                  <td> <?= $recent->title ?> </td>
                                  <td> <?= $recent->description ?> </td>
                                  <td> <?= $recent_date_created ?> </td>
                                  <td> <?= $recent_last_updated ?> </td>
                                  <td>
                                    <?php if ($recent->content): ?>
                                      <i class="bi bi-file-text"></i>
                                    <?php endif; ?>
                                    <?php if ($recent->pdf_file): ?>
                                      <i class="bi bi-file-pdf"></i>
                                    <?php endif; ?>
                                    <?php if ($recent->video_file): ?>
                                      <i class="bi bi-camera-video"></i>
                                    <?php endif; ?>
                                  </td>
                                  <td>
                                    <a href="<?= SERVER_NAME . "/views/help-center?page=view&category=" . $encrypted_category_id . "&id=" . $encrypted_recent_id ?>" class="btn btn-primary text-white m-1">
                                      <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= SERVER_NAME . "/views/help-center?page=edit&category=" . $encrypted_category_id . "&id="  . $encrypted_recent_id ?>" class="btn btn-warning m-1 <?= $current_user->role == "super_admin" ? "" : "d-none" ?>">
                                      <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger m-1 <?= $current_user->role == "super_admin" ? "" : "d-none" ?>" onclick="handleToggleArchive('<?= $recent->id ?>')">
                                      <i class="bi bi-archive"></i>
                                    </button>
                                  </td>
                                </tr>
                              <?php endforeach; ?>
                            <?php endif; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <?php
                    if (count($categories) > 0):
                      foreach ($categories as $category):
                    ?>
                        <div class="tab-pane fade" id="<?= $category->category ?>" role="tabpanel" aria-labelledby="tab-<?= $category->category ?>">
                          <div class="text-end">
                            <a href="<?= SERVER_NAME . "/views/help-center?page=add&category=" . $helpers->encrypt($category->id) ?>" class="btn btn-success text-white mb-2">
                              <i class="fa-solid fa-plus me-1"></i>
                              Add <?= $category->formatted_category ?>
                            </a>
                          </div>
                          <div class="table-responsive">
                            <table id="<?= $category->category ?>" class="table table-striped">
                              <thead>
                                <tr>
                                  <th>Title</th>
                                  <th>Description</th>
                                  <th>Created Date</th>
                                  <th>Last Updated</th>
                                  <th>Type</th>
                                  <th>Action</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php
                                $help_center_items = $helpers->select_all_with_params("help_center", "is_archived = 0 and category_id = '$category->id'");
                                if (count($help_center_items) > 0):
                                  foreach ($help_center_items as $item):
                                    $encrypted_category_id = $helpers->encrypt($item->category_id);
                                    $encrypted_item_id = $helpers->encrypt($item->id);
                                    $formatted_date_created = date("F j, Y H:i A", strtotime($item->date_created));
                                    $formatted_last_updated = date("F j, Y H:i A", strtotime($item->last_updated));
                                ?>
                                    <tr>
                                      <td> <?= $item->title ?> </td>
                                      <td> <?= $item->description ?> </td>
                                      <td> <?= $formatted_date_created ?> </td>
                                      <td> <?= $formatted_last_updated ?> </td>
                                      <td>
                                        <?php if ($item->content): ?>
                                          <i class="bi bi-file-text"></i>
                                        <?php endif; ?>
                                        <?php if ($item->pdf_file): ?>
                                          <i class="bi bi-file-pdf"></i>
                                        <?php endif; ?>
                                        <?php if ($item->video_file): ?>
                                          <i class="bi bi-camera-video"></i>
                                        <?php endif; ?>
                                      </td>
                                      <td>
                                        <a href="<?= SERVER_NAME . "/views/help-center?page=view&category=" . $encrypted_category_id . "&id=" . $encrypted_item_id ?>" class="btn btn-primary text-white m-1">
                                          <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= SERVER_NAME . "/views/help-center?page=edit&category=" . $encrypted_category_id . "&id="  . $encrypted_item_id ?>" class="btn btn-warning m-1 <?= $current_user->role == "super_admin" ? "" : "d-none" ?>">
                                          <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger m-1 <?= $current_user->role == "super_admin" ? "" : "d-none" ?>" onclick="handleToggleArchive('<?= $item->id ?>')">
                                          <i class="bi bi-archive"></i>
                                        </button>
                                      </td>
                                    </tr>
                                  <?php endforeach; ?>
                                <?php else: ?>
                                  <tr>
                                    <td colspan="6" class="text-center">
                                      No Items found.
                                    </td>
                                  </tr>
                                <?php endif; ?>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <?php include("./components/footer.php") ?>
  </div>

</body>
<?php include("../components/system/scripts.php") ?>

<script>
  $(document).ready(function() {
    new DataTable("#tableRecentlyAdded", {
      responsive: true,
    });
  });

  function handleArchiveCategory(category_id) {
    swal.fire({
      title: 'Are you sure?',
      text: 'Do you want to archive this category and all its items?',
      icon: 'warning',
      dangerMode: true,
      showDenyButton: true,
      confirmButtonText: 'Yes',
      denyButtonText: 'Cancel'
    }).then((archive) => {
      if (archive.isConfirmed) {
        swal.showLoading();

        $.ajax({
          url: '<?= SERVER_NAME . '/backend/nodes' ?>',
          type: 'POST',
          data: {
            action: 'archive_help_center_category',
            category_id: category_id
          },
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              swal.fire('Success', 'Category archived successfully.', 'success').then(() => {
                location.reload();
              });
            } else {
              swal.fire('Error', 'Failed to archive category: ' + response.message, 'error');
            }
          },
          error: function() {
            swal.fire('Error', 'An error occurred while archiving the category.', 'error');
          }
        });
      }
    });
  }

  function handleToggleArchive(item_id) {
    swal.fire({
      title: 'Are you sure?',
      text: 'Do you want to archive this item?',
      icon: 'warning',
      dangerMode: true,
      showDenyButton: true,
      confirmButtonText: 'Yes',
      denyButtonText: 'Cancel'
    }).then((archive) => {
      if (archive.isConfirmed) {
        swal.showLoading();

        $.ajax({
          url: '<?= SERVER_NAME . '/backend/nodes' ?>',
          type: 'POST',
          data: {
            action: 'archive_item',
            item_id: item_id
          },
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              swal.fire('Success', 'Item archived successfully.', 'success').then(() => {
                location.reload();
              });
            } else {
              swal.fire('Error', 'Failed to archive item: ' + response.message, 'error');
            }
          },
          error: function() {
            swal.fire('Error', 'An error occurred while archiving the item.', 'error');
          }
        });
      }
    });
  }

  $("#addCategory").on("click", function(e) {
    swal.fire({
        text: "Category name",
        input: "text",
        confirmButtonText: `Submit`,
        showDenyButton: true,
        denyButtonText: `Cancel`,
        showLoaderOnConfirm: true,
        preConfirm: async (value) => {
          const res = await $.ajax({
            url: '<?= SERVER_NAME . '/backend/nodes' ?>',
            data: {
              action: 'add_help_center_category',
              category: value,
            },
            type: "POST",
            dataType: "JSON",
            success: function(resp) {
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
              title: "Success",
              html: d.value.message,
              icon: "success",
            })
            .then(() => window.location.reload());
        }
      });
  })
</script>

</html>