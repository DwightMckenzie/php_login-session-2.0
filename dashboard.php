<?php
  /**
   * dashboard.php
   *
   * Version: 0.2
   *
   * Changelog
   *  Bootstrap 3.4.1 modals for create, edit and delete.
   *  Font Awesome 4.7 pencil (edit) and trash (delete) icons.
   */
  declare(strict_types=1);

  require 'helpers/session.php';
  require 'helpers/auth.php';
  require 'classes/Database.php';

  start_secure_session();
  requireLogin();

  $conn = Database::getConn();

  // Errors for the create/edit modals, and errors for delete (shown at the top of the page)
  $formErrors = [];
  $pageErrors = [];

  // Which modal to reopen after a failed save: 'create', 'edit' or '' for none
  $reopen = '';

  // Values shown in each modal. Only the modal that was submitted gets the posted values.
  $createVals = ['username' => '', 'email' => ''];
  $editVals   = ['id' => 0, 'username' => '', 'email' => ''];

  /**
   * Validate account fields.
   * Password is required on create and optional on edit (blank keeps the current one).
   *
   * @return string[] list of error messages, empty when valid
   */
  function validate_account(string $username, string $email, string $password, bool $passwordRequired): array
  {
    $errs = [];

    if (!preg_match('/^[A-Za-z0-9_.-]{3,64}$/', $username)) {
      $errs[] = 'Username must be 3 to 64 characters: letters, numbers, dots, dashes, or underscores.';
    }

    if (strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
      $errs[] = 'Please enter a valid email address.';
    }

    if ($passwordRequired || $password !== '') {
      if (strlen($password) < 8) {
        $errs[] = 'Password must be at least 8 characters.';
      }
      // bcrypt ignores anything past 72 bytes
      if (strlen($password) > 72) {
        $errs[] = 'Password must be 72 bytes or fewer.';
      }
    }

    return $errs;
  }

  /** True if another account (not $excludeId) already uses this username or email. */
  function account_taken(PDO $conn, string $username, string $email, int $excludeId): bool
  {
    $sql = 'SELECT 1 FROM users
            WHERE (username = :username
            OR email = :email)
            AND id <> :id
            LIMIT 1';
    $stmt = $conn->prepare($sql);
    $stmt->execute([':username' => $username, ':email' => $email, ':id' => $excludeId]);
    return (bool) $stmt->fetch();
  }

  /** Print the errors inside a modal, but only if that modal is the one being reopened. */
  function show_form_errors(string $modal, string $reopen, array $errors): void
  {
    if ($reopen !== $modal) {
      return;
    }
    foreach ($errors as $error) {
      echo '<div class="alert alert-danger" role="alert">' . e($error) . '</div>';
    }
  }

  // ---------- Handle POST (create, update, delete) ----------
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // does certificate exist?
    if (!csrf_valid($_POST['csrf'] ?? null)) {
      $pageErrors[] = 'Your session expired. Please try again.';
    } else {
      $action = $_POST['action'] ?? '';
      $id     = (int) ($_POST['id'] ?? 0);

      // what action is present value
      if ($action === 'delete') {

        // Don't let a user delete the account they are logged in with
        if ($id === (int) $_SESSION['id']) {
          $pageErrors[] = 'You cannot delete the account you are logged in with.';
        } else {
          $stmt = $conn->prepare('DELETE FROM users WHERE id = :id');
          $stmt->execute([':id' => $id]);

          if ($stmt->rowCount() === 1) {
            $_SESSION['flash'] = 'Account deleted.';
            header('Location: dashboard.php');
            exit;
          }
          $pageErrors[] = 'That account no longer exists.';
        }

      } elseif ($action === 'create' || $action === 'update') {

        $isEdit   = ($action === 'update');
        $reopen   = $isEdit ? 'edit' : 'create';
        $password = $_POST['password'] ?? '';

        $form = [
          'id'       => $id,
          'username' => trim($_POST['username'] ?? ''),
          'email'    => trim($_POST['email'] ?? ''),
        ];

        // Remember posted values so the modal can be refilled if validation fails
        if ($isEdit) {
          $editVals = $form;
        } else {
          $createVals = ['username' => $form['username'], 'email' => $form['email']];
        }

        $formErrors = validate_account($form['username'], $form['email'], $password, !$isEdit);

        if ($formErrors === [] && $isEdit) {
          $exists = $conn->prepare('SELECT 1 FROM users WHERE id = :id');
          $exists->execute([':id' => $id]);
          if (!$exists->fetch()) {
            $formErrors[] = 'That account no longer exists.';
          }
        }

        if ($formErrors === [] && account_taken($conn, $form['username'], $form['email'], $id)) {
          $formErrors[] = 'That username or email is already in use.';
        }

        if ($formErrors === []) {
          try {
            if ($action === 'create') {
              $sql = 'INSERT INTO users (username, email, password)
                      VALUES (:username, :email, :password)';
              $stmt = $conn->prepare($sql);
              $stmt->execute([
                ':username' => $form['username'],
                ':email'    => $form['email'],
                ':password' => password_hash($password, PASSWORD_DEFAULT),
              ]);
              $_SESSION['flash'] = 'Account created.';

            } elseif ($password !== '') {
              $sql = 'UPDATE users
                      SET username = :username, email = :email, password = :password
                      WHERE id = :id';
              $stmt = $conn->prepare($sql);
              $stmt->execute([
                ':username' => $form['username'],
                ':email'    => $form['email'],
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':id'       => $id,
              ]);
              $_SESSION['flash'] = 'Account updated.';

            } else {
              $sql = 'UPDATE users
                      SET username = :username, email = :email
                      WHERE id = :id';
              $stmt = $conn->prepare($sql);
              $stmt->execute([
                  ':username' => $form['username'],
                  ':email'    => $form['email'],
                  ':id'       => $id,
              ]);
              $_SESSION['flash'] = 'Account updated.';
            }

            header('Location: dashboard.php');
            exit;

          } catch (PDOException $e) {
            // 23000 = duplicate key (race between two saves)
            if ($e->getCode() === '23000') {
              $formErrors[] = 'That username or email is already in use.';
            } else {
              error_log('Dashboard save failed: ' . $e->getMessage());
              $formErrors[] = 'Could not save. Please try again later.';
            }
          }
        }

      } else {
        $pageErrors[] = 'Unknown action.';
      }
    }
  }

  // ---------- Data for the page ----------
  $sql = 'SELECT id, username, email
          FROM users
          ORDER BY username';
  $users = $conn->query($sql)->fetchAll();

  $flash = $_SESSION['flash'] ?? null;
  unset($_SESSION['flash']);

  $csrf = csrf_token();

  // Tells navbar.php to show the Add User button
  $nav_add_user = true;

  $pg_title = 'Dashboard';
  $pg_class = 'dashboard';
  include 'includes/header.php';
  include 'includes/navbar.php';
?>

<?php if ($flash !== null): ?>
  <div class="alert alert-success" role="alert"><?= e($flash) ?></div>
<?php endif; ?>

<?php foreach ($pageErrors as $error): ?>
  <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
<?php endforeach; ?>

<div class="container">

  <p><strong>Registered users: </strong><?= count($users) ?></p>

  <div class="table-responsive">
    <table class="table table-sm table-condensed table-striped table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Email</th>
          <th class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <?php $isSelf = ((int) $u['id'] === (int) $_SESSION['id']); ?>
          <tr>
            <td><?= (int) $u['id'] ?></td>
            <td>
              <?= e($u['username']) ?>
              <!-- <php if ($isSelf): ?><span class="label label-info">you</span><php endif; ?> -->
              <!-- <php if ($isSelf): ?><span class="label label-success"> <i class="fa fa-user" aria-hidden="true"></i></span><php endif; ?> -->
              <?php if ($isSelf): ?>
          <span class="label label-info current-user">
            <i class="fa fa-circle" aria-hidden="true"></i>
          </span>
        <?php endif; ?>
            </td>
            <td><?= e($u['email']) ?></td>
            <td class="text-right">
              <!-- Edit: opens the edit modal, which is filled by the script below -->
              <button type="button" class="btn btn-xs btn-info btn-icon"
                      data-toggle="modal" data-target="#editModal"
                      data-id="<?= (int) $u['id'] ?>"
                      data-username="<?= e($u['username']) ?>"
                      data-email="<?= e($u['email']) ?>"
                      title="Edit user">
                <i class="fa fa-pencil"></i>
              </button>

              <?php if (!$isSelf): ?>
                <!-- Delete: opens the confirmation modal -->
                <button type="button" class="btn btn-xs btn-danger btn-icon"
                        data-toggle="modal" data-target="#deleteModal"
                        data-id="<?= (int) $u['id'] ?>"
                        data-username="<?= e($u['username']) ?>"
                        title="Delete user">
                  <i class="fa fa-trash"></i>
                </button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if ($users === []): ?>
          <tr><td colspan="4" class="text-center">No accounts yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- ===================== CREATE USER MODAL ===================== -->
  <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="post" action="dashboard.php" class="form-horizontal" novalidate>
          <div class="modal-header">
            <h4 class="modal-title" id="createModalLabel">Add User</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body">
            <?php show_form_errors('create', $reopen, $formErrors); ?>

            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="create">

            <div class="form-group">
              <label class="col-sm-3 control-label" for="create-username">Username</label>
              <div class="col-sm-12">
                <input type="text" class="form-control" id="create-username" name="username"
                      value="<?= e($createVals['username']) ?>" maxlength="64" required>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label" for="create-email">Email</label>
              <div class="col-sm-12">
                <input type="email" class="form-control" id="create-email" name="email"
                      value="<?= e($createVals['email']) ?>" maxlength="254" required>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label" for="create-password">Password</label>
              <div class="col-sm-12">
                <input type="password" class="form-control" id="create-password" name="password"
                      autocomplete="new-password" minlength="8" maxlength="72" required>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-info">Create</button>
          </div>
      
        </form>
      </div>
    </div>
  </div>

  <!-- ===================== EDIT USER MODAL ===================== -->
  <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="post" action="dashboard.php" class="form-horizontal" novalidate>
          <div class="modal-header">
            <h4 class="modal-title" id="editModalLabel">Edit User</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body">
            <?php show_form_errors('edit', $reopen, $formErrors); ?>

            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-id" value="<?= (int) $editVals['id'] ?>">

            <div class="form-group">
              <label class="col-sm-3 control-label" for="edit-username">Username</label>
              <div class="col-sm-12">
                <input type="text" class="form-control" id="edit-username" name="username"
                      value="<?= e($editVals['username']) ?>" maxlength="64" required>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label" for="edit-email">Email</label>
              <div class="col-sm-12">
                <input type="email" class="form-control" id="edit-email" name="email"
                      value="<?= e($editVals['email']) ?>" maxlength="254" required>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label" for="edit-password">Password</label>
              <div class="col-sm-12">
                <input type="password" class="form-control" id="edit-password" name="password"
                      autocomplete="new-password" minlength="8" maxlength="72"
                      placeholder="Leave blank to keep current">
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-info">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ===================== DELETE USER MODAL (confirm) ===================== -->
  <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="post" action="dashboard.php">
          <div class="modal-header">
            <h4 class="modal-title" id="deleteModalLabel">Delete User</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body">
            <p>Are you sure you want to delete <strong id="delete-name"></strong>? This cannot be undone.</p>

            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="delete-id" value="">
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> Delete</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Runs after jQuery and Bootstrap have loaded (footer.php loads them at the end of <body>)
    window.addEventListener('load', function () {

      // Fill the edit modal from the clicked row. Skip when the server reopens it after an error,
      // so the posted values are kept.
      jQuery('#editModal').on('show.bs.modal', function (event) {
      var btn = jQuery(event.relatedTarget);
      if (!btn.length) {
        return;
      }
      jQuery('#edit-id').val(btn.data('id'));
      jQuery('#edit-username').val(btn.data('username'));
      jQuery('#edit-email').val(btn.data('email'));
      jQuery('#edit-password').val('');
      });

      // Fill the delete modal with the clicked user
      jQuery('#deleteModal').on('show.bs.modal', function (event) {
      var btn = jQuery(event.relatedTarget);
      jQuery('#delete-id').val(btn.data('id'));
      jQuery('#delete-name').text(btn.data('username'));
      });

      // Reopen the modal that had the failed save
      <?php if ($reopen !== ''): ?>
      jQuery('#<?= $reopen === 'edit' ? 'editModal' : 'createModal' ?>').modal('show');
      <?php endif; ?>
    });
  </script>

</div><!-- end container -->

<?php include 'includes/footer.php'; ?>