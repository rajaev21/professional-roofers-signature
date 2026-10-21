<?php
include("../backend/nodes.php");
include("../components/system/config.php");

$pageTitle = "Templates";
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
        if ($page == "choices") {
          include("./components/templates/choices.php");
        } else if ($page == "edit") {
          $id = $_GET["id"];
          $decrypted_id = $helpers->decrypt($id);

          $template = $helpers->select_all_individual("templates", "id = '$decrypted_id'");
          if ($template) {
            include("./components/templates/edit.php");
          } else {
            http_response_code(404);
            header("Location: " . SERVER_NAME . "/error/404");
            exit();
          }
        } else if ($page == "signature") {
          $id = $_GET["id"];
          $decrypted_id = $helpers->decrypt($id);

          $template = $helpers->select_all_individual("templates", "id = '$decrypted_id'");
          if ($template) {
            include("./components/templates/signature.php");
          } else {
            http_response_code(404);
            header("Location: " . SERVER_NAME . "/error/404");
            exit();
          }
        } else if ($page == "view") {
          $id = $_GET["id"];
          $decrypted_id = $helpers->decrypt($id);

          $template = $helpers->select_all_individual("templates", "id = '$decrypted_id'");
          if ($template) {
            include("./components/templates/view.php");
          } else {
            http_response_code(404);
            header("Location: " . SERVER_NAME . "/error/404");
            exit();
          }
        } else if ($page == "use") {
          $id = $_GET["id"];
          $decrypted_id = $helpers->decrypt($id);

          $template = $helpers->select_all_individual("templates", "id = '$decrypted_id'");
          if ($template) {
            include("./components/templates/use.php");
          } else {
            http_response_code(404);
            header("Location: " . SERVER_NAME . "/error/404");
            exit();
          }
        } else {
          http_response_code(404);
          header("Location: " . SERVER_NAME . "/error/404");
          exit();
        }
      ?>
      <?php else: ?>
        <div class="container">
          <div class="row mb-2">
            <div class="col-6 col-md-6">
              <h1 class="h3"><?= $pageTitle ?></h1>
            </div>
            <div class="col-6 col-md-6 text-end">
              <button type="button" class="btn btn-outline-secondary ms-2 mb-2" onclick="window.location.reload()">
                <i class="fa-solid fa-arrows-rotate me-1"></i>
                Refresh
              </button>
              <a href="<?= SERVER_NAME . "/views/templates?page=choices" ?>" class="btn btn-success text-white mb-2">
                <i class="fa-solid fa-plus me-1"></i>
                Add Template
              </a>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <ul class="nav nav-tabs d-flex justify-content-evenly mb-3" id="templateTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="published-tab" data-bs-toggle="tab" data-bs-target="#published" type="button" role="tab" aria-controls="published" aria-selected="true">
                    Published
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="draft-tab" data-bs-toggle="tab" data-bs-target="#draft" type="button" role="tab" aria-controls="draft" aria-selected="false">
                    Drafts
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="archived-tab" data-bs-toggle="tab" data-bs-target="#archived" type="button" role="tab" aria-controls="archived" aria-selected="false">
                    Archived
                  </button>
                </li>
              </ul>

              <div class="tab-content mb-3" id="legalTabsContent">
                <?php
                $params = "id IS NOT NULL";

                if ($current_user->role === "admin") {
                  $params .= " AND created_by = '$current_user->id'";
                }

                $templateData = $helpers->select_all_with_params("templates", "$params ORDER BY id DESC");

                $publishedData = array_filter($templateData, function ($template) {
                  return $template->document_status === 'published';
                });
                $draftData = array_filter($templateData, function ($template) {
                  return $template->document_status === 'draft';
                });
                $archivedData = array_filter($templateData, function ($template) {
                  return $template->document_status === 'archived';
                });

                ?>
                <div class="tab-pane fade show active" id="published" role="tabpanel" aria-labelledby="published-tab">
                  <div class="table-responsive">
                    <table class="table table-striped" id="templatesTable">
                      <thead>
                        <tr>
                          <th class="d-table-cell d-md-none">More</th>
                          <th>Name</th>
                          <th>Created By</th>
                          <th>Date Created</th>
                          <th class="d-none d-md-table-cell">Last Updated</th>
                          <th class="d-none d-md-table-cell">Status</th>
                          <th class="d-none d-md-table-cell">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        if (count($publishedData) > 0):
                          foreach ($publishedData as $template):
                            $created_by = $helpers->get_user_by_id($template->created_by);
                            $encrypted_id = $helpers->encrypt($template->id);
                        ?>
                            <tr>
                              <td class="d-table-cell d-md-none"><button class="btn btn-transparent btn-sm" onclick="viewMore(this)"> <i class="bi bi-three-dots"></i></button></td>
                              <td><?= $template->name ?></td>
                              <td><?= "$created_by->fname $created_by->lname" ?></td>
                              <td><?= date("F j, Y", strtotime($template->date_created)) ?></td>
                              <td class="d-none d-md-table-cell"><?= date("F j, Y", strtotime($template->date_updated)) ?></td>
                              <td class="d-none d-md-table-cell">
                                <?php if ($template->document_status === 'published'): ?>
                                  <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i> Published
                                  </span>
                                <?php elseif ($template->document_status === 'draft'): ?>
                                  <span class="badge bg-secondary">
                                    <i class="bi bi-pencil me-1"></i> Draft
                                  </span>
                                <?php elseif ($template->document_status === 'archived'): ?>
                                  <span class="badge bg-danger">
                                    <i class="bi bi-archive me-1"></i> Archived
                                  </span>
                                <?php endif; ?>
                              </td>
                              <td class="d-none d-md-table-cell">
                                <a href="<?= SERVER_NAME . "/views/templates?page=signature&id=$encrypted_id" ?>" class="btn btn-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Edit">
                                  <i class="bi bi-pencil-square"></i>
                                </a>

                                <?php if ($template->document_status == "published"): ?>
                                  <a href="<?= SERVER_NAME . "/views/templates?page=view&id=$encrypted_id" ?>" type="button" class="btn btn-primary my-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Preview">
                                    <i class="bi bi-eye"></i>
                                  </a>

                                  <a href="<?= SERVER_NAME . "/views/templates?page=use&id=$encrypted_id" ?>" type="button" class="btn btn-success my-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Use Template">
                                    <i class="bi bi-file-earmark-image"></i>
                                  </a>
                                <?php endif; ?>

                                <?php if (in_array($template->document_status, array("published", "draft"))): ?>
                                  <button type="button" class="btn btn-danger my-1 archive-template" onclick="handleToggleArchive('archive', '<?= $encrypted_id ?>')" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Archive">
                                    <i class="bi bi-archive"></i>
                                  </button>
                                <?php else: ?>
                                  <button type="button" class="btn btn-secondary my-1 unarchive-template" onclick="handleToggleArchive('unarchive', '<?= $encrypted_id ?>')" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Unarchive">
                                    <i class="bi bi-box-arrow-in-up"></i>
                                  </button>
                                <?php endif; ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <tr>
                            <td colspan="6" class="text-center text-muted">
                              There are no records to display.
                            </td>
                          </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="tab-pane fade" id="draft" role="tabpanel" aria-labelledby="draft-tab">
                  <div class="table-responsive">
                    <table class="table table-striped" id="templatesTable">
                      <thead>
                        <tr>
                          <th class="d-table-cell d-md-none">More</th>
                          <th>Name</th>
                          <th>Created By</th>
                          <th>Date Created</th>
                          <th class="d-none d-md-table-cell">Last Updated</th>
                          <th class="d-none d-md-table-cell">Status</th>
                          <th class="d-none d-md-table-cell">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        if (count($draftData) > 0):
                          foreach ($draftData as $template):
                            $created_by = $helpers->get_user_by_id($template->created_by);
                            $encrypted_id = $helpers->encrypt($template->id);
                        ?>
                            <tr>
                              <td class="d-table-cell d-md-none"><button class="btn btn-transparent btn-sm" onclick="viewMore(this)"> <i class="bi bi-three-dots"></i></button></td>
                              <td><?= $template->name ?></td>
                              <td><?= "$created_by->fname $created_by->lname" ?></td>
                              <td><?= date("F j, Y", strtotime($template->date_created)) ?></td>
                              <td class="d-none d-md-table-cell"><?= date("F j, Y", strtotime($template->date_updated)) ?></td>
                              <td class="d-none d-md-table-cell">
                                <?php if ($template->document_status === 'published'): ?>
                                  <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i> Published
                                  </span>
                                <?php elseif ($template->document_status === 'draft'): ?>
                                  <span class="badge bg-secondary">
                                    <i class="bi bi-pencil me-1"></i> Draft
                                  </span>
                                <?php elseif ($template->document_status === 'archived'): ?>
                                  <span class="badge bg-danger">
                                    <i class="bi bi-archive me-1"></i> Archived
                                  </span>
                                <?php endif; ?>
                              </td>
                              <td class="d-none d-md-table-cell">
                                <a href="<?= SERVER_NAME . "/views/templates?page=signature&id=$encrypted_id" ?>" class="btn btn-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Edit">
                                  <i class="bi bi-pencil-square"></i>
                                </a>

                                <?php if ($template->document_status == "published"): ?>
                                  <a href="<?= SERVER_NAME . "/views/templates?page=view&id=$encrypted_id" ?>" type="button" class="btn btn-primary my-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Preview">
                                    <i class="bi bi-eye"></i>
                                  </a>

                                  <a href="<?= SERVER_NAME . "/views/templates?page=use&id=$encrypted_id" ?>" type="button" class="btn btn-success my-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Use Template">
                                    <i class="bi bi-file-earmark-image"></i>
                                  </a>
                                <?php endif; ?>

                                <?php if (in_array($template->document_status, array("published", "draft"))): ?>
                                  <button type="button" class="btn btn-danger my-1 archive-template" onclick="handleToggleArchive('archive', '<?= $encrypted_id ?>')" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Archive">
                                    <i class="bi bi-archive"></i>
                                  </button>
                                <?php else: ?>
                                  <button type="button" class="btn btn-secondary my-1 unarchive-template" onclick="handleToggleArchive('unarchive', '<?= $encrypted_id ?>')" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Unarchive">
                                    <i class="bi bi-box-arrow-in-up"></i>
                                  </button>
                                <?php endif; ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <tr>
                            <td colspan="6" class="text-center text-muted">
                              There are no records to display.
                            </td>
                          </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="tab-pane fade" id="archived" role="tabpanel" aria-labelledby="archived-tab">
                  <div class="table-responsive">
                    <table class="table table-striped" id="templatesTable">
                      <thead>
                        <tr>
                          <th class="d-table-cell d-md-none">More</th>
                          <th>Name</th>
                          <th>Created By</th>
                          <th>Date Created</th>
                          <th class="d-none d-md-table-cell">Last Updated</th>
                          <th class="d-none d-md-table-cell">Status</th>
                          <th class="d-none d-md-table-cell">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        if (count($archivedData) > 0):
                          foreach ($archivedData as $template):
                            $created_by = $helpers->get_user_by_id($template->created_by);
                            $encrypted_id = $helpers->encrypt($template->id);
                        ?>
                            <tr>
                              <td class="d-table-cell d-md-none"><button class="btn btn-transparent btn-sm" onclick="viewMore(this)"> <i class="bi bi-three-dots"></i></button></td>
                              <td><?= $template->name ?></td>
                              <td><?= "$created_by->fname $created_by->lname" ?></td>
                              <td class="d-none d-md-table-cell"><?= date("F j, Y", strtotime($template->date_created)) ?></td>
                              <td class="d-none d-md-table-cell"><?= date("F j, Y", strtotime($template->date_updated)) ?></td>
                              <td>
                                <?php if ($template->document_status === 'published'): ?>
                                  <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i> Published
                                  </span>
                                <?php elseif ($template->document_status === 'draft'): ?>
                                  <span class="badge bg-secondary">
                                    <i class="bi bi-pencil me-1"></i> Draft
                                  </span>
                                <?php elseif ($template->document_status === 'archived'): ?>
                                  <span class="badge bg-danger">
                                    <i class="bi bi-archive me-1"></i> Archived
                                  </span>
                                <?php endif; ?>
                              </td>
                              <td class="d-none d-md-table-cell">
                                <a href="<?= SERVER_NAME . "/views/templates?page=signature&id=$encrypted_id" ?>" class="btn btn-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Edit">
                                  <i class="bi bi-pencil-square"></i>
                                </a>

                                <?php if ($template->document_status == "published"): ?>
                                  <a href="<?= SERVER_NAME . "/views/templates?page=view&id=$encrypted_id" ?>" type="button" class="btn btn-primary my-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Preview">
                                    <i class="bi bi-eye"></i>
                                  </a>

                                  <a href="<?= SERVER_NAME . "/views/templates?page=use&id=$encrypted_id" ?>" type="button" class="btn btn-success my-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Use Template">
                                    <i class="bi bi-file-earmark-image"></i>
                                  </a>
                                <?php endif; ?>

                                <?php if (in_array($template->document_status, array("published", "draft"))): ?>
                                  <button type="button" class="btn btn-danger my-1 archive-template" onclick="handleToggleArchive('archive', '<?= $encrypted_id ?>')" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Archive">
                                    <i class="bi bi-archive"></i>
                                  </button>
                                <?php else: ?>
                                  <button type="button" class="btn btn-secondary my-1 unarchive-template" onclick="handleToggleArchive('unarchive', '<?= $encrypted_id ?>')" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Unarchive">
                                    <i class="bi bi-box-arrow-in-up"></i>
                                  </button>
                                <?php endif; ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <tr>
                            <td colspan="6" class="text-center text-muted">
                              There are no records to display.
                            </td>
                          </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>


            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
    <?php
    if (!isset($_GET["page"]) || (isset($_GET["page"]) && $_GET["page"] != "signature")) {
      include("./components/footer.php");
    }
    ?>
  </div>
</body>
<?php include("../components/system/scripts.php") ?>
<script>
  function viewMore(btn) {
    $("tr.extra-row").remove();
    var th = $(btn).closest("table").find("th");
    var tr = $(btn).closest("tr");
    var detailsHtml = "";
    console.log(th)
    tr.find("td").each(function(index) {
      if (index < 4) return;
      detailsHtml += `<div><strong>${$(th[index]).text()}: </strong>${$(this).html()}</div>`;
    });

    tr.after(`
    <tr class="extra-row">
      <td colspan="100%">
        ${detailsHtml}
      </td>
    </tr>
  `);
  }

  $(document).ready(function() {
    var lastTab = localStorage.getItem('activeTemplateTab');
    if (lastTab) {
      var $tabTrigger = $('[data-bs-target="' + lastTab + '"]');
      if ($tabTrigger.length) {
        var tab = new bootstrap.Tab($tabTrigger[0]);
        tab.show();
      }
    }

    $('#templateTab button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
      localStorage.setItem('activeTemplateTab', $(e.target).attr('data-bs-target'));
    });
  });

  function handleToggleArchive(action, id) {
    swal.fire({
      title: `${action === 'archive' ? 'Archive' : 'Unarchive'} Template`,
      text: `Are you sure you want to ${action} this template?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: "Yes",
      cancelButtonText: 'No',
      cancelButtonColor: '#d33',
    }).then((result) => {
      if (result.isConfirmed) {

        $.ajax({
          url: "<?= SERVER_NAME . "/backend/nodes" ?>",
          type: "POST",
          data: {
            action: "toggle_archive",
            method: action,
            encryptedId: id
          },
          dataType: "json",
          success: function(response) {
            swal.fire({
              title: response.success ? 'Success' : "Error",
              html: response.message,
              icon: response.success ? 'success' : "error",
            }).then(() => response.success ? location.reload() : null);
          },
          error: function(xhr, status, error) {
            swal.fire({
              title: "Error",
              html: "An error occurred while processing your request.<br><b>Status:</b> " + status + "<br><b>Error:</b> " + error,
              icon: "error"
            });
          }
        });
      }
    })

  }
</script>

</html>