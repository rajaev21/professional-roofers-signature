<?php
$item_id = null;
$title = null;
$description = null;
$content = null;
$video = null;
$pdf = null;
$external_link = null;
$category_id = $helpers->decrypt($_GET["category"]);
$category_data = $helpers->select_all_individual("help_center_categories", "id = $category_id");

if (isset($_GET["id"])) {
  $item_id = $helpers->decrypt($_GET["id"]);
  $help_center_data = $helpers->select_all_with_params("help_center", "id = $item_id");
  if (count($help_center_data) > 0) {
    foreach ($help_center_data as $data) {
      $title = $data->title;
      $description = $data->description;
      $content = $data->content;
      $video = $data->video_file ?? "";
      $pdf = $data->pdf_file ?? "";
      $external_link = $data->external_link ?? "";
    }
  }
}

?>

<div class="container">
  <div class="row mb-2">
    <div class="col-6 col-md-6">
      <h1 class="h3"><?= $pageTitle ?></h1>
    </div>
    <div class="col-6 col-md-6 text-end">
      <button type="button" class="btn btn-secondary text-white my-2" onclick="window.history.back()">
        <i class="bi bi-chevron-left me-1"></i>
        Go Back
      </button>
    </div>
  </div>

  <div class="card">
    <form id="formAddTutorial" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add_help_center_item">
      <input type="hidden" name="created_by" value="<?= $current_user->id ?>">
      <input type="hidden" name="id" value="<?= $item_id ? $item_id : '' ?>">
      <input type="hidden" name="category_id" value="<?= $category_data->id ?>">
      <div class="card-body">
        <div class="mb-3">
          <label id="title" class="form-label">
            Title
          </label>
          <input type="text" id="title" class="form-control" name="title" value="<?= $title ? $title : "" ?>" required>
        </div>
        <div class="mb-3">
          <label for="description" class="form-label">Description</label>
          <textarea class="form-control" id="description" name="description" rows="3"><?= $description ? $description : "" ?></textarea>
        </div>
        <div class="mb-3">
          <label for="content" class="form-label">Content</label>
          <textarea class="form-control" id="content" name="content" rows="3"><?= $content ? $content : "" ?></textarea>
        </div>
        <div class="mb-3">
          <label for="video" class="form-label ">
            Upload Video
          </label>
          <input class="form-control" type="file" id="video" name="upload_video" accept="video/*">
          <?php if ($video) : ?>
            <div class="mt-2">
              <video style="width: 50%;" id="videoPlayer" controls controlsList="nodownload" oncontextmenu="return false;">
                <source src="<?= SERVER_NAME . "/uploads/" . $category_data->category . "/videos/" . $video ?>" type="video/mp4" id="videoSource">
              </video>
            </div>
          <?php endif; ?>
        </div>
        <div class="mb-3">
          <label for="pdf" class="form-label ">
            Upload PDF
          </label>
          <input class="form-control" type="file" id="pdf" name="upload_pdf" accept="application/pdf">
          <?php if ($pdf) : ?>
            <div class="mt-2">
              <iframe style="width: 50%; height: 550px" src="<?= $pdf ? SERVER_NAME . "/uploads/" . $category_data->category . "/pdfs/" . $pdf : "" ?>" frameborder="0" oncontextmenu="return false;"></iframe>
            </div>
          <?php endif; ?>
        </div>
        <div class="mb-3">
          <label id="external_link" class="form-label">
            External Link
          </label>
          <input type="text" id="external_link" class="form-control" name="external_link" value="<?= $external_link ? $external_link : "" ?>">
        </div>
      </div>
      <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary text-white">
          Submit
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  tinymce.init({
    selector: '#content',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo styles fontsize bold italic alignleft aligncenter alignright alignjustify link restoredraft save',
  });

  $("#formAddTutorial").on("submit", function(event) {
    if (event) event.preventDefault();
    tinymce.triggerSave();
    let formData = new FormData(this);
    for (let [key, value] of formData.entries()) {
      console.log(key + ":", value);
    }
    handleSave(new FormData(this));
  });

  async function handleSave(formData) {
    console.log(formData)
    try {
      const response = await $.ajax({
        url: "<?= SERVER_NAME . "/backend/nodes" ?>",
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        enctype: 'multipart/form-data'
      });

      swal.fire({
        icon: response.success ? 'success' : 'error',
        title: response.success ? 'Success' : 'Error',
        text: (response && response.message) || (response.success ? 'Item uploaded.' : 'An error occurred while uploading item.'),
      }).then(() => {
        if (response.success) {
          if (response.redirect) {
            window.location.href = response.redirect;
          } else {
            window.location.reload();
          }
        }
      });

    } catch (error) {
      console.error('Error:', error);
      swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'An error occurred while uploading item.',
      });
    }
  }
</script>