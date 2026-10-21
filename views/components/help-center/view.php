<?php ?>

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

  <?php
  if (count($help_center) > 0):
    foreach ($help_center as $data):
  ?>
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-8">
            <div class="card mt-4">
              <div class="card-header">
                <h1 class="card-title py-1"><?= $data->title ?></h1>
              </div>
              <div class="card-body">
                <div class="row g-3 mt-3">
                  <div class="col-12" style="border-bottom: 1px solid #f5f5f5; padding-bottom: 30px">
                    <h6><?= $data->description ?></h6>
                    <small><?= date("F j, Y g:i a", strtotime($data->date_created)) ?></small>
                  </div>
                  <?php if ($data->content) : ?>
                    <div class="col-12">
                      <?= $data->content ?>
                    </div>
                  <?php endif; ?>
                  <?php if ($data->external_link) : ?>
                    <div class="col-12">
                      <a href="<?= $data->external_link ?>" target="_blank"><?= $data->external_link ?></a>
                    </div>
                  <?php endif; ?>
                  <?php if ($data->video_file) : ?>
                    <div class="col-12">
                      <video style="width: 100%;" id="videoPlayer" controls controlsList="nodownload" oncontextmenu="return false;">
                        <source src="<?= SERVER_NAME . "/uploads/" . $help_center_categories->category . "/videos/" . $data->video_file ?>" type="video/mp4">
                      </video>
                    </div>
                  <?php endif; ?>
                  <?php if ($data->pdf_file) : ?>
                    <div class="col-12">
                      <iframe style="width: 100%; height: 550px" src="<?= SERVER_NAME . "/uploads/" . $help_center_categories->category . "/pdfs/" . $data->pdf_file ?>#toolbar=0&amp;navpanes=0&amp;scrollbar=0" frameborder="0" oncontextmenu="return false;"></iframe>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script>

</script>