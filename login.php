<?php
	declare(strict_types=1);

	// commented here
	// ini_set('display_errors', '1');
	// error_reporting(E_ALL);

	require 'helpers/session.php';
	require 'helpers/auth.php';
	require 'classes/Database.php';

	start_secure_session();

	if (isLoggedIn()) {
		header('Location: login.php');
		exit;
	}

	$error = null;

	// Precomputed bcrypt hash of a random string, used to equalize timing
	// when the username doesn't exist.
	const DUMMY_HASH = '$2y$10$ST3ZUvriKFCPeR8hGRDzwOvt8TPFnDPMvXccJ5nyh9pPd9jZ2jK5K';

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		if (!csrf_valid($_POST['csrf'] ?? null)) {
			$error = 'Your session expired. Please try again.';
		} else {
			$username = trim($_POST['username'] ?? '');
			$password = $_POST['password'] ?? '';

			if ($username === '' || $password === '') {
				$error = 'Please enter a username and password.';
			} else {
				$conn = Database::getConn();
				$stmt = $conn->prepare('SELECT id, username, password FROM users WHERE username = :u LIMIT 1');
				$stmt->execute([':u' => $username]);
				$user = $stmt->fetch();

				$valid = false;

				if ($user) {
					if (password_verify($password, $user['password'])) {
						$valid = true;
					} elseif (strlen($user['password']) === 32 && hash_equals($user['password'], md5($password))) {
						// Legacy MD5 account: accept once, then upgrade to a proper hash.
						$valid = true;
						$upgrade = $conn->prepare('UPDATE users SET password = :p WHERE id = :id');
						$upgrade->execute([
						':p'  => password_hash($password, PASSWORD_DEFAULT),
						':id' => $user['id'],
						]);
					}
				} else {
					password_verify($password, DUMMY_HASH); // timing equalization
				}

				if ($valid) {
					session_regenerate_id(true); // prevent session fixation

					$_SESSION = [
						'logged_in'     => true,
						'id'            => (int) $user['id'],
						'username'      => $user['username'],
						'last_activity' => time(),
					];

					header('Location: dashboard.php');
					exit;
				}

				$error = 'Invalid username or password.';
			}
		}
	}

	$pg_title = 'Login';
	$pg_class = 'login';
	include 'includes/header.php';
?>

<div class="container">
	<div id="login">
		<h3 class="text-center pt-5"><?= e($app_name) ?></h3>
		<div id="login-row" class="row justify-content-center align-items-center">
			<div id="login-column" class="col-md-6">
				<div id="login-box" class="col-md-12">
					<h3 class="text-center text-info">Login</h3>

					<?php if ($error !== null): ?>
					<div class="alert alert-warning" role="alert"><?= e($error) ?></div>
					<?php endif; ?>

					<form id="login-form" class="form" method="post" action="login.php" novalidate>
						<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

						<div class="form-group">
							<label for="username" class="text-info">Username:</label>
							<input type="text" class="form-control" id="username" name="username"
							autocomplete="username" maxlength="64" required>
						</div>
						<div class="form-group">
							<label for="password" class="text-info">Password:</label>
							<input type="password" class="form-control" id="password" name="password"
							autocomplete="current-password" required>
						</div>
						<div class="form-group">
							<button type="submit" class="btn btn-sm btn-info">Login</button>
							<a href="register.php" class="btn btn-sm btn-link text-info" target="_self">Register</a>
						</div>
						
					</form>
				
				</div>
			</div>
		</div>
	</div>
</div>

<?php include 'includes/footer.php'; ?>