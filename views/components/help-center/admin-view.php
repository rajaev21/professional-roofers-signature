    <?php
    $help_center_categories = $helpers->select_all_with_params("help_center_categories", "is_archived = 0");
    $pageTitle = "Recent Entries";
    ?>
    <div class="container-fluid" style="background-image: url('<?= SERVER_NAME . "/assets/img/help-center/knowledge-base-bg.png" ?>'); background-size: cover; background-position: center; background-repeat: no-repeat; height: 200px;">
      <div class="fs-1 fw-bold text-white py-5 text-center">
        How can we help you
        <div class="d-flex justify-content-center mb-4">
          <div class="col-md-6">
            <div class="input-group mb-3">
              <input type="text" class="form-control form-control-lg" id="searchHelpCenter" placeholder="Search help articles, guides, and FAQs..." />
              <button class="input-group-text" id="searchButton"><i class="bi bi-search"></i></button>
            </div>
          </div>
        </div>
      </div>
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-4">
            <div class="card mt-4">
              <div class="card-header">
                <h3 class="card-title py-1">Categories</h3>
              </div>
              <div class="card-body">
                <div class="accordion accordion-flush" id="accordionCategories">
                  <?php foreach ($help_center_categories as $categories_data):
                  ?>
                    <div class="accordion-item">
                      <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $categories_data->id ?>" aria-expanded="false">
                          <?= $categories_data->formatted_category ?>
                        </button>
                      </h2>
                      <div id="<?= $categories_data->id ?>" class="accordion-collapse collapse" data-bs-parent="#accordionCategories">
                        <div class="accordion-body">
                          <?php $category_item = $helpers->select_all_with_params("help_center", "category_id = " . $categories_data->id);
                          if (count($category_item) == 0) : ?>
                            <p class='text-body-secondary'>No items found in this category.</p>
                            <?php
                          else :
                            foreach ($category_item as $item):
                              $encrypted_category_id = $helpers->encrypt($item->category_id);
                              $encrypted_id = $helpers->encrypt($item->id);
                            ?>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="checkDefault" value="<?= $item->id ?>" data-title="<?= $item->title ?>" data-description="<?= $item->description ?>" data-category="<?= $encrypted_category_id ?>" data-id="<?= $encrypted_id ?>">
                                <label class="form-check-label" for="checkDefault">
                                  <?= $item->title ?>
                                </label>
                              </div>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-8">
            <div class="card mt-4">
              <div class="card-header">
                <h2 class="card-title"><?= $pageTitle ?></h2>
              </div>
              <div class="card-body" id="entriesBody">
                <?php
                $recent_entries = $helpers->select_all_with_params("help_center", "is_archived = 0 ORDER BY date_created desc limit 10");
                foreach ($recent_entries as $entry):
                  $encrypted_category_id = $helpers->encrypt($entry->category_id);
                  $encrypted_id = $helpers->encrypt($entry->id);
                ?>
                  <div class="mb-3">
                    <h3><a href="<?= SERVER_NAME . "/views/help-center?page=view&category=" . $encrypted_category_id . "&id=" . $encrypted_id ?>"><?= $entry->title ?></a></h3>
                    <p><?= $entry->description ?></p>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script>
        let cleared = false;
        $(document).on("change", ".form-check-input", function() {
          const id = $(this).val();
          const title = $(this).data("title");
          const description = $(this).data("description");
          const category = $(this).data("category");
          const encryptedId = $(this).data("id");
          const container = $("#entriesBody");

          if (!cleared) {
            $("#entriesBody").empty();
            cleared = true;
          }

          if ($(this).is(":checked")) {
            if ($("#selected_" + id).length) return;
            container.append(`
            <div class="entries mb-3" id="selected_${id}">
              <h3>
                <a href="<?= SERVER_NAME ?>/views/help-center?page=view&category=${category}&id=${encryptedId}">
                  ${title}
                </a>
              </h3>
              <p>${description}</p>
            </div>
          `);
          } else {
            $("#selected_" + id).remove();
            if ($(".entries").length === 0) {
              cleared = false;
              container.append(`<p class='text-body-secondary'>No entries selected.</p>`);
            }
          }
        });

        $('#searchButton').on('click', async function() {
          const value = $('#searchHelpCenter').val().trim();
          const res = await searchHelpCenter(value);

          if (res) {
            if (cleared) {
              $(".form-check-input").prop("checked", false);
              cleared = false;
            }
            let entriesHtml = '';
            res.data.forEach(entry => {
              entriesHtml += `
                <div class="mb-3">
                  <h3><a href="#">${entry.title}</a></h3>
                  <p>${entry.description}</p>
                </div>
              `;
            });
            $('#entriesBody').html(entriesHtml);
          }
        });

        async function searchHelpCenter(value) {
          const res = await $.ajax({
            url: '<?= SERVER_NAME . "/backend/nodes" ?>',
            type: "POST",
            dataType: "json",
            data: {
              action: 'get_help_center_by_search',
              search: value,
            }
          });

          if (!res.success) {
            Swal.showValidationMessage(`Error: ${res.message}`);
            return null;
          }
          return res;
        }
      </script>