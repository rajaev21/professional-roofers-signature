  <div class="row mb-2">
    <div class="col-6 col-md-6">
      <h1 class="h3">View Company</h1>
    </div>
    <div class="col-6 col-md-6 text-end">
      <button type="button" class="btn btn-secondary text-white" onclick="window.location.replace(`<?= SERVER_NAME . '/views/companies' ?>`)">
        <i class="bi bi-chevron-left me-1"></i>
        Go Back
      </button>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-12">
          <div class="d-flex align-items-start align-items-sm-center gap-4">
            <img src="<?= $helpers->get_company_img_link($company->id) ?>" alt="company-avatar" class="d-block rounded" height="100" width="100" style="object-fit: cover" id="uploadedCompany">
          </div>
        </div>
        <hr>

        <div class="col-md-6">
          <p><strong>Company name: </strong><?= $company->name ?> </p>
        </div>

        <div class="col-md-6">
          <p><strong>Company license: </strong><?= $company->license ?> </p>
        </div>

        <div class="col-md-6">
          <p><strong>Company address: </strong><?= $company->address ?> </p>
        </div>

        <div class="col-md-6">
          <p><strong>Company contact: </strong><?= $company->contact ?> </p>
        </div>

        <div class="col-md-6">
          <p><strong>Company email: </strong><?= $company->email ?> </p>
        </div>

        <div class="col-md-6">
          <p><strong>Company number_of_employee: </strong><?= $company->number_of_employee ?> </p>
        </div>

        <div class="col-md-12">
          <p><strong>Company website: </strong><?= $company->website ?> </p>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped" id="usersTable">
          <thead>
            <tr>
              <th class="d-table-cell d-md-none">More</th>
              <th>Avatar</th>
              <th>Name</th>
              <th class="d-none d-md-table-cell">Contact</th>
              <th class="d-none d-md-table-cell">Address</th>
              <th class="d-none d-md-table-cell">Email</th>
              <th class="d-none d-md-table-cell">Role</th>
              <th class="d-none d-md-table-cell">Company</th>
              <th class="d-none d-md-table-cell">Position</th>
              <th class="d-none d-md-table-cell">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (count($users) > 0) :
              foreach ($users as $user) :
            ?>
                <tr>
                  <td class="d-table-cell d-md-none"><button class="btn btn-transparent btn-sm" onclick="viewMore(this)"> <i class="bi bi-three-dots"></i></button></td>
                  <td class="td-image">
                    <img src="<?= $helpers->get_avatar_link($user->id) ?>" alt="User Avatar" class="rounded-circle avatar" style="width: 80px; height: 80px;" onclick='handleOpenModalImg($(this), `modalUserAvatar`, `userAvatarID`)'>
                  </td>
                  <td><?= $helpers->get_full_name($user->id) ?></td>
                  <td class="d-none d-md-table-cell"><?= $user->contact ?></td>
                  <td class="d-none d-md-table-cell"><?= $user->address ?></td>
                  <td class="d-none d-md-table-cell"><?= $user->email ?></td>
                  <td class="d-none d-md-table-cell"><?= $user->role ?></td>
                  <td class="d-none d-md-table-cell"><?= $company->name ?></td>
                  <td class="d-none d-md-table-cell"><?= $user->position ?></td>
                  <td class="d-none d-md-table-cell">
                    <?php if ($user->status == "1"): ?>
                      <i class="fa-solid fa-circle-check me-1 text-success"></i>
                      Active
                    <?php else: ?>
                      <i class="fa-solid fa-circle-xmark me-1 text-danger"></i>
                      Inactive
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