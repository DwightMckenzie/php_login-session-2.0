<?php
declare(strict_types=1);

require 'helpers/session.php';
require 'helpers/auth.php';
require 'classes/Database.php';

start_secure_session();

// Logged-in users don't need to register
if (isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$errors   = [];
$success  = false;
$username = '';
$email    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm'] ?? '';

        if (!preg_match('/^[A-Za-z0-9_.-]{3,64}$/', $username)) {
            $errors[] = 'Username must be 3 to 64 characters: letters, numbers, dots, dashes, or underscores.';
        }

        if (strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        // bcrypt ignores anything past 72 bytes, so reject longer passwords
        if (strlen($password) > 72) {
            $errors[] = 'Password must be 72 bytes or fewer.';
        }

        if (!hash_equals($password, $confirm)) {
            $errors[] = 'Passwords do not match.';
        }

        if ($errors === []) {
            $conn = Database::getConn();

            $check = $conn->prepare(
                'SELECT username, email FROM users WHERE username = :username OR email = :email LIMIT 1'
            );
            $check->execute([':username' => $username, ':email' => $email]);
            $existing = $check->fetch();

            if ($existing) {
                // Same message for either match, so this doesn't reveal which field exists
                $errors[] = 'That username or email is already registered.';
            } else {
                try {
                    $insert = $conn->prepare(
                        'INSERT INTO users (username, email, password) VALUES (:username, :email, :password)'
                    );
                    $insert->execute([
                        ':username' => $username,
                        ':email'    => $email,
                        ':password' => password_hash($password, PASSWORD_DEFAULT),
                    ]);

                    $success  = true;
                    $username = '';
                    $email    = '';
                } catch (PDOException $e) {
                    // 23000 = duplicate key, which happens if two people register the same details at once
                    if ($e->getCode() === '23000') {
                        $errors[] = 'That username or email is already registered.';
                    } else {
                        error_log('Registration failed: ' . $e->getMessage());
                        $errors[] = 'Registration failed. Please try again later.';
                    }
                }
            }
        }
    }
}

$pg_title = 'Register';
$pg_class = 'register';
include 'includes/header.php';
?>

<div class="container">
  <div id="login">
    <h3 class="text-center pt-5"><?= e($app_name) ?></h3>
    <div class="row justify-content-center align-items-center">
      <div class="col-md-6">
        <div class="col-md-12">
          <h3 class="text-center text-info">Create Account</h3>

          <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
              Account created. <a href="login.php">Log in</a>.
            </div>
          <?php endif; ?>

          <?php foreach ($errors as $error): ?>
            <div class="alert alert-warning" role="alert"><?= e($error) ?></div>
          <?php endforeach; ?>

          <?php if (!$success): ?>
          <form method="post" action="register.php" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

            <div class="form-group">
              <label for="username" class="text-info">Username:</label>
              <input type="text" class="form-control" id="username" name="username"
                     value="<?= e($username) ?>" autocomplete="username" maxlength="64" required>
            </div>
            <div class="form-group">
              <label for="email" class="text-info">Email:</label>
              <input type="email" class="form-control" id="email" name="email"
                     value="<?= e($email) ?>" autocomplete="email" maxlength="254" required>
            </div>
            <div class="form-group">
              <label for="password" class="text-info">Password:</label>
              <input type="password" class="form-control" id="password" name="password"
                     autocomplete="new-password" minlength="8" maxlength="72" required>
            </div>
            <div class="form-group">
              <label for="confirm" class="text-info">Confirm Password:</label>
              <input type="password" class="form-control" id="confirm" name="confirm"
                     autocomplete="new-password" minlength="8" maxlength="72" required>
            </div>
            <div class="form-group">
              <button type="submit" class="btn btn-sm btn-info">Register</button>
              <a href="login.php" class="btn btn-sm btn-link text-info">Back to login</a>
            </div>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>