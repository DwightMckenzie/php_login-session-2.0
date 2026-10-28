<?php
  declare(strict_types=1);

  require 'helpers/session.php';

  start_secure_session();

  $_SESSION = [];

  // Expire the session cookie in the browser.
  if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
      'expires'  => time() - 42000,
      'path'     => $p['path'],
      'domain'   => $p['domain'],
      'secure'   => $p['secure'],
      'httponly' => $p['httponly'],
      'samesite' => $p['samesite'],
    ]);
  }

  session_destroy();

  header('Location: login.php');
  exit;

?>