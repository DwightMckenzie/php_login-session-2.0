<?php

  /**
   * Authenticate user and the activity
   */

  declare(strict_types=1);

  const SESSION_IDLE_TIMEOUT = 1800; // 30 minutes

  function isLoggedIn(): bool
  {
    return ($_SESSION['logged_in'] ?? false) === true
      && !empty($_SESSION['id']);
  }

  /** Call at the top of every protected page */
  function requireLogin(): void
  {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    if (isset($_SESSION['last_activity'])
        && time() - $_SESSION['last_activity'] > SESSION_IDLE_TIMEOUT) {
        $_SESSION = [];
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }

    $_SESSION['last_activity'] = time();
  }

?>